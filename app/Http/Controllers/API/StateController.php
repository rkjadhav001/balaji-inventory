<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\State;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StateController extends Controller
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
            $states = State::orderByDesc('id')->get();
            return response()->json(['success' => 'true','data' => $states,'message' => 'State List Fetch successfully'], 200);
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
            $checkDuplicate = State::where('name', $request->name)->first();
            if ($checkDuplicate) {
                return response()->json(['success' => 'false','message' => 'State already exists'], 200);
            } 
            $state = new State();
            $state->name = $request->name;
            $state->save();
            return response()->json(['success' => 'true','data' => $state,'message' => 'State created successfully'], 200);
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
            $state = State::find($id);
            if (!$state) {
                return response()->json(['success' => 'false','message' => 'State not found'], 200);
            }
            $validator = Validator::make($request->all(), [
                'name' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['success' => 'false', 'data' => $validator->errors ()], 200);
            }
            $checkDuplicate = State::where('name', $request->name)->where('id','<>',$id)->first();
            if ($checkDuplicate) {
                return response()->json(['success' => 'false','message' => 'State already exists'], 200);
            } 
            $state->name = $request->name;
            $state->save();
            return response()->json(['success' => 'true','data' => $state,'message' => 'State updated successfully'], 200);
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
            $state = State::find($request->id);
            if (!$state) {
                return response()->json(['success' => 'false','message' => 'State not found'], 200);
            }
            $state->status = $request->status;
            $state->save();
            return response()->json(['success' => 'true', 'data' => $state, 'message' => 'State status updated successfully'], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => ' Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors], 200);
        }
    }
}
