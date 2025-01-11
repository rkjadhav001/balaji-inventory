<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SupplierController extends Controller
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
            $supplier = Supplier::orderByDesc('id')->get();
            return response()->json(['success' => 'true','data' => $supplier,'message' => 'Supplier List Fetch successfully'], 200);
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
            $supplier = new Supplier();
            $supplier->name = $request->name;
            $supplier->phone_number = $request->phone_number;
            $supplier->address = $request->address;
            $supplier->save();
            return response()->json(['success' => 'true','data' => $supplier,'message' => 'Supplier created successfully'], 200);
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
            $supplier = Supplier::find($id);
            if (!$supplier) {
                return response()->json(['success' => 'false','message' => 'Supplier not found'], 200);
            }
            $supplier->name = $request->name;
            $supplier->phone_number = $request->phone_number;
            $supplier->address = $request->address;
            $supplier->save();
            return response()->json(['success' => 'true','data' => $supplier,'message' => 'Supplier updated successfully'], 200);
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
