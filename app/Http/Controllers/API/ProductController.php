<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class ProductController extends Controller
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
            $products = Product::with('category')->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('categories.status', 1)
            ->get(['products.*']);
            foreach ($products as $key => $product) {
                $stockDetails = $product->stock_details;
                $product->stocks = [
                    'box' => $stockDetails['box'],
                    'patti' => $stockDetails['patti'],
                    'packet' => $stockDetails['packet'],
                ];
            }
            return response()->json(['success' => 'true','data' => $products,'message' => 'Product List Fetch successfully'], 200);

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
            $findProduct = Product::where('barcode', $request->barcode)->first();
            if ($findProduct) {
                return response()->json(['success' => 'true', 'data' => [], 'message' => 'Barcode is already exist'], 200);
            }
            $product = new Product();
            $product->name = $request->name;
            $product->short_name = $request->short_name;
            $product->hsn = $request->hsn;
            $product->barcode = $request->barcode;
            $product->category_id = $request->category_id;
            $product->unit_types = $request->unit_types;
            $product->selling_price = $request->selling_price;
            $product->box = $request->box;
            $product->packet = $request->packet;
            $product->patti = $request->patti;
            if ($request->patti) {
                $calPerPiece = $request->packet / $request->patti;
                $product->per_patti_piece = $calPerPiece;
            } else {
                $product->per_patti_piece = 0;
            }
            if ($request->thumbnail) {
                $file = $request->file('thumbnail');
                $fileName = $file->hashName();
                $path = $file->move('product/',$fileName);
                $product->thumbnail = $fileName;
            }
            $product->is_wholeseller = $request->is_wholeseller ?? 0;
            $product->low_stock = $request->low_stock ?? 0;
            $product->save();
            return response()->json(['success' => 'true', 'data' => $product, 'message' => 'Product created successfully'], 200);
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
            $product = Product::find($id);
            if ($product) {
                $findProduct = Product::where('barcode', $request->barcode)->where('id','<>',$id)->first();
                if ($findProduct) {
                    return response()->json(['success' => 'true', 'data' => [], 'message' => 'Barcode is already exist in another product'], 200);
                }
                $product->name = $request->name;
                $product->short_name = $request->short_name;
                $product->hsn = $request->hsn;
                $product->barcode = $request->barcode;
                $product->category_id = $request->category_id;
                $product->unit_types = $request->unit_types;
                $product->selling_price = $request->selling_price;
                $product->box = $request->box;
                $product->packet = $request->packet;
                $product->patti = $request->patti;
                $calPerPiece = $request->packet / $request->patti;
                $product->per_patti_piece = $calPerPiece;
                if ($request->thumbnail) {
                    $file = $request->file('thumbnail');
                    $fileName = $file->hashName();
                    $path = $file->move('product/',$fileName);
                    $product->thumbnail = $fileName;
                }
                $product->is_wholeseller = $request->is_wholeseller ?? 0;
                $product->low_stock = $request->low_stock ?? 0;
                $product->save();
                return response()->json(['success' => 'true', 'data' => $product, 'message' => 'Product updated successfully'], 200);
            } else {
                return response()->json(['success' => 'true', 'data' => [], 'message' => 'Product not found'], 200);
            }
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
            $product = Product::find($request->id);
            if (!$product) {
                return response()->json(['success' => 'false','message' => 'Product not found'], 200);
            }
            $product->status = $request->status;
            $product->save();
            return response()->json(['success' => 'true', 'data' => $product, 'message' => 'Product status updated successfully'], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => ' Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
        }
    }

    public function scanProduct(Request $request)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $product = Product::where('barcode',$request->barcode)->first();
            if (!$product) {
                return response()->json(['success' => 'false','message' => 'Product not found'], 200);
            }
            if ($product->status == 0) {
                return response()->json(['success' => 'false','message' => 'Product is not activitated'], 200);
            }
            $stockDetails = $product->stock_details; // Ensure this exists as a relationship or accessor
            $result = [
                'product' => $product,
                'stockDetails' => [
                    'box' => $stockDetails['box'] ?? 0, 
                    'patti' => $stockDetails['patti'] ?? 0,
                    'packet' => $stockDetails['packet'] ?? 0,
                ],
            ];
            return response()->json(['success' => 'true', 'data' => $result, 'message' => 'Product status updated successfully'], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => ' Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
        }
    }
}
