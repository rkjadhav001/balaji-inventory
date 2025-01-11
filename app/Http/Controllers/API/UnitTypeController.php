<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\UnitType;
use App\Models\User;
use Illuminate\Http\Request;

class UnitTypeController extends Controller
{
    public function get_admin_by_token($request)
    {
        $data = '';
        $success = 0;
        $token = explode(' ', $request->header('authorization'));
        if (count($token) > 1 && strlen($token[1]) > 30) {
            $employee = User::where(['remember_token' => $token['1']])->where('role','admin')->first();
            if (isset($employee)) {
                $data = $employee;
                $success = 1;
                return [
                    'success' => $success,
                    'data' => $data
                ];
            } else {
            }
        } else {
        }
    }
    
    public function index(Request $request)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $unitType = UnitType::orderByDesc('id')->get();
            return response()->json(['success' => 'true','data' => $unitType,'message' => 'UnitType List Fetch successfully'], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
        }
    }

    public function update(Request $request, $id)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $unitType = UnitType::find($id);
            if (!$unitType) {
                return response()->json(['success' => 'false','message' => 'UnitType not found'], 200);
            }
            $checkDuplicate = UnitType::where('name', $request->name)->where('id','<>',$id)->first();
            if ($checkDuplicate) {
                return response()->json(['success' => 'false','message' => 'UnitType already exists'], 200);
            }
            $unitType->name = $request->name;
            $unitType->save();
            return response()->json(['success' => 'true', 'data' => $unitType, 'message' => 'UnitType updated successfully'], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
        }
    }
}
