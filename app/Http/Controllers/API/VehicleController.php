<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VehicleController extends Controller
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
            $vehicle = Vehicle::orderByDesc('id')->get();
            return response()->json(['success' => 'true','data' => $vehicle,'message' => 'Vehicle List Fetch successfully'], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
        }
    }

    public function create(Request $request)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['success' => 'false', 'data' => $validator->errors ()], 200);
            }
            $vehicle = new Vehicle();
            $vehicle->name = $request->name;
            $vehicle->plate_number = $request->plate_number;
            $vehicle->short_name = $request->short_name;
            $vehicle->save();
            return response()->json(['success' => 'true','data' => $vehicle,'message' => 'Vehicle created successfully'], 200);
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
            $validator = Validator::make($request->all(), [
                'name' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['success' => 'false', 'data' => $validator->errors ()], 200);
            }
            $vehicle = Vehicle::find($id);
            if (!$vehicle) {
                return response()->json(['success' => 'false','message' => 'Vehicle not found'], 200);
            }
            $vehicle->name = $request->name;
            $vehicle->plate_number = $request->plate_number;
            $vehicle->short_name = $request->short_name;
            $vehicle->save();
            return response()->json(['success' => 'true','data' => $vehicle,'message' => 'Vehicle created successfully'], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
        }
    }

    public function status(Request $request)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $vehicle = Vehicle::find($request->id);
            if (!$vehicle) {
                return response()->json(['success' => 'false','message' => 'Vehicle not found'], 200);
            }
            $vehicle->status = $request->status;
            $vehicle->save();
            return response()->json(['success' => 'true', 'data' => $vehicle, 'message' => 'Vehicle status updated successfully'], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => ' Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors], 200);
        }
    }
}
