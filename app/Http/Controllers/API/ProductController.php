<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use App\Models\Tax;
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
            $products = Product::with('category', 'tax')->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('categories.status', 1)
            ->orderByRaw('CAST(products.product_sort AS UNSIGNED) ASC')
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
            if ($request->patti) {
                $calPerPieceCheck = $request->packet / $request->patti;

                if ($calPerPieceCheck != intval($calPerPieceCheck)) {
                    return response()->json([
                        'success' => 'false',
                        'message' => 'Please enter valid packet and patti quantities.'
                    ], 400);
                }
            }
            $product = new Product();
            $product->name = $request->name;
            $product->short_name = $request->short_name;
            $product->hsn = $request->hsn;
            $product->barcode = $request->barcode;
            $product->category_id = $request->category_id;
            $product->unit_types = $request->unit_types;
            $product->wholesaler_unit_types = $request->wholesaler_unit_types ?? '';
           
            $product->box = $request->box ?? 1;
            $product->packet = $request->packet;
            $product->tax_id = $request->tax_id;
            $product->patti = $request->patti;
            $product->sell_gst_type = $request->sell_gst_type;
            $product->purchase_gst_type = $request->purchase_gst_type;

            $tax = Tax::find($request->tax_id);
            if ($tax) {
                $gstRate = $tax ? $tax->value : 0;
            } else {
                $gstRate = 0;
            }

            // Box-level prices
            $box_selling_price = $request->selling_price ?? 0;
            $box_purchase_price = $request->purchase_price ?? 0;
            // box selling price and purchase price
            $packet_count = $request->packet ?? 1;
            $patti_count = $request->patti ?? 0;
            $packet_selling_price = ($packet_count > 0) ? $box_selling_price / $packet_count : 0;
            $packet_purchase_price = ($packet_count > 0) ? $box_purchase_price / $packet_count : 0;
            $patti_selling_price = ($patti_count > 0) ? $packet_selling_price / $patti_count : 0;
            $patti_purchase_price = ($patti_count > 0) ? $packet_purchase_price / $patti_count : 0;

            $product->box_selling_price = $request->selling_price ?? 0;
            $product->box_purchase_price = $request->purchase_price ?? 0;
            $product->patti_selling_price = round($patti_selling_price, 6);
            $product->patti_purchase_price = round($patti_purchase_price, 6);

            // $selling_price = $request->selling_price;
            // $purchase_price = $request->purchase_price ?? 0;

            $selling_price = round($packet_selling_price, 6) ?? 0;
            $purchase_price = round($packet_purchase_price, 6) ?? 0;
            
            // $selling_price = $request->selling_price;
            // $purchase_price = $request->purchase_price ?? 0;
           if ($request->sell_gst_type == 'included' && $gstRate > 0) {
                $excluded_selling_price = round($selling_price / (1 + ($gstRate / 100)), 6);
                $included_selling_price = $selling_price;
            } else {
                $excluded_selling_price = $selling_price;
                $included_selling_price = round($selling_price * (1 + ($gstRate / 100)), 6);
            }
            if ($request->purchase_gst_type == 'included' && $gstRate > 0) {
                $excluded_purchase_price = round($purchase_price / (1 + ($gstRate / 100)), 6);
                $included_purchase_price = $purchase_price;
            } else {
                $excluded_purchase_price = $purchase_price;
                $included_purchase_price = round($purchase_price * (1 + ($gstRate / 100)), 6);
            }
            $product->excluded_selling_price = $excluded_selling_price;
            $product->excluded_purchase_price = $excluded_purchase_price;
            $product->included_selling_price = $included_selling_price;
            $product->included_purchase_price = $included_purchase_price;


            if ($request->sell_gst_type == 'included') {
                $product->selling_price = $included_selling_price;
            } else {
                $product->selling_price = $included_selling_price;
            }

            if ($request->purchase_gst_type == 'included') {
                $product->purchase_price = $included_purchase_price;
            } else {
                $product->purchase_price = $included_purchase_price;
            }
            

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
            // $stockPlus  
            $product->opening_box = $request->opening_box ?? 0;
            $product->opening_patti = $request->opening_patti ?? 0;
            $product->opening_packet = $request->opening_packet ?? 0;
            if ($request->opening_box) {
                $product->available_stock = $request->packet;
            }
            if ($request->opening_patti) {
                $product->available_stock = $product->per_patti_piece;
            }
            if ($request->opening_packet) {
                $product->available_stock = $product->opening_packet;
            }
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
                
                if ($request->patti) {
                    $calPerPieceCheck = $request->packet / $request->patti;

                    if ($calPerPieceCheck != intval($calPerPieceCheck)) {
                        return response()->json([
                            'success' => 'false',
                            'message' => 'Please enter valid packet and patti quantities.'
                        ], 400);
                    }
                }
                $product->name = $request->name;
                $product->short_name = $request->short_name;
                $product->hsn = $request->hsn;
                $product->barcode = $request->barcode;
                $product->category_id = $request->category_id;
                $product->unit_types = $request->unit_types;
                $product->wholesaler_unit_types = $request->wholesaler_unit_types ?? '';
                // $product->selling_price = $request->selling_price;
                // $product->purchase_price = $request->purchase_price ?? 0;
                $product->box = $request->box ?? 1;
                $product->tax_id = $request->tax_id;
                $product->packet = $request->packet;
                $product->patti = $request->patti;
                if ($request->patti) {
                    $calPerPiece = $request->packet / $request->patti;
                    $product->per_patti_piece = $calPerPiece;
                } else {
                    $product->per_patti_piece = 0;
                }

                $product->sell_gst_type = $request->sell_gst_type;
                $product->purchase_gst_type = $request->purchase_gst_type;
                $tax = Tax::find($request->tax_id);
                if ($tax) {
                    $gstRate = $tax ? $tax->value : 0;
                } else {
                    $gstRate = 0;
                }


                 // Box-level prices
                $box_selling_price = $request->selling_price ?? 0;
                $box_purchase_price = $request->purchase_price ?? 0;
                // box selling price and purchase price
                $packet_count = $request->packet ?? 1;
                $patti_count = $request->patti ?? 0;
                $packet_selling_price = ($packet_count > 0) ? $box_selling_price / $packet_count : 0;
                $packet_purchase_price = ($packet_count > 0) ? $box_purchase_price / $packet_count : 0;
                $patti_selling_price = ($patti_count > 0) ? $packet_selling_price / $patti_count : 0;
                $patti_purchase_price = ($patti_count > 0) ? $packet_purchase_price / $patti_count : 0;

                $product->box_selling_price = $request->selling_price ?? 0;
                $product->box_purchase_price = $request->purchase_price ?? 0;
                $product->patti_selling_price = round($patti_selling_price, 6);
                $product->patti_purchase_price = round($patti_purchase_price, 6);

                // $selling_price = $request->selling_price;
                // $purchase_price = $request->purchase_price ?? 0;

                $selling_price = round($packet_selling_price, 6) ?? 0;
                $purchase_price = round($packet_purchase_price, 6) ?? 0;

                if ($request->sell_gst_type == 'included' && $gstRate > 0) {
                    $excluded_selling_price = round($selling_price / (1 + ($gstRate / 100)), 6);
                    $included_selling_price = $selling_price;
                } else {
                    $excluded_selling_price = $selling_price;
                    $included_selling_price = round($selling_price * (1 + ($gstRate / 100)), 6);
                }
                if ($request->purchase_gst_type == 'included' && $gstRate > 0) {
                    $excluded_purchase_price = round($purchase_price / (1 + ($gstRate / 100)), 6);
                    $included_purchase_price = $purchase_price;
                } else {
                    $excluded_purchase_price = $purchase_price;
                    $included_purchase_price = round($purchase_price * (1 + ($gstRate / 100)), 6);
                }
                $product->excluded_selling_price = $excluded_selling_price;
                $product->excluded_purchase_price = $excluded_purchase_price;
                $product->included_selling_price = $included_selling_price;
                $product->included_purchase_price = $included_purchase_price;

                if ($request->sell_gst_type == 'included') {
                    $product->selling_price = $included_selling_price;
                } else {
                    $product->selling_price = $included_selling_price;
                }

                if ($request->purchase_gst_type == 'included') {
                    $product->purchase_price = $included_purchase_price;
                } else {
                    $product->purchase_price = $included_purchase_price;
                }

                // $calPerPiece = $request->packet / $request->patti;
                // $product->per_patti_piece = $calPerPiece;
                if ($request->thumbnail) {
                    $file = $request->file('thumbnail');
                    $fileName = $file->hashName();
                    $path = $file->move('product/',$fileName);
                    $product->thumbnail = $fileName;
                }
                $product->is_wholeseller = $request->is_wholeseller ?? 0;
                $product->low_stock = $request->low_stock ?? 0;
                $product->opening_box = $request->opening_box ?? 0;
                $product->opening_patti = $request->opening_patti ?? 0;
                $product->opening_packet = $request->opening_packet ?? 0;
                // Calculate available stock
                $availableStock = 0;
                if ($product->opening_box) {
                    $availableStock += $product->opening_box * ($product->per_box_patti ?? 0) * ($product->per_patti_piece ?? 0);
                }
                if ($product->opening_patti) {
                    $availableStock += $product->opening_patti * ($product->per_patti_piece ?? 0);
                }
                if ($product->opening_packet) {
                    $availableStock += $product->opening_packet;
                }
                $product->available_stock = $availableStock;
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

     public function sortingUpdate(Request $request)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            foreach ($request->products as $product) {
                Product::where('id', $product['id'])->update(['product_sort' => $product['sorting']]);
            }
            $products = Product::orderByRaw('CAST(product_sort AS UNSIGNED) ASC')->get();
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
