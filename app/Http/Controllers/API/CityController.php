<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CityController extends Controller
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
            $city = City::join('states', 'cities.state_id', '=', 'states.id')
            ->where('states.status', 1)
            ->orderByDesc('cities.id')
            ->get(['cities.*']);
            return response()->json(['success' => 'true','data' => $city,'message' => 'City List Fetch successfully'], 200);
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
                'state_id' => [
                    'required',
                    Rule::exists('states', 'id')->where('status', 1),
                ],
            ]);
            if ($validator->fails()) {
                return response()->json(['success' => 'false', 'data' => $validator->errors ()], 200);
            }
            $checkDuplicate = City::where('name', $request->name)->where('state_id',$request->state_id)->first();
            if ($checkDuplicate) {
                return response()->json(['success' => 'false','message' => 'City already exists'], 200);
            } 
            $city = new City();
            $city->name = $request->name;
            $city->state_id = $request->state_id;
            $city->save();
            return response()->json(['success' => 'true','data' => $city,'message' => 'City created successfully'], 200);
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
            $city = City::find($id);
            if (!$city) {
                return response()->json(['success' => 'false','message' => 'City not found'], 200);
            }
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'state_id' => [
                    'required',
                    Rule::exists('states', 'id')->where('status', 1),
                ],
            ]);
            if ($validator->fails()) {
                return response()->json(['success' => 'false', 'data' => $validator->errors ()], 200);
            }
            $checkDuplicate = City::where('name', $request->name)->where('state_id',$request->state_id)->where('id','<>',$id)->first();
            if ($checkDuplicate) {
                return response()->json(['success' => 'false','message' => 'City already exists'], 200);
            } 
            $city->name = $request->name;
            $city->save();
            return response()->json(['success' => 'true','data' => $city,'message' => 'City updated successfully'], 200);
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
            $city = City::find($request->id);
            if (!$city) {
                return response()->json(['success' => 'false','message' => 'City not found'], 200);
            }
            $city->status = $request->status;
            $city->save();
            return response()->json(['success' => 'true', 'data' => $city, 'message' => 'City status updated successfully'], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => ' Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors], 200);
        }
    }
}
