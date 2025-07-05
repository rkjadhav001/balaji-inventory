<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AppConfig;
use Illuminate\Http\Request;

class AppConfigController extends Controller
{
    public function getAppConfig(Request $request)
    {
        $config = AppConfig::whereIn('name', ['contact_number','name', 'address', 'state_id'])->get();
        if ($config) {
            return response()->json(['status' => true, 'data' => $config]);
        } else {
            return response()->json(['status' => false, 'message' => 'Config not found']);
        }
    }
    public function updateAppConfig(Request $request)
    {
        $data = $request->only(['contact_number', 'name', 'address', 'state_id']);
        foreach ($data as $key => $value) {
            $config = AppConfig::where('name', $key)->first();
            if ($config) {
                $config->value = $value;
                $config->save();
            }
        }
        return response()->json(['status' => true, 'message' => 'Configs updated successfully']);
    }
}
