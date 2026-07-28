<?php

namespace App\Http\Controllers;

use App\Models\Alert;

class AlertController extends Controller
{
    public function index()
    {
        $alerts = Alert::all();
        return response()->json($alerts);
    }
}
