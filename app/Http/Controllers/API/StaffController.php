<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class StaffController extends Controller
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
            $staff = Staff::orderByDesc('id')->get();
            return response()->json(['success' => 'true','data' => $staff,'message' => 'Staff List Fetch successfully'], 200);
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
                'phone_number' => 'required || unique:staff,phone_number',
            ]);
            if ($validator->fails()) {
                return response()->json(['success' => 'false', 'data' => $validator->errors ()], 200);
            }
            $staff = new Staff();
            $staff->name = $request->name;
            $staff->phone_number = $request->phone_number;
            $staff->password = Hash::make($request->password);
            $staff->save();
            return response()->json(['success' => 'true','data' => $staff,'message' => 'Staff created successfully'], 200);
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
            $uniqeStaff = Staff::where('phone_number', $request->phone_number)->where('id','<>', $id)->first();
            if ($uniqeStaff) {
                return response()->json(['success' => 'false', 'data' => [], 'message' => 'Phone number already exists'], 200);
            }
            $staff = Staff::find($id);
            $staff->name = $request->name;
            $staff->phone_number = $request->phone_number;
            if ($request->password) {
                $staff->password = Hash::make($request->password);
            }
            $staff->save();
            return response()->json(['success' => 'true','data' => $staff,'message' => 'Staff update successfully'], 200);
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
