<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AppConfig;
use Illuminate\Http\Request;

class AppConfigController extends Controller
{
    public function getAppConfig(Request $request)
    {
        $config = AppConfig::whereIn('name', ['contact_number','name'])->get();
        if ($config) {
            return response()->json(['status' => true, 'data' => $config]);
        } else {
            return response()->json(['status' => false, 'message' => 'Config not found']);
        }
    }
    public function updateAppConfig(Request $request)
    {
        $config = AppConfig::where('name', 'contact_number')->first();
        if ($config) {
            $config->value = $request->value;
            $config->save();
            return response()->json(['status' => true, 'message' => 'Config updated successfully']);
        } else {
            return response()->json(['status' => false, 'message' => 'Config not found']);
        }
    }
}
