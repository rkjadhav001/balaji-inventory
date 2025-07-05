<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetial;
use App\Models\Product;
use App\Models\User;
use App\Models\Wholesaler;
use App\Models\BillCollection;
use App\Models\Supplier;
use App\Models\Banks;
use App\Models\BankTransaction;
use App\Models\Transaction;
use App\Models\CollectionType;
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
                $link = route('Wholesaler.Product');
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
            $order = [];
        
            if (!isset($request->status)) { 
                $order = Order::with([
                    'wholesaler',
                    'orderProduct.product' => function ($query) {
                        $query->select('*');
                    }
                ])->where('order_type', 'wholesaler')->orderByDesc('id')->get();
                foreach ($order as $ord) {
                    foreach ($ord->orderProduct as $orderProduct) {
                        if ($orderProduct['product']) {
                            // return $orderProduct['product'];
                            $stockDetails = $orderProduct['product']->stock_details;
                            $orderProduct['product']->stocks = [
                                'box' => $stockDetails['box'],
                                'patti' => $stockDetails['patti'],
                                'packet' => $stockDetails['packet'],
                            ];
                        } else {
                            
                        }
                        
                    }
                }
            } else {
                if ($request->status == 1 || $request->status == 2) {
                    $order = Order::where('status', '=', $request->status)->with([
                        'wholesaler',
                        'orderProduct.product' => function ($query) {
                            $query->select('*');
                        }
                    ])->where('order_type', 'wholesaler')->orderByDesc('id')->get();
                    foreach ($order as $ord) {
                        foreach ($ord->orderProduct as $orderProduct) {
                            if ($orderProduct['product']) {
                                // return $orderProduct['product'];
                                $stockDetails = $orderProduct['product']->stock_details;
                                $orderProduct['product']->stocks = [
                                    'box' => $stockDetails['box'],
                                    'patti' => $stockDetails['patti'],
                                    'packet' => $stockDetails['packet'],
                                ];
                            } else {
                            }
                        }
                    }
                } else {
                    $order = Order::where('status', "=", "0")->with([
                        'wholesaler',
                        'orderProduct.product' => function ($query) {
                            $query->select('*');
                        }
                    ])->where('order_type', 'wholesaler')->orderByDesc('id')->get();
                    foreach ($order as $ord) {
                        foreach ($ord->orderProduct as $orderProduct) {
                            if ($orderProduct['product']) {
                                // return $orderProduct['product'];
                                $stockDetails = $orderProduct['product']->stock_details;
                                $orderProduct['product']->stocks = [
                                    'box' => $stockDetails['box'],
                                    'patti' => $stockDetails['patti'],
                                    'packet' => $stockDetails['packet'],
                                ];
                            } else {
                            }
                        }
                    }
                }
            }
        
            return response()->json(['success' => 'true', 'data' => $order, 'message' => 'Wholesaler Order List Fetch successfully'], 200);
        } else {
            $errors = [['code' => 'auth-001', 'message' => 'Unauthorized.']];
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 401);
        }
        
    }

    public function orderUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
                'order_id' => 'required|exists:orders,id',
                'total_box' => 'required',
                'total_patti' => 'required',
                'total_packet' => 'required',
                'final_amount' => 'required',
                'products' => 'required|array', 
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $data = $this->get_admin_by_token($request);
            if (!$data) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            } 
            $order = Order::findOrFail($request->order_id);
            $order->update([
                'total_box' => $request->total_box,
                'total_patti' => $request->total_patti,
                'total_packet' => $request->total_packet,
                'final_amount' => $request->final_amount,
                    
            ]);      

            $array = ($request->products);  
            if(count($array) > 0){
                foreach ($array as $item) {
                        $orderProduct = $order->orderProduct()->find($item['order_product_id']); 
                        if ($orderProduct) {
                            $findProduct = Product::find($item['product_id']);
                            $findProduct->available_stock -= $request['total_qty'] ?? 0;
                            $findProduct->save();
                            $orderProduct->update([
                                'box' => $item['box'],
                                'patti' => $item['patti'],
                                'packet' => $item['packet'],
                                'total_qty' =>  $request['total_qty'],
                                'total_cost' => $request['total_cost'],
                            ]);
                        } else {
                            $product = Product::find($item['product_id']);
                            if ($product) {
                                $findProduct = Product::find($product->id);
                                $findProduct->available_stock -= $request['total_qty'] ?? 0;
                                $findProduct->save();
                                $orderProduct = new OrderDetial();
                                $orderProduct->order_id = $order->id;
                                $orderProduct->product_id = $product->id;
                                $orderProduct->box = $item['box'];
                                $orderProduct->patti = $item['patti'];
                                $orderProduct->packet = $item['packet'];
                                $orderProduct->price = $product->selling_price;
                                $orderProduct->total_qty = $request['total_qty'];
                                $orderProduct->total_cost = $request['total_cost'];
                                $orderProduct->save();
                            }

                            // $orderProduct->create([
                            //     'order_id' => $request->order_id,
                            //     'product_id' => $item['product_id'],
                            //     'box' => $item['box'],
                            //     'patti' => $item['patti'],
                            //     'packet' => $item['packet'],
                            //     'price' =>  $product->selling_price,
                            //     'total_qty' =>  $request['total_qty'],
                            //     'total_cost' => $request['total_cost'],
                            // ]);
                        }
                }
            } 
            $prefix = 'S-';
            // Get the last created order that matches the prefix pattern
            $lastOrder = Order::where('bill_id', 'LIKE', "{$prefix}%")
                ->orderBy('id', 'desc')
                ->first();
            if ($lastOrder) {
                // Extract the numeric part after the prefix
                $lastNumber = (int) str_replace($prefix, '', $lastOrder->bill_id);
                $orderNumber = $lastNumber + 1;
            } else {
                $orderNumber = 1;
            }
            $newOrder = Order::findOrFail($request->order_id);
            $newOrder->status = 1;
            $newOrder->supplier_id = $request->supplier_id;
            $newOrder->bill_id = $prefix . $orderNumber;
            $newOrder->save();

            
            if (count($request->collection_type) > 0)
            {
                $billCollection = new BillCollection();
                $party = Supplier::where('id', $request->supplier_id)->first();
                if ($party) {
                    $party->amount -= collect($request->collection_type)->sum('amount');
                    $party->save();
                }
                $billCollection->party_id = $request->supplier_id;
                $billCollection->bill_id = $order->id;
                $billCollection->date = $request->date ?? now();
                $billCollection->amount = collect($request->collection_type)->sum('amount');
                // $billCollection->note = null;
                $billCollection->is_bill = 1;
                $billCollection->collection_type = json_encode($request->collection_type ?? []);
                $billCollection->save();
                foreach ($request->collection_type as $collection) {
                    $collectionType = new CollectionType();
                    $collectionType->collection_id = $billCollection->id;
                    $collectionType->bill_id = $billCollection->bill_id;
                    $collectionType->name = $collection['name'] ?? null;
                    $collectionType->date = $collection['date'] ?? null;
                    $collectionType->remark = $collection['remark'] ?? null;
                    $collectionType->amount = $collection['amount'] ?? 0;
                    $collectionType->save();
                }
            }
            if (count($request->collection_type) > 0) {
                $transaction = new Transaction();
                // $transaction->type = 'sale partial';
                $transaction->party_id = $request->supplier_id;
                $transaction->total_amount = $request->final_amount;
                $transaction->pending_amount = $request->final_amount - collect($request->collection_type)->sum('amount');
                if ($transaction->pending_amount == 0) {
                    $transaction->type = 'sale paid';
                    $mainOrderFind = Order::where('id', $order->id)->first();
                    $mainOrderFind->bill_status = 1;
                    $mainOrderFind->save();
                } else {
                    $transaction->type = 'sale partial';
                }
                $transaction->transaction_type = 'sale';
                $transaction->bill_id = $order->id;
                $transaction->is_bill = 1;
                $transaction->transaction_no = $newOrder->bill_id;
                $transaction->save();
            } else {
                $transaction = new Transaction();
                $transaction->type = 'sale unpaid';
                $transaction->party_id = $request->supplier_id;
                $transaction->total_amount = $request->final_amount;
                $transaction->pending_amount = $request->final_amount;
                $transaction->bill_id = $order->id;
                $transaction->is_bill = 1;
                $transaction->transaction_type = 'sale';
                $transaction->transaction_no = $newOrder->bill_id;
                $transaction->save();
            }

            $getBillCollection = CollectionType::where('bill_id', $order->id)->get();

            if ($getBillCollection->count() > 0) {
                $groupedByDate = $getBillCollection->groupBy(function ($item) {
                    return \Carbon\Carbon::parse($item->date)->format('Y-m-d');
                });
                foreach ($groupedByDate as $date => $collectionsOnDate) {
                    $gpayAmount = $collectionsOnDate->where('name', 'G-pay')->sum('amount');
                    $cashAmount = $collectionsOnDate->where('name', 'Cash')->sum('amount');
                    if ($gpayAmount > 0) {
                        $bank = Banks::where('is_default', 1)->first();
                        $bankTransaction = new BankTransaction();
                        $bankTransaction->withdraw_from = $order->id;
                        $bankTransaction->p_type = 'sale_bill';
                        $bankTransaction->deposit_to = $bank->id;
                        $bankTransaction->balance = $gpayAmount;
                        $bankTransaction->date = $date;
                        $bankTransaction->save();
                        $bank->total_amount += $gpayAmount;
                        $bank->save();
                    }

                    if ($cashAmount > 0) {
                        $bankTransaction = new BankTransaction();
                        $bankTransaction->withdraw_from = $order->id;
                        $bankTransaction->p_type = 'sale_cash';
                        $bankTransaction->deposit_to = "Cash";
                        $bankTransaction->balance = $cashAmount;
                        $bankTransaction->date = $date;
                        $bankTransaction->save();
                    }
                }
            }

            return response()->json([
                    'success' => true,
                    'message' => 'Order updated successfully',
            ], 200);        
    }


    public function cancleOrder(Request $request){
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $order = Order::where('id', $request->order_id)->first();
            if(empty($order)){ 
                return response()->json(['success' => 'false',   'message' => 'Not Order Found'], 200);
            }
            
                Order::where('id',$order->id)->update(['status' => 2]);

                return response()->json(['success' => 'true', 'message' => 'Order Cancel successfully']);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
        }
    }

    public function pendingToOrder(Request $request){
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $order = Order::where('id', $request->order_id)->first();
            if(empty($order)){ 
                return response()->json(['success' => 'false',   'message' => 'Not Order Found'], 200);
            }
            Order::where('id',$order->id)->update(['status' => 0]);
            return response()->json(['success' => 'true', 'data' => $order, 'message' => 'Order Status Update Successfully'], 200);
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

    public function statusUpdate(Request $request)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $status = $request->status;
            $orderId = $request->order_id;
            $order = Order::where('id', $orderId)->first();
            if (isset($order)) {
                $order->status = $status;
                $order->save();
                return response()->json(['success' => 'true', 'data' => $order, 'message' => 'Wholesaler Order Status Update successfully'], 200);
            } else {
                return response()->json(['success' => 'false', 'data' => [], 'message' => 'Order not found'], 200);
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
