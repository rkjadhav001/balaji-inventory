<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request) 
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
        ]);
        if($validator->fails()){
            return response()->json(['success' => 'false','message' => 'Email is required'], 200);      
        }
        $user = User::where('email', $request->email)->where('role','admin')->first();
        if ($user) {
            if (Auth::attempt(['email' => $request->email, 'password' => $request->password, 'role' => 'admin'])) {
                $authUser = User::find(Auth::user()->id);
                $token = $authUser->createToken('MyAuthApp')->plainTextToken;
                User::where(['id' => $authUser->id])->update(['remember_token' => $token, 'device_id' => $request->device_id]);
                $list = User::where('id',$authUser->id)->first();
                return response()->json(['success' => 'true','token' => $token,'data' => $list,'message' => 'Login Successfully'], 200);
            } else {
                return response()->json(['success' => 'false','message' => 'Credentials not match.'], 200);
            }
        } else {
            return response()->json(['success' => 'false','token' => 'N/A','data' => 'N/A','message' => 'Invalid credentials.'], 200);
        }
    }
}
