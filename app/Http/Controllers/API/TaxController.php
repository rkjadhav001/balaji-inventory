<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Tax;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaxController extends Controller
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
            $tax = Tax::orderByDesc('id')->get();
            return response()->json(['success' => 'true','data' => $tax,'message' => 'Tax List Fetch successfully'], 200);
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
                'value' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['success' => 'false', 'data' => $validator->errors ()], 200);
            }
            $checkDuplicate = Tax::where('name', $request->name)->where('value',$request->value)->first();
            if ($checkDuplicate) {
                return response()->json(['success' => 'false','message' => 'Tax already exists'], 200);
            }
            $tax = new Tax();
            $tax->name = $request->name;
            $tax->value = $request->value;
            $tax->save();
            return response()->json(['success' => 'true','data' => $tax,'message' => 'Tax created successfully'], 200);
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
            $tax = Tax::find($id);
            if (!$tax) {
                return response()->json(['success' => 'false','message' => 'Tax not found'], 200);
            }
            $checkDuplicate = Tax::where('name', $request->name)->where('value',$request->value)->where('id','<>',$id)->first();
            if ($checkDuplicate) {
                return response()->json(['success' => 'false','message' => 'Tax already exists'], 200);
            }
            $tax->name = $request->name;
            $tax->value = $request->value;
            $tax->save();
            return response()->json(['success' => 'true', 'data' => $tax, 'message' => 'Tax updated successfully'], 200);
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
