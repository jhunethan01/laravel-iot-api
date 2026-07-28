<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function index()
    {
        $devices = Device::all();
        return response()->json($devices);
    }

    public function show($id)
    {
        $device = Device::find($id);
        if (!$device) {
            return response()->json(['error' => 'Device not found'], 404);
        }
        return response()->json($device);
    }

    public function post(Request $request)
    {
        // Accept JSON body only
        if (!$request->isJson()) {
            return response()->json(['error' => 'Content-Type must be application/json'], 415);
        }

        $payload = $request->json()->all();

        $validated = validator($payload, [
            'device_key' => 'required|string|max:64',
            'name' => 'required|string|max:100',
            'model' => 'nullable|string|max:100',
            'status' => 'nullable|in:online,offline',
        ])->validate();

        $existing = Device::where('device_key', $validated['device_key'])->first();
        if ($existing) {
            return response()->json([
                'error' => 'Device already exists',
                'device_id' => $existing->id,
                'device_key' => $existing->device_key,
            ], 409);
        }

        $device = Device::create([
            'device_key' => $validated['device_key'],
            'name' => $validated['name'],
            'model' => $validated['model'] ?? null,
            'status' => $validated['status'] ?? 'offline',
        ]);

        return response()->json($device, 201);
    }
}
