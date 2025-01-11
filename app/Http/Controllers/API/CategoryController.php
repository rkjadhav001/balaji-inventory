<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;

class CategoryController extends Controller
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
            $categories = Category::orderByDesc('id')->get();
            return response()->json(['success' => 'true','data' => $categories,'message' => 'Categories Fetch successfully'], 200);
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
        // return $request->all();
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $checkDuplicate = Category::where('name', $request->name)->first();
            if ($checkDuplicate) {
                return response()->json(['success' => 'false','message' => 'Category already exists'], 200);
            } 
            $category = new Category();
            $category->name = $request->name;
            if ($request->image) {
                $file = $request->file('image');
                $fileName = $file->hashName();
                $path = $file->move('category/',$fileName);
                $category->image = $fileName;
            }
            $category->save();
            return response()->json(['success' => 'true','data' => $category,'message' => 'Category created successfully'], 200);
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
            $category = Category::find($id);
            if (!$category) {
                return response()->json(['success' => 'false','message' => 'Category not found'], 200);
            }
            $checkDuplicate = Category::where('name', $request->name)->where('id','<>',$id)->first();
            if ($checkDuplicate) {
                return response()->json(['success' => 'false','message' => 'Category already exists'], 200);
            } 
            $category->name = $request->name;
            if ($request->image) {
                $file = $request->file('image');
                $fileName = $file->hashName();
                $path = $file->move('category/',$fileName);
                $category->image = $fileName;
            }
            $category->save();
            return response()->json(['success' => 'true','data' => $category,'message' => 'Category updated successfully'], 200);
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
            $category = Category::find($request->id);
            if (!$category) {
                return response()->json(['success' => 'false','message' => 'Category not found'], 200);
            }
            $category->status = $request->status;
            $category->save();
            return response()->json(['success' => 'true', 'data' => $category, 'message' => 'Category status updated successfully'], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => ' Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors], 200);
        }
    }
}
