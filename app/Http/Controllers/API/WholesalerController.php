<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Wholesaler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WholesalerController extends Controller
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

    public function list(Request $request)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $wholesalers = Wholesaler::orderByDesc('id')->get();
            foreach ($wholesalers as $key => $wholesaler) {
                $link = route('Wholesaler.Product', $wholesaler->unique_id);
                $wholesaler->link = $link;
            }
            
            return response()->json(['success' => 'true','data' => $wholesalers,'message' => 'Wholesaler List Fetch successfully'], 200);
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
                'phone_number' => 'required || unique:wholesalers,phone_number',
            ]);
            if ($validator->fails()) {
                return response()->json(['success' => 'false', 'data' => $validator->errors ()], 200);
            }
            $wholesaler = new Wholesaler();
            $wholesaler->unique_id = uniqid();
            $wholesaler->name = $request->name;
            $wholesaler->phone_number = $request->phone_number;
            $wholesaler->wp_number = $request->wp_number;
            $wholesaler->address = $request->address;
            $wholesaler->save();
            return response()->json(['success' => 'true','data' => $wholesaler,'message' => 'wholesalers created successfully'], 200);
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
            $uniqeStaff = Wholesaler::where('phone_number', $request->phone_number)->where('id','<>', $id)->first();
            if ($uniqeStaff) {
                return response()->json(['success' => 'false', 'data' => [], 'message' => 'Phone number already exists'], 200);
            }
            $staff = Wholesaler::find($id);
            $staff->name = $request->name;
            $staff->phone_number = $request->phone_number;
            $staff->wp_number = $request->wp_number;
            $staff->address = $request->address;
            $staff->save();
            return response()->json(['success' => 'true','data' => $staff,'message' => 'wholesalers created successfully'], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
        }
    }

    public function order(Request $request)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $order = Order::with([
                'wholesaler',
                'orderProduct.product' => function ($query) {
                    $query->select('id', 'name'); 
                }
            ])->where('order_type', 'wholesaler')->orderByDesc('id')->get();
            return response()->json(['success' => 'true', 'data' => $order, 'message' => 'Wholesaler Order List Fetch successfully'], 200);

        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
        }
    }

    public function products(Request $request)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $products = Product::where('is_wholeseller', 1)->orderByRaw('CAST(sorting AS UNSIGNED) ASC')->get();
            return response()->json(['success' => 'true', 'data' => $products, 'message' => 'Wholesaler Product List Fetch successfully'], 200);

        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
        }
    }

    public function sortingUpdate(Request $request)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            foreach ($request->products as $product) {
                Product::where('id', $product['id'])->update(['sorting' => $product['sorting']]);
            }
            $products = Product::where('is_wholeseller', 1)->orderByRaw('CAST(sorting AS UNSIGNED) ASC')->get();
            return response()->json([
                'success' => true,
                'data' => $products,
                'message' => 'Products Sorting Updated Successfully.'
            ]);
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
