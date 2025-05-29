<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetial;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\ReturnOrder;
use App\Models\ReturnOrderDetail;
use App\Models\TransferAmount;
use App\Models\User;
use App\Models\BillCollection;
use App\Models\CollectionType;
use App\Models\PaymentInBill;
use App\Models\BankTransaction;
use App\Models\Banks;
use Illuminate\Http\Request;
use Carbon\Carbon;

class OrderController extends Controller
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
            $order = Order::with([
                'supplier',
                'orderProduct.product' => function ($query) {
                    $query->select('id', 'name', 'short_name'); 
                }
            ])->where('order_type', 'retailer')->orderByDesc('id')->get();
            return response()->json(['success' => 'true', 'data' => $order, 'message' => 'Order List Fetch successfully'], 200);
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
            $totalBox = 0;
            $totalPatti = 0;
            $totalPacket = 0;
            $orderID = strtoupper('ORD-' . uniqid());
            foreach ($request->products as $product) {
                if (($product['box'] ?? 0) > 0 || ($product['patti'] ?? 0) > 0 || ($product['packet'] ?? 0) > 0) {
                    $totalBox += $product['box'] ?? 0;
                    $totalPatti += $product['patti'] ?? 0;
                    $totalPacket += $product['packet'] ?? 0;
                }
            }
            $order = new Order();
            $prefix = 'B';
            $year = date('y');
            $lastOrder = Order::where('order_id', 'LIKE', "{$prefix}{$year}-%")
                ->orderBy('id', 'desc')
                ->first();
            $orderNumber = $lastOrder ? ((int) explode('-', $lastOrder->order_id)[1] + 1) : 1;
            // Generate the order_id
            $order->bill_id = "{$prefix}{$year}-{$orderNumber}";
            $order->order_id = $orderID;
            $order->supplier_id = $request->supplier_id;
            $order->total_box = $totalBox;
            $order->total_patti = $totalPatti;
            $order->total_packet = $totalPacket;
            $order->products = $request->total_products;
            $order->date = $request->date;
            $order->final_amount = $request->final_amount;
            $order->order_type = 'retailer';
            $supplier = Supplier::find($request->supplier_id);
            $supplier->amount = $supplier->amount + $request->final_amount;
            $supplier->save();
            $order->save();

            foreach ($request->products as $product) {
                if (($product['box'] ?? 0) > 0 || ($product['patti'] ?? 0) > 0 || ($product['packet'] ?? 0) > 0) {
                    $orderDetail = new OrderDetial();
                    $findProduct = Product::find($product['product_id']);
                    $orderDetail->order_id = $order->id;
                    $orderDetail->product_id = $product['product_id'];
                    $orderDetail->box = $product['box'] ?? 0;
                    $orderDetail->patti = $product['patti'] ?? 0;
                    $orderDetail->packet = $product['packet'] ?? 0;
                    $orderDetail->price = $findProduct->selling_price ?? 0;
                    $orderDetail->total_qty = $product['qty'] ?? 0;
                    $orderDetail->total_cost = $product['total_amount'] ?? 0;
                    $orderDetail->save();
                    $findProduct->available_stock -= $product['qty'] ?? 0;
                    $findProduct->save();
                }
            }
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
                $transaction->save();
            }
            // $getBillCollection = CollectionType::where('bill_id', $order->id)->get();
            // if (count($getBillCollection) > 0) {
            //     $getBillCollectionGpay = CollectionType::where('bill_id', $order->id)->where('name', 'G-pay')->sum('amount');
            //     $getBillCollectionCash = CollectionType::where('bill_id', $order->id)->where('name', 'Cash')->sum('amount');
            //     if ($getBillCollectionGpay > 0) {
            //         $bank = Banks::where('is_default', 1)->first();
            //         $bankTransaction = new BankTransaction();
            //         $bankTransaction->withdraw_from = $order->id;
            //         $bankTransaction->p_type = 'sale_bill';
            //         $bankTransaction->deposit_to = $bank->id;
            //         $bankTransaction->balance = $getBillCollectionGpay;
            //         $bankTransaction->date = $request->date ?? now();
            //         $bankTransaction->save();

            //         $bank->total_amount += $getBillCollectionGpay;
            //         $bank->save();
            //     }
            //     if ($getBillCollectionCash > 0) {
            //         $bankTransaction = new BankTransaction();
            //         $bankTransaction->withdraw_from = $order->id;
            //         $bankTransaction->p_type = 'sale_cash';
            //         $bankTransaction->deposit_to = "Cash";
            //         $bankTransaction->balance = $getBillCollectionCash;
            //         $bankTransaction->date = $request->date ?? now();
            //         $bankTransaction->save();
            //     }

            //     // $bank = Banks::where('is_default',1)->first();
            //     // $bank->balance += $getBillCollection->sum('pending_amount');
            //     // $bank->save();
            // }

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


            return response()->json(['success' => 'true', 'data' => $order, 'message' => 'Order created successfully'], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
        }   
    }

   public function transactions(Request $request)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $query = Transaction::query();

            if ($request->party_id) {
                $query->where('party_id', $request->party_id);
            }

            if ($request->filter_type == 'today' && $request->has('date')) {
                $query->whereDate('date', Carbon::parse($request->date)->format('Y-m-d'));
            } elseif ($request->filter_type == 'monthly' && $request->has('month')) {
                $query->whereMonth('date', Carbon::parse($request->month)->format('m'))
                    ->whereYear('date', Carbon::parse($request->month)->format('Y'));
            } elseif ($request->filter_type == 'weekly') {
                $date = $request->has('date') ? Carbon::parse($request->date) : Carbon::now();
                $query->whereBetween('date', [
                    $date->copy()->startOfWeek(),
                    $date->copy()->endOfWeek()
                ]);
            } elseif ($request->filter_type == 'yearly' && $request->has('date')) {
                $query->whereYear('date', Carbon::parse($request->date)->format('Y'));
            } elseif ($request->filter_type == 'custom' && $request->has(['from_date', 'to_date'])) {
                $query->whereBetween('date', [
                    Carbon::parse($request->from_date)->startOfDay(),
                    Carbon::parse($request->to_date)->endOfDay()
                ]);
            }

            if ($request->transaction_type) {
                if ($request->transaction_type == 'sale') {
                    $query->where('transaction_type', 'sale');
                } elseif ($request->transaction_type == 'creditnote') {
                    $query->where('transaction_type', 'return');
                } elseif ($request->transaction_type == 'purchase') {
                    $query->where('transaction_type', 'purchase');
                } elseif ($request->transaction_type == 'debitnote') {
                    $query->where('transaction_type', 'purchase return');
                } elseif ($request->transaction_type == 'saleCreditnote') {
                    $query->where(function ($q) {
                        $q->where('transaction_type', 'sale')
                        ->orWhere('transaction_type', 'return');
                    });
                } elseif ($request->transaction_type == 'purchaseDebitnote') {
                    $query->where(function ($q) {
                        $q->where('transaction_type', 'purchase')
                        ->orWhere('transaction_type', 'purchase return');
                    });
                }
            }

            $perPage = $request->get('per_page', 25);

            $transactions = $query->with(['party', 'bill'])
                ->orderByDesc('id')
                ->paginate($perPage);

            $transactions->getCollection()->transform(function ($transaction) {
                if ($transaction->type === 'expense') {
                    $transaction->bill = $transaction->expanseBill;
                }
                if ($transaction->transaction_type === 'sale') {
                    
                }
                return $transaction;
            });
              $filteredQuery = clone $query;

            $totalSale = (clone $filteredQuery)->where('transaction_type', 'sale')->sum('total_amount');
            $totalPendingSale = (clone $filteredQuery)->where('transaction_type', 'sale')->sum('pending_amount');
            $totalReturn = (clone $filteredQuery)->where('transaction_type', 'return')->sum('pending_amount');
            $totalPurchase = (clone $filteredQuery)->where('transaction_type', 'purchase')->sum('total_amount');
            $totalPurchaseReturn = (clone $filteredQuery)->where('transaction_type', 'purchase return')->sum('total_amount');
            $totalExpenses = (clone $filteredQuery)->where('transaction_type', 'expense')->sum('total_amount');
            $totalTakePayments = (clone $filteredQuery)->where('transaction_type', 'payment_in')->sum('total_amount');

            $totalBalance = $totalSale - $totalTakePayments - $totalReturn - $totalExpenses - $totalPurchase + $totalPurchaseReturn;
            $transactionsList = [
                'total_amount' => $totalBalance,
                'net_receivable' => $totalSale - $totalReturn - $totalExpenses,
                'net_payable' => $totalPurchase - $totalPurchaseReturn,
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
                'data' => $transactions->items(),
            ];

            return response()->json([
                'success' => 'true',
                'data' => $transactionsList,
                'message' => 'Transaction List Fetch successfully'
            ], 200);
        } else {
            return response()->json([
                'success' => 'false',
                'data' => [
                    ['code' => 'auth-001', 'message' => 'Unauthorized.']
                ]
            ], 200);
        }
    }


    public function updateOrder(Request $request,$id)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $totalBox = 0;
            $totalPatti = 0;
            $totalPacket = 0;
            $orderID = strtoupper('ORD-' . uniqid());
            foreach ($request->products as $product) {
                if (($product['box'] ?? 0) > 0 || ($product['patti'] ?? 0) > 0 || ($product['packet'] ?? 0) > 0) {
                    $totalBox += $product['box'] ?? 0;
                    $totalPatti += $product['patti'] ?? 0;
                    $totalPacket += $product['packet'] ?? 0;
                }
            }
            $order = Order::find($id);
            // $prefix = 'B';
            // $year = date('y');
            // $lastOrder = Order::where('order_id', 'LIKE', "{$prefix}{$year}-%")
            //     ->orderBy('id', 'desc')
            //     ->first();
            // $orderNumber = $lastOrder ? ((int) explode('-', $lastOrder->order_id)[1] + 1) : 1;
            // // Generate the order_id
            // $order->bill_id = "{$prefix}{$year}-{$orderNumber}";
            // $order->order_id = "{$prefix}{$year}-{$orderNumber}";
            // $order->supplier_id = $request->supplier_id;
            $order->total_box = $totalBox;
            $order->total_patti = $totalPatti;
            $order->total_packet = $totalPacket;
            $order->products = $request->total_products;
            $order->date = $request->date;
            $order->final_amount = $request->final_amount;
            $order->order_type = 'retailer';

            $oldSupplier = Supplier::findOrFail($order->supplier_id);
            $supplier = Supplier::find($request->supplier_id);
            // Deduct Old Amount
            $oldSupplier->amount -= $order->final_amount;
            $oldSupplier->save();
            // Add New Amount
            $supplier->amount += $request->final_amount;
            $supplier->save();

            $order->save();

            foreach ($request->products as $product) {
                if (($product['box'] ?? 0) > 0 || ($product['patti'] ?? 0) > 0 || ($product['packet'] ?? 0) > 0) {
                    $orderDetail = OrderDetial::where('product_id',$product['product_id'])->where('order_id',$order->id)->first();
                    // Ajectst old stock
                    $findOldProduct = Product::find($product['product_id']);
                    $findOldProduct->available_stock += $orderDetail->total_qty;
                    $findOldProduct->save();
                    // Add new stock
                    $findProduct = Product::find($product['product_id']);
                    $orderDetail->order_id = $order->id;
                    $orderDetail->product_id = $product['product_id'];
                    $orderDetail->box = $product['box'] ?? 0;
                    $orderDetail->patti = $product['patti'] ?? 0;
                    $orderDetail->packet = $product['packet'] ?? 0;
                    $orderDetail->price = $findProduct->selling_price ?? 0;
                    $orderDetail->total_qty = $product['qty'] ?? 0;
                    $orderDetail->total_cost = $product['total_amount'] ?? 0;
                    $orderDetail->save();
                    $findProduct->available_stock -= $product['qty'] ?? 0;
                    $findProduct->save();
                }
            }
            if (count($request->collection_type) > 0)
            {
                $checkBillCollection = BillCollection::where('bill_id', $order->id)->where('is_bill',1)->first();
                if ($checkBillCollection) {
                    // Delete old collection
                    $deleteCollectionType = CollectionType::where('collection_id', $checkBillCollection->id)->delete();

                    $billCollection = BillCollection::find($checkBillCollection->id);
                    // party amount adjust
                    $oldPartyCollection = Supplier::where('id', $request->supplier_id)->first();
                    if ($oldPartyCollection) {
                        $oldPartyCollection->amount += $billCollection->amount;
                        $oldPartyCollection->save();
                    }
                } else {
                    $billCollection = new BillCollection();
                    $billCollection->is_bill = 1;
                }
                
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
                // $findTransaction = Transaction::where('bill_id', $order->id)->where('is_bill',1)->first();
                $findTransaction = Transaction::where('bill_id', $order->id)->where('is_bill',1)->where('transaction_type', 'sale')->first();

                if ($findTransaction) {
                    $Transaction = Transaction::find($findTransaction->id);
                    $oldPendingAmount = $Transaction->pending_amount;
                } else {
                    $Transaction = new Transaction();
                    $oldPendingAmount = 0;
                }
                $Transaction->party_id = $request->supplier_id;
                $Transaction->total_amount = $request->final_amount;
                $ReturnAmount = ReturnOrder::where('order_id', $order->id)->sum('final_amount');
                $paymentIn = PaymentInBill::where('bill_id', $order->id)->sum('amount');
                $debitTransfer = TransferAmount::where('to_transfer_id', $request->supplier_id)->where('type', 'bill')->where('bill_id', $order->id)->sum('amount');
                $creditTransfer = TransferAmount::where('from_transfer_id', $request->supplier_id)->where('type', 'bill')->where('from_bill_id', $order->id)->sum('amount');

                $collectedAmount = collect($request->collection_type)->sum('amount');
                $oldtoSettel =  ($request->final_amount - $ReturnAmount) - $paymentIn;
                $Transaction->pending_amount = $oldtoSettel - $collectedAmount - $creditTransfer + $debitTransfer;
                if ($Transaction->pending_amount == 0) {
                    $Transaction->type = 'sale paid';
                    $mainOrderFind = Order::where('id', $order->id)->first();
                    $mainOrderFind->bill_status = 1;
                    $mainOrderFind->save();
                } else {
                    $Transaction->type = 'sale partial';
                }
                // $Transaction->bill_id = $order->id;
                $Transaction->transaction_type = 'sale';
                $Transaction->save();
            }

            // $getBillCollection = CollectionType::where('bill_id', $order->id)->get();
            // if (count($getBillCollection) > 0) {
            //     $getBillCollectionGpay = CollectionType::where('bill_id', $order->id)->where('name', 'G-pay')->sum('amount');
            //     $getBillCollectionCash = CollectionType::where('bill_id', $order->id)->where('name', 'Cash')->sum('amount');
            //     if ($getBillCollectionGpay > 0) {

            //         $bank = Banks::where('is_default', 1)->first();
            //         $bankTransaction = BankTransaction::where('deposit_to', $order->id)->where('p_type', 'sale_bill')->first();
            //         if (!$bankTransaction) {
            //             $bankTransaction = new BankTransaction();
            //         }
            //         $bankTransaction->withdraw_from = $order->id;
            //         $bankTransaction->p_type = 'sale_bill';
            //         $bankTransaction->deposit_to = $bank->id;
            //         $bankTransaction->balance = $getBillCollectionGpay;
            //         $bankTransaction->date = $request->date ?? now();
            //         $bankTransaction->save();

            //         $bank->total_amount += $getBillCollectionGpay;
            //         $bank->save();
            //     }
            //     if ($getBillCollectionCash > 0) {

            //         $bankTransaction = BankTransaction::where('withdraw_from', $order->id)->where('p_type', 'sale_cash')->first();
            //         if (!$bankTransaction) {
            //             $bankTransaction = new BankTransaction();
            //         }
            //         $bankTransaction->withdraw_from = $order->id;
            //         $bankTransaction->p_type = 'sale_cash';
            //         $bankTransaction->deposit_to = "Cash";
            //         $bankTransaction->balance = $getBillCollectionCash;
            //         $bankTransaction->date = $request->date ?? now();
            //         $bankTransaction->save();
            //     }
            // }

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
                        $bankTransaction = BankTransaction::where('withdraw_from', $order->id)
                            ->where('p_type', 'sale_bill')
                            ->whereDate('date', $date)
                            ->first();
                        if (!$bankTransaction) {
                            $bankTransaction = new BankTransaction();
                        }
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
                        $bankTransaction = BankTransaction::where('withdraw_from', $order->id)
                            ->where('p_type', 'sale_cash')
                            ->whereDate('date', $date)
                            ->first();
                        if (!$bankTransaction) {
                            $bankTransaction = new BankTransaction();
                        }
                        $bankTransaction->withdraw_from = $order->id;
                        $bankTransaction->p_type = 'sale_cash';
                        $bankTransaction->deposit_to = 'Cash';
                        $bankTransaction->balance = $cashAmount;
                        $bankTransaction->date = $date;
                        $bankTransaction->save();
                    }
                }
            }

            return response()->json(['success' => 'true', 'data' => $order, 'message' => 'Order updated successfully'], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
        }
    }

    public function editReturn(Request $request, $id)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $returnOrder = ReturnOrder::where('id', $id)->first();
            $order = Order::with([
                'supplier',
                'orderProduct.product' => function ($query) {
                    $query->select('*'); 
                }
            ])->where('order_type', 'retailer')->where('id', $returnOrder->order_id)->first();
            foreach ($order->orderProduct as $key => $orderProduct) {
                $returnProduct = ReturnOrderDetail::where('return_order_id', $id)->where('product_id', $orderProduct->product_id)->first();
                if ($returnProduct) {
                    $orderProduct->added_qty = [
                        'box' => $returnProduct->box,
                        'patti' => $returnProduct->patti,
                        'packet' => $returnProduct->packet
                    ];
                } else {
                    $orderProduct->added_qty = [
                        'box' => 0,
                        'patti' => 0,
                        'packet' => 0
                    ];
                }
            }
            if ($order) {
                return response()->json(['success' => 'true', 'data' => $order, ' message' => 'Order View Successfully'], 200);
            } else {
                return response()->json(['success' => 'false', 'data' => [], 'message' => 'Order Not Found'], 200);
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

    public function billReturnStore(Request $request,$orderID)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $totalBox = 0;
            $totalPatti = 0;
            $totalPacket = 0;
            foreach ($request->products as $product) {
                if (($product['box'] ?? 0) > 0 || ($product['patti'] ?? 0) > 0 || ($product['packet'] ?? 0) > 0) {
                    $totalBox += $product['box'] ?? 0;
                    $totalPatti += $product['patti'] ?? 0;
                    $totalPacket += $product['packet'] ?? 0;
                }
            }
            $order = ReturnOrder::find($orderID);
            $oldFinalAmount = $order->final_amount;
            $mainOrderFind = Order::where('id', $order->order_id)->first();
            // $order->order_id = $orderID;
            $order->supplier_id = $mainOrderFind->supplier_id;
            $order->total_box = $totalBox;
            $order->total_patti = $totalPatti;
            $order->total_packet = $totalPacket;
            $order->products = $request->total_products;
            $order->final_amount = $request->final_amount;
            $order->order_type = 'retailer';
            $supplier = Supplier::find($mainOrderFind->supplier_id);
            $supplier->amount -= $request->final_amount;
            $supplier->save();
            $order->save();
            foreach ($request->products as $product) {
                if (($product['box'] ?? 0) > 0 || ($product['patti'] ?? 0) > 0 || ($product['packet'] ?? 0) > 0) {
                    $checkProduct = ReturnOrderDetail::where('return_order_id', $order->id)->where('product_id', $product['product_id'])->first();
                    if ($checkProduct) { 
                        $orderDetail = ReturnOrderDetail::find($checkProduct->id);
                        $findProduct = Product::find($product['product_id']);
                        $orderDetail->order_id = $mainOrderFind->id;
                        $orderDetail->return_order_id = $order->id;
                        $orderDetail->product_id = $product['product_id'];
                        $orderDetail->box = $product['box'] ?? 0;
                        $orderDetail->patti = $product['patti'] ?? 0;
                        $orderDetail->packet = $product['packet'] ?? 0;
                        $orderDetail->price = $findProduct->selling_price ?? 0;
                        $orderDetail->total_qty = $product['qty'] ?? 0;
                        $orderDetail->total_cost = $product['total_amount'] ?? 0;
                        $orderDetail->save();
                        $oldproductQty = $orderDetail->total_qty;
                        $newproductQty = $findProduct->available_stock - $oldproductQty;
                        $findProduct->available_stock = $newproductQty + $product['qty'] ?? 0;
                        $findProduct->save();
                    } else {
                        $orderDetail = new ReturnOrderDetail();
                        $findProduct = Product::find($product['product_id']);
                        $orderDetail->order_id = $mainOrderFind->id;
                        $orderDetail->return_order_id = $order->id;
                        $orderDetail->product_id = $product['product_id'];
                        $orderDetail->box = $product['box'] ?? 0;
                        $orderDetail->patti = $product['patti'] ?? 0;
                        $orderDetail->packet = $product['packet'] ?? 0;
                        $orderDetail->price = $findProduct->selling_price ?? 0;
                        $orderDetail->total_qty = $product['qty'] ?? 0;
                        $orderDetail->total_cost = $product['total_amount'] ?? 0;
                        $orderDetail->save();
                        $findProduct->available_stock += $product['qty'] ?? 0;
                        $findProduct->save();
                    }
                }
            }

            $findTransaction = Transaction::where('bill_id', $mainOrderFind->id)->where('is_bill',1)->where('transaction_type','sale')->first();
            if ($findTransaction) {
                $Transaction = Transaction::find($findTransaction->id);
            } else {
                $Transaction = new Transaction();
            }
            $Transaction->pending_amount = ($Transaction->pending_amount + $oldFinalAmount) - $request->final_amount;
            if ($Transaction->pending_amount == 0) {
                $Transaction->type = 'sale paid';
                $mainOrderFind->bill_status = 1;
                $mainOrderFind->save();
            } else {
                $Transaction->type = 'sale partial';
            }
            $Transaction->transaction_type = 'sale';
            $Transaction->save();
            $returnTransaction = Transaction::where('bill_id', $order->id)->where('transaction_type','return')->first();
            $returnTransaction->total_amount = $mainOrderFind->final_amount;
            $returnTransaction->pending_amount = $request->final_amount;
            // $returnTransaction->type = 'sale return';
            $Transaction->party_id = $mainOrderFind->supplier_id;
            $Transaction->bill_id = $order->id;
            $returnTransaction->save();

            return response()->json(['success' => 'true', 'data' => $order, 'message' => 'Return order updated successfully'], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
        }   
    }

    public function deleteTransaction(Request $request, $id)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $transaction = Transaction::where('id', $id)->first();
            if ($transaction) {
                if ($transaction->transaction_type == 'sale') {
                    if ($transaction->is_bill == 1) {
                        $order = Order::where('id', $transaction->bill_id)->first();
                        if ($order) {
                            $orderReturn = ReturnOrder::where('order_id', $order->id)->first();
                            if ($orderReturn) {
                                $returnOrderDetail = ReturnOrderDetail::where('return_order_id', $orderReturn->id)->get();
                                foreach ($returnOrderDetail as $returnOrderDetails) {
                                    $returnOrderDetails->delete();
                                }
                                Transaction::where('id', $orderReturn->id)->where('transaction_type', 'return')->delete();
                                $orderReturn->delete();
                            }
                            $billCollection = BillCollection::where('bill_id', $order->id);
                            if ($billCollection) {
                                $collectionType = CollectionType::where('bill_id', $order->id)->delete();
                                $billCollection->delete();
                            }
                            $PaymentInBill = PaymentInBill::where('bill_id', $order->id)->get();
                            foreach ($PaymentInBill as $paymentInBill) {
                                $paymentInTransaction = Transaction::where('id', $paymentInBill->transaction_id)->where('transaction_type', 'payment_in')->first();
                                if (count($PaymentInBill) > 1) {
                                    if ($paymentInTransaction) {
                                        $paymentInTransaction->total_amount -= $paymentInBill->amount;
                                        $paymentInTransaction->save();
                                        $paymentInBill->delete();
                                    }
                                } else {
                                    $paymentInTransaction->delete();
                                }
                            }

                            $orderDetail = OrderDetial::where('order_id', $order->id)->get();
                            foreach ($orderDetail as $orderDetails) {
                                $findProduct = Product::find($orderDetails->product_id);
                                $findProduct->available_stock += $orderDetails->qty;
                                $findProduct->save();
                                $orderDetails->delete();
                            }
                        }
                        $supplier = Supplier::find($transaction->party_id);
                        $supplier->amount -= $transaction->final_amount;
                        $supplier->save();
                        $order->delete();
                        $transaction->delete();
                    } else {
                        if ($transaction->transaction_type == 'return')
                        $order = ReturnOrder::where('id', $transaction->bill_id)->first();
                        $mainOrderFind = Order::where('id', $order->order_id)->first();

                        $findTransaction = Transaction::where('bill_id', $mainOrderFind->id)->where('is_bill',1)->where('transaction_type','sale')->first();
                        if ($findTransaction) {
                            $Transaction = Transaction::find($findTransaction->id);
                        } else {
                            $Transaction = new Transaction();
                        }
                        $Transaction->pending_amount = $Transaction->pending_amount - $order->final_amount;
                        if ($Transaction->pending_amount == 0) {
                            $Transaction->type = 'sale paid';
                            $mainOrderFind->bill_status = 1;
                            $mainOrderFind->save();
                        } else {
                            $Transaction->type = 'sale partial';
                        }
                        $Transaction->transaction_type = 'sale';
                        $Transaction->save();

                        $supplier = Supplier::find($order->supplier_id);
                        $supplier->amount -= $order->final_amount;
                        $supplier->save();
                        $orderDetail = ReturnOrderDetail::where('return_order_id', $order->id)->get();
                        foreach ($orderDetail as $orderDetails) {
                            $findProduct = Product::find($orderDetails->product_id);
                            $findProduct->available_stock -= $product['qty'] ?? 0;
                            $findProduct->save();
                            $orderDetails->delete();
                        }
                        $order->delete();
                        $transaction->delete();
                    }
                    return response()->json(['success' => 'true', 'data' => [], 'message' => 'Transaction Delete successfully'], 200);
                } elseif ($transaction->transaction_type == 'payment_in') {

                    $PaymentInBill = PaymentInBill::where('transaction_id', $transaction->id)->get();
                    foreach ($PaymentInBill as $paymentInBill) {
                        $biilTransaction = Transaction::where('bill_id', $paymentInBill->bill_id)->where('transaction_type', 'sale')->first();
                        if ($biilTransaction) {
                            $biilTransaction->pending_amount += $paymentInBill->amount;
                        if ($biilTransaction->pending_amount == 0) {
                            $biilTransaction->type = 'sale paid';
                            $mainOrderFind = Order::where('id', $paymentInBill->bill_id)->first();
                            $mainOrderFind->bill_status = 1;
                            $mainOrderFind->save();
                        } else {
                            $biilTransaction->type = 'sale partial';
                            $mainOrderFind = Order::where('id', $paymentInBill->bill_id)->first();
                            $mainOrderFind->bill_status = 0;
                            $mainOrderFind->save();
                        }
                            $biilTransaction->save();
                        }

                        // $paymentInTransaction = Transaction::where('id', $paymentInBill->transaction_id)->first();
                        // if ($paymentInTransaction) {
                        //     $paymentInTransaction->total_amount -= $paymentInBill->amount;
                        //     $paymentInTransaction->save();
                        // }
                        $paymentInBill->delete();
                    }
                    $transaction->delete();
                }
            } else {
                return response()->json(['success' => 'true', 'data' => [], 'message' => 'Transaction Not Found'], 200);
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
