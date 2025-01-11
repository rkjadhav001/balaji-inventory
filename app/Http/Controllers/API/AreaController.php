<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AreaController extends Controller
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
            $areas = Area::join('cities', 'areas.city_id', '=', 'cities.id')
            ->join('states', 'areas.state_id', '=', 'states.id')
            ->where('cities.status', 1)
            ->where('states.status', 1)
            ->orderByDesc('areas.id')
            ->get(['areas.*']);
            return response()->json(['success' => 'true','data' => $areas,'message' => 'Area List Fetch successfully'], 200);
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
                'city_id' => [
                    'required',
                    Rule::exists('cities', 'id')->where('status', 1),
                ],
            ]);
            if ($validator->fails()) {
                return response()->json(['success' => 'false', 'data' => $validator->errors ()], 200);
            }
            $checkDuplicate = Area::where('name', $request->name)->where('state_id',$request->state_id)
            ->where('city_id',$request->city_id)->first();
            if ($checkDuplicate) {
                return response()->json(['success' => 'false','message' => 'Area already exists'], 200);
            } 
            $area = new Area();
            $area->name = $request->name;
            $area->state_id = $request->state_id;
            $area->city_id = $request->city_id;
            $area->save();
            return response()->json(['success' => 'true','data' => $area,'message' => 'Area created successfully'], 200);
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
            $area = Area::find($id);
            if (!$area) {
                return response()->json(['success' => 'false','message' => 'City not found'], 200);
            }
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'state_id' => [
                    'required',
                    Rule::exists('states', 'id')->where('status', 1),
                ],
                'city_id' => [
                    'required',
                    Rule::exists('cities', 'id')->where('status', 1),
                ],
            ]);
            if ($validator->fails()) {
                return response()->json(['success' => 'false', 'data' => $validator->errors ()], 200);
            }
            $checkDuplicate = Area::where('name', $request->name)->where('state_id',$request->state_id)->where('city_id',$request->city_id)->where('id','<>',$id)->first();
            if ($checkDuplicate) {
                return response()->json(['success' => 'false','message' => 'Area already exists'], 200);
            } 
            $area->name = $request->name;
            $area->state_id = $request->state_id;
            $area->city_id = $request->city_id;
            $area->save();
            return response()->json(['success' => 'true','data' => $area,'message' => 'Area updated successfully'], 200);
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
            $area = Area::find($request->id);
            if (!$area) {
                return response()->json(['success' => 'false','message' => 'Area not found'], 200);
            }
            $area->status = $request->status;
            $area->save();
            return response()->json(['success' => 'true', 'data' => $area, 'message' => 'Area status updated successfully'], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => ' Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors], 200);
        }
    }
}
