<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class TelemetryController extends Controller
{
    public function post(Request $request)
    {
        return $request->all();
    }

}
