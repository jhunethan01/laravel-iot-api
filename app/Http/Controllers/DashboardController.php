<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Device;
use App\Models\Telemetry;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $status = $request->query('status', 'all');

        $criticalDeviceIds = Alert::where('severity', 'critical')
            ->whereNull('resolved_at')
            ->pluck('device_id')
            ->unique();

        $devices = Device::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('device_key', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->get()
            ->map(fn (Device $device) => [
                'id' => $device->id,
                'device_key' => $device->device_key,
                'name' => $device->name,
                'model' => $device->model,
                'last_seen_at' => $device->last_seen_at ? Carbon::parse($device->last_seen_at)->toIso8601String() : null,
                'status' => $criticalDeviceIds->contains($device->id) ? 'critical' : $device->status,
                'latest_temperature' => $device->latest_temperature,
                'latest_battery' => $device->latest_battery,
                'latest_storage_used' => $device->latest_storage_used,
                'latest_latitude' => $device->latest_latitude,
                'latest_longitude' => $device->latest_longitude,
            ])
            ->when($status !== 'all', fn ($collection) => $collection->where('status', $status))
            ->values();

        $telemetry = Telemetry::where('recorded_at', '>=', Carbon::now()->subHours(24))
            ->orderBy('recorded_at')
            ->get(['recorded_at', 'temperature'])
            ->groupBy(function ($row) {
                $recordedAt = Carbon::parse($row->recorded_at);
                $bucketMinute = intdiv($recordedAt->minute, 10) * 10;
                return $recordedAt->format('Y-m-d H:') . str_pad((string) $bucketMinute, 2, '0', STR_PAD_LEFT);
            })
            ->map(fn ($rows, $bucket) => [
                'bucket' => $bucket,
                'avg_temperature' => round($rows->avg('temperature'), 1),
            ])
            ->values();

        $alerts = Alert::with('device')
            ->orderByDesc('triggered_at')
            ->limit(10)
            ->get()
            ->map(fn (Alert $alert) => [
                'id' => $alert->id,
                'type' => $alert->type,
                'severity' => $alert->severity,
                'message' => $alert->message,
                'device_name' => $alert->device->name ?? $alert->device->device_key ?? 'Unknown device',
                'triggered_at' => Carbon::parse($alert->triggered_at)->toIso8601String(),
                'resolved' => $alert->resolved_at !== null,
            ]);

        return Inertia::render('Dashboard', [
            'stats' => [
                'devicesOnline' => Device::where('status', 'online')->count(),
                'devicesOffline' => Device::where('status', 'offline')->count(),
                'activeAlerts' => Alert::whereNull('resolved_at')->count(),
            ],
            'telemetry' => $telemetry,
            'alerts' => $alerts,
            'devices' => $devices,
            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
        ]);
    }
}
