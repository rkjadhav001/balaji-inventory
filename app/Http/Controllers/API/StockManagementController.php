<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class StockManagementController extends Controller
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
    
    public function available(Request $request)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $products = Product::where('status', 1)->whereRaw('CAST(available_stock AS UNSIGNED) > 0')->get();
            $products = $products->map(function ($product) {
                $stockDetails = $product->stock_details;
                return [
                    'id' => $product->id,
                    'category' => $product->category->name,
                    'stock_amount' => $product->available_stock * $product->selling_price,
                    'name' => $product->name,
                    'box' => $stockDetails['box'],
                    'patti' => $stockDetails['patti'],
                    'packet' => $stockDetails['packet'],
                ];
            });
            return response()->json(['success' => 'true', 'data' => $products, 'message' => 'Stock List Fetch successfully'], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
        }

    }

    public function lowStock(Request $request)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $products = Product::where('status', 1)
            ->whereRaw('CAST(available_stock AS UNSIGNED) <= CAST(low_stock AS UNSIGNED)')
            ->whereRaw('CAST(available_stock AS UNSIGNED) > 0')->get();
            $products = $products->map(function ($product) {
                $stockDetails = $product->stock_details;
                return [
                    'id' => $product->id,
                    'category' => $product->category->name,
                    'name' => $product->name,
                    'box' => $stockDetails['box'],
                    'patti' => $stockDetails['patti'],
                    'packet' => $stockDetails['packet'],
                ];
            });
            return response()->json(['success' => 'true', 'data' => $products, 'message' => 'Stock List Fetch successfully'], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
        }
    }

    public function outofStock(Request $request)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $products = Product::where('status', 1)->whereRaw('CAST(available_stock AS UNSIGNED) <= 0')->get();
            $products = $products->map(function ($product) {
                $stockDetails = $product->stock_details;
                return [
                    'id' => $product->id,
                    'category' => $product->category->name,
                    'name' => $product->name,
                    'box' => $stockDetails['box'],
                    'patti' => $stockDetails['patti'],
                    'packet' => $stockDetails['packet'],
                ];
            });
            return response()->json(['success' => 'true', 'data' => $products, 'message' => 'Stock List Fetch successfully'], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
        }
    }

    public function addManual(Request $request)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $product = Product::find($request->id);
            if ($product) {
                $box = $request->box * $product->packet;
                $patti = $request->patti * $product->per_patti_piece;
                $product->available_stock += $box + $patti + $request->packet;
                $product->save();
                return response()->json(['success' => 'false', 'data' => $product,'message' => 'Manual Stock Added Successfully'], 200);
            } else {
                return response()->json(['success' => 'false', 'data' => [],'message' => 'Product not found'], 200);
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

    public function minusManual(Request $request)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $product = Product::find($request->id);
            if ($product) {
                $box = $request->box * $product->packet;
                $patti = $request->patti * $product->per_patti_piece;
                $product->available_stock -= $box + $patti + $request->packet;
                $product->save();
                return response()->json(['success' => 'false', 'data' => $product,'message' => 'Manual Stock Less Successfully'], 200);
            } else {
                return response()->json(['success' => 'false', 'data' => [],'message' => 'Product not found'], 200);
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
}
