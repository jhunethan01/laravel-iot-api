<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessAlerts;
use App\Models\Alert;
use App\Models\Device;
use App\Models\Telemetry;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class TelemetryController extends Controller
{
    public function post(Request $request)
    {
        $telemetryPayload = $request->all();

        $request->validate([
            'device_id' => 'required|string',
            'timestamp' => 'required|date',
            'temperature' => 'required|numeric',
            'battery' => 'required|numeric',
            'storage_used' => 'required|numeric',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'online' => 'required|boolean',
        ]);

        DB::transaction(function () use ($telemetryPayload) {
            $device = Device::firstOrCreate(
                ['device_key' => $telemetryPayload['device_id']],
                [
                    'name' => $telemetryPayload['device_id'],
                    'status' => (bool) $telemetryPayload['online'] ? 'online' : 'offline',
                ]
            );

            $recordedAt = Carbon::parse($telemetryPayload['timestamp']);

            $telemetry = Telemetry::create([
                'device_id' => $device->id,
                'recorded_at' => $recordedAt,
                'temperature' => $telemetryPayload['temperature'],
                'battery' => $telemetryPayload['battery'],
                'storage_used' => $telemetryPayload['storage_used'],
                'latitude' => $telemetryPayload['latitude'],
                'longitude' => $telemetryPayload['longitude'],
                'online' => $telemetryPayload['online'],
            ]);

            $this->evaluateAlerts($device, $telemetry, $recordedAt);

            $device->fill([
                'status' => (bool) $telemetryPayload['online'] ? 'online' : 'offline',
                'last_seen_at' => $recordedAt,
                'latest_temperature' => $telemetryPayload['temperature'],
                'latest_battery' => $telemetryPayload['battery'],
                'latest_storage_used' => $telemetryPayload['storage_used'],
                'latest_latitude' => $telemetryPayload['latitude'],
                'latest_longitude' => $telemetryPayload['longitude'],
            ])->save();
        });

        return $request->all();
    }
    private function evaluateAlerts(Device $device, Telemetry $telemetry, Carbon $at): void
    {
        $checks = [
            'high_temperature' => [
                'breach' => $telemetry->temperature >= 50,
                'severity' => 'critical',
                'message' => "Temperature is {$telemetry->temperature}C",
            ],
            'low_battery' => [
                'breach' => $telemetry->battery <= 20,
                'severity' => 'warning',
                'message' => "Battery is {$telemetry->battery}%",
            ],
            'high_storage' => [
                'breach' => $telemetry->storage_used >= 90,
                'severity' => 'warning',
                'message' => "Storage used is {$telemetry->storage_used}%",
            ],
            'device_offline' => [
                'breach' => !(bool) $telemetry->online,
                'severity' => 'critical',
                'message' => 'Device is offline',
            ],
        ];

        foreach ($checks as $type => $check) {
            $open = Alert::where('device_id', $device->id)
                ->where('type', $type)
                ->whereNull('resolved_at')
                ->latest('triggered_at')
                ->first();

            if ($check['breach'] && !$open) {
                $alert = Alert::create([
                    'device_id' => $device->id,
                    'telemetry_id' => $telemetry->id,
                    'type' => $type,
                    'severity' => $check['severity'],
                    'message' => $check['message'],
                    'triggered_at' => $at,
                ]);

                // Sends the alert to AWS SQS for further processing
                // Disabled, just here to learn how to use AWS SQS with Laravel.
                // ProcessAlerts::dispatch($alert->id, 'opened')->afterCommit();
            }

            if (!$check['breach'] && $open) {
                $open->update([
                    'resolved_at' => $at,
                    'telemetry_id' => $telemetry->id,
                ]);
            }
        }
    }

}
