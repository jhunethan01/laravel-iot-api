<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Log;

class TelemetryController extends Controller
{
    public function post(Request $request)
    {
        // Log::info('Telemetry hit', [
        //     'payload' => $request->all(),
        //     'ip' => $request->ip(),
        // ]);
        return $request->all();
    }

}
