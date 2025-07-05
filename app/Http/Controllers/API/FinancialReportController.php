<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BillCollection;
use App\Models\CollectionType;
use App\Models\Expense;
use App\Models\ExpenseDetail;
use App\Models\ExpanseCategory;
use App\Models\Order;
use App\Models\OrderDetial;
use App\Models\PaymentInBill;
use App\Models\DuePayment;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseReturnInvoice;
use App\Models\Product;
use App\Models\ReturnOrder;
use App\Models\ReturnOrderDetail;
use App\Models\Transaction;
use App\Models\Supplier;
use App\Models\TransferAmount;
use App\Models\BankTransaction;
use App\Models\Banks;
use App\Models\Tax;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinancialReportController extends Controller
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

    // public function partyList(Request $request)
    // {
    //     $data = $this->get_admin_by_token($request);
    //     if ($data) {
    //         $suppliers = Supplier::orderByDesc('id')->get();
    //         foreach ($suppliers as $key => $supplier) {
    //             $sale = Order::where('supplier_id', $supplier->id)->sum('final_amount');
    //             $collection = BillCollection::where('party_id', $supplier->id)->sum('amount');
    //             $returnBill = ReturnOrder::where('supplier_id', $supplier->id)->sum('final_amount');
    //             $expanses = Expense::where('party_id', $supplier->id)->sum('total_amount');
    //             $creditTransfer = TransferAmount::with('creditedParty', 'debitedParty')->where('to_transfer_id', $supplier->id)->where('type', 'bill')->sum('amount');
    //             $debitTransfer = TransferAmount::where('from_transfer_id', $supplier->id)->where('type', 'bill')->sum('amount');
    //             $purchaseInvoice = PurchaseInvoice::where('party_id', $supplier->id)->sum('total_payable_amount');
    //             $purchaseInvoicePayment = PurchaseInvoice::where('party_id', $supplier->id)->sum('payment_amount');
    //             $purchaseReturnInvoice = PurchaseReturnInvoice::where('party_id', $supplier->id)->sum('total_payable_amount');
    //             $duePaymentCash = DuePayment::where('party_id', $supplier->id)->sum('cash_amount');
    //             $duePaymentBank = DuePayment::where('party_id', $supplier->id)->sum('bank_amount');
    //             $paymentIn = Transaction::where('party_id', $supplier->id)->where('transaction_type', 'payment_in')->sum('total_amount');
    //             $supplier->sale = $sale;
    //             $supplier->collection = $collection;
    //             // $supplier->pending = $sale - $collection - $returnBill - $expanses;
    //             $supplier->pending = $sale - $collection - $paymentIn - $returnBill - $expanses + $creditTransfer - $debitTransfer - $purchaseInvoice - $purchaseInvoicePayment + $purchaseReturnInvoice + $duePaymentCash + $duePaymentBank;
    //             $supplier->outstanding = 0.00;
    //             $supplier->return = $returnBill;
    //             $netReceivable = $sale - $collection - $paymentIn - $returnBill - $expanses - $creditTransfer + $debitTransfer;
    //             $netPayable = $purchaseInvoice - $purchaseInvoicePayment - $purchaseReturnInvoice - $duePaymentCash - $duePaymentBank;
    //         }
    //         return response()->json(['success' => 'true','data' => $suppliers, 'net_receivable' => $netReceivable, 'net_payable' => $netPayable,'message' => 'Supplier List Fetch successfully'], 200);
    //     } else {
    //         $errors = [];
    //         array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
    //         return response()->json([
    //             'success' => 'false',
    //             'data' => $errors
    //         ], 200);
    //     }
    // }

    public function partyList(Request $request)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $suppliers = Supplier::orderByDesc('id')->get();
            $totalNetReceivable = 0;
            $totalNetPayable = 0;
            foreach ($suppliers as $key => $supplier) {
                $party_id = $supplier->id;
                 $saleMinnusTransactions = Transaction::where('party_id', $party_id)
                ->where('transaction_type', 'sale')
                ->where('pending_amount', '<', 0)
                ->sum('pending_amount');
                $rawPending = round($saleMinnusTransactions, 2);               // -1602.60
                $totalPendingAmount = round(abs($saleMinnusTransactions), 2);  // 1602.60

                $sale = Order::where('supplier_id', $party_id)->sum('final_amount');
                $collection = BillCollection::where('party_id', $party_id)->sum('amount');
                $returnBill = ReturnOrder::where('supplier_id', $party_id)->sum('final_amount');
                $expanses = Expense::where('party_id', $party_id)->sum('total_amount');

                $creditTransfer = TransferAmount::where('to_transfer_id', $party_id)
                                                ->where('type', 'bill')
                                                ->sum('amount');
                $debitTransfer = TransferAmount::where('from_transfer_id', $party_id)
                                            ->where('type', 'bill')
                                            ->sum('amount');

                $purchaseInvoice = PurchaseInvoice::where('party_id', $party_id)->sum('total_payable_amount');
                $purchaseInvoicePayment = PurchaseInvoice::where('party_id', $party_id)->sum('payment_amount');
                $purchaseReturnInvoice = PurchaseReturnInvoice::where('party_id', $party_id)->sum('total_payable_amount');
                $duePaymentCash = DuePayment::where('party_id', $party_id)->sum('cash_amount');
                $duePaymentBank = DuePayment::where('party_id', $party_id)->sum('bank_amount');
                $paymentIn = Transaction::where('party_id', $party_id)
                                        ->where('transaction_type', 'payment_in')
                                        ->sum('total_amount');

                // Correct Calculations
                $netReceivable = ($sale ?? 0) 
                            - ($collection ?? 0) 
                            - ($returnBill ?? 0) 
                            - ($paymentIn ?? 0) 
                            - ($debitTransfer ?? 0) 
                            + ($creditTransfer ?? 0) 
                            - ($expanses ?? 0)
                            + ($totalPendingAmount ?? 0);

                $netPayable = $purchaseInvoice 
                            - $purchaseInvoicePayment 
                            - $purchaseReturnInvoice 
                            - $duePaymentCash 
                            - $duePaymentBank
                            + $totalPendingAmount;

                $netReceivable = round($netReceivable, 2);
                $totalNetReceivable += $netReceivable;
                $netPayable = round($netPayable, 2);
                $totalNetPayable += $netPayable;
                
                $supplier->pending = ($netPayable) - ($netReceivable);
                $supplier->outstanding = 0.00;
                $supplier->return = $returnBill;
                $netReceivable = $sale - $collection - $paymentIn - $returnBill - $expanses - $creditTransfer + $debitTransfer;
                $netPayable = $purchaseInvoice - $purchaseInvoicePayment - $purchaseReturnInvoice - $duePaymentCash - $duePaymentBank;
            }
            return response()->json(['success' => 'true','data' => $suppliers, 'net_receivable' => $netReceivable, 'net_payable' => $netPayable,'message' => 'Supplier List Fetch successfully'], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
        }
    }

    public function partySellslist(Request $request)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $perPage = $request->get('per_page', 25);
            $order = Order::where('supplier_id', $request->party_id)->where('order_type', 'retailer')->orderByDesc('id')->paginate($perPage);
            foreach ($order as $key => $orders) {
                $collection = BillCollection::where('party_id', $request->party_id)->where('bill_id', $orders->id)->sum('amount');
                $returnBill = ReturnOrder::where('supplier_id', $request->party_id)->where('order_id', $orders->id)->sum('final_amount');
                $expanses = Expense::where('party_id', $request->party_id)->where('bill_id', $orders->id)->sum('total_amount');
                $transfer = TransferAmount::where('from_transfer_id', $request->party_id)->where('type', 'bill')->where('bill_id', $orders->id)->sum('amount');
                $orders->pending = $orders->final_amount - $collection - $returnBill - $expanses - $transfer;
            }
            $SellList = [
                'current_page' => $order->currentPage(),
                'last_page' => $order->lastPage(),
                'per_page' => $order->perPage(),
                'total' => $order->total(),
                'data' => $order->items(),
            ];
            return response()->json(['success' => 'true', 'data' => $SellList, 'message' => 'Sell List Fetch successfully'], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
        } 
    }

    public function billCollection(Request $request)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $billCollection = new BillCollection();
            $party = Supplier::where('id', $request->party_id)->first();
            if ($party) {
                $party->amount -= $request->amount;
                $party->save();
            }
            $billCollection->party_id = $request->party_id;
            $billCollection->bill_id = $request->bill_id;
            $billCollection->date = $request->date;
            // $billCollection->cash = $request->cash ?? 0;
            // $billCollection->expenses = $request->expenses ?? 0;
            // $billCollection->g_pay = $request->g_pay ?? 0;
            $billCollection->amount = $request->amount;
            $billCollection->note = $request->note;
            $billCollection->collection_type = json_encode($request->collection_type ?? []);
            $billCollection->save();

            foreach ($request->collection_type as $collection) {
                $collectionType = new CollectionType();
                $collectionType->collection_id = $billCollection->id;
                $collectionType->bill_id = $billCollection->bill_id;
                $collectionType->name = $collection['name'] ?? null;
                $collectionType->remark = $collection['remark'] ?? null;
                $collectionType->amount = $collection['amount'] ?? 0;
                $collectionType->save();
            }
            
            return response()->json(['success' => 'true', 'data' => $billCollection, 'message' => 'Bill Collection Add Successfully'], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
        } 
    }

    public function billCollectionList(Request $request)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $perPage = $request->get('per_page', 25);
            $order = BillCollection::where('party_id', $request->party_id)->orderByDesc('id')->paginate($perPage);
            foreach ($order as $key => $bill) {
                $orderget = Order::where('id', $bill->bill_id)->first();
                $bill->bill_no = $orderget->bill_id;
                $bill->collection_type = json_decode($bill->collection_type);
            }
            $SellList = [
                'current_page' => $order->currentPage(),
                'last_page' => $order->lastPage(),
                'per_page' => $order->perPage(),
                'total' => $order->total(),
                'data' => $order->items(),
            ];
            return response()->json(['success' => 'true', 'data' => $SellList, 'message' => 'Sell List Fetch successfully'], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
        } 
    }

    public function dateWisePartyBills(Request $request)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $perPage = $request->get('per_page', 25);
            $orderQuery = Order::where('order_type', 'retailer')->orderByDesc('id');

            if ($request->filter_type == 'daily' && $request->has('order_date')) {
                $orderQuery->whereDate('date', Carbon::parse($request->order_date)->format('Y-m-d'));
            } elseif ($request->filter_type == 'monthly' && $request->has('month')) {
                $orderQuery->whereMonth('date', Carbon::parse($request->month)->format('m'))
                           ->whereYear('date', Carbon::parse($request->month)->format('Y'));
            } elseif ($request->filter_type == 'custom' && $request->has(['from_date', 'to_date'])) {
                $orderQuery->whereBetween('date', [
                    Carbon::parse($request->from_date)->startOfDay(),
                    Carbon::parse($request->to_date)->endOfDay()
                ]);
            }

            $paginatedOrders = $orderQuery->paginate($perPage);
            $orderData = collect($paginatedOrders->items())->groupBy(function ($order) {
                return Carbon::parse($order->date)->format('d-m-Y');
            });
            $SellList = [];
            foreach ($orderData as $date => $orders) {
                $supplierDetails = $orders->groupBy('supplier_id')->map(function ($supplierOrders, $supplierId) {
                    $sale = Order::where('supplier_id', $supplierId)->where('order_type', 'retailer')->sum('final_amount');
                    $collection = BillCollection::where('party_id', $supplierId)->sum('amount');
                    $ReturnAmount = ReturnOrder::where('supplier_id', $supplierId)->sum('final_amount');
                    return [
                        'id' => $supplierId,
                        'name' => $supplierOrders->first()->supplier->name ?? 'Unknown',
                        'total_orders' => $supplierOrders->count(),
                        'sale' => $sale,
                        'collection' => $collection,
                        'pending' => $supplierOrders->first()->supplier->amount,
                        'outstanding' => 0.00,
                        'return' => $ReturnAmount,
                        'amount' => $supplierOrders->sum('final_amount'),
                    ];
                })->values();
                $SellList[] = [
                    'date' => $date,
                    'total_suppliers' => $supplierDetails->count(),
                    'suppliers' => $supplierDetails,
                    'total_amount' => $orders->sum('final_amount'),
                    // 'orders' => $orders->toArray(),
                ];
            }
            return response()->json([
                'success' => true,
                'data' => [
                    'current_page' => $paginatedOrders->currentPage(),
                    'last_page' => $paginatedOrders->lastPage(),
                    'per_page' => $paginatedOrders->perPage(),
                    'total' => $paginatedOrders->total(),
                    'data' => $SellList
                ],
                'message' => 'Sell List fetched successfully'
            ], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
        } 
    }

    public function report(Request $request)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $orderQuery = Order::where('order_type', 'retailer')->orderByDesc('id');
            $collectionQuery = BillCollection::orderByDesc('id');
            if ($request->filter_type == 'daily' && $request->has('order_date')) {
                $orderQuery->whereDate('date', Carbon::parse($request->order_date)->format('Y-m-d'));
                $collectionQuery->whereDate('date', Carbon::parse($request->order_date)->format('Y-m-d'));
            } elseif ($request->filter_type == 'monthly' && $request->has('month')) {
                $orderQuery->whereMonth('date', Carbon::parse($request->month)->format('m'))
                           ->whereYear('date', Carbon::parse($request->month)->format('Y'));
                $collectionQuery->whereMonth('date', Carbon::parse($request->month)->format('m'))
                           ->whereYear('date', Carbon::parse($request->month)->format('Y'));
            } elseif ($request->filter_type == 'custom' && $request->has(['from_date', 'to_date'])) {
                $orderQuery->whereBetween('date', [
                    Carbon::parse($request->from_date)->startOfDay(),
                    Carbon::parse($request->to_date)->endOfDay()
                ]);
                $collectionQuery->whereBetween('date', [
                    Carbon::parse($request->from_date)->startOfDay(),
                    Carbon::parse($request->to_date)->endOfDay()
                ]);
            }
            $totalSale = $orderQuery->sum('final_amount');
            $totalCollection = $collectionQuery->sum('amount');
            return response()->json([
                'success' => true,
                'data' => [
                    'total_sale' => $totalSale,
                    'total_collection' => $totalCollection,
                    'total_expense' => 0.00,
                    'total_pending' => 0.00,
                ],
                'message' => 'financial Report fetched successfully'
            ], 200);

        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
        } 
    }

    public function dateWisePartyCollections(Request $request)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $perPage = $request->get('per_page', 25);
            $collectionQuery = BillCollection::orderByDesc('id');

            if ($request->filter_type == 'daily' && $request->has('order_date')) {
                $collectionQuery->whereDate('created_at', Carbon::parse($request->order_date)->format('Y-m-d'));
            } elseif ($request->filter_type == 'monthly' && $request->has('month')) {
                $collectionQuery->whereMonth('created_at', Carbon::parse($request->month)->format('m'))
                           ->whereYear('created_at', Carbon::parse($request->month)->format('Y'));
            } elseif ($request->filter_type == 'custom' && $request->has(['from_date', 'to_date'])) {
                $collectionQuery->whereBetween('created_at', [
                    Carbon::parse($request->from_date)->startOfDay(),
                    Carbon::parse($request->to_date)->endOfDay()
                ]);
            }

            $paginatedCollections = $collectionQuery->paginate($perPage);
            $collationData = collect($paginatedCollections->items())->groupBy(function ($collection) {
                return Carbon::parse($collection->created_at)->format('d-m-Y');
            });
            $SellList = [];
            foreach ($collationData as $date => $collections) {
                $supplierDetails = $collections->groupBy('party_id')->map(function ($supplierCollections, $supplierId) {
                    $sale = Order::where('supplier_id', $supplierId)->where('order_type', 'retailer')->sum('final_amount');
                    $collectionParty = BillCollection::where('party_id', $supplierId)->sum('amount');
                    return [
                        'id' => $supplierId,
                        'name' => $supplierCollections->first()->supplier->name ?? 'Unknown',
                        'total_orders' => $supplierCollections->count(),
                        'sale' => $sale,
                        'collection' => $collectionParty,
                        'pending' => $sale - $collectionParty,
                        'outstanding' => 0.00,
                        'amount' => $supplierCollections->sum('amount'),
                    ];
                })->values();
                $SellList[] = [
                    'date' => $date,
                    'total_suppliers' => $supplierDetails->count(),
                    'suppliers' => $supplierDetails,
                    'total_amount' => $collections->sum('amount'),
                    // 'orders' => $orders->toArray(),
                ];
            }
            return response()->json([
                'success' => true,
                'data' => [
                    'current_page' => $paginatedCollections->currentPage(),
                    'last_page' => $paginatedCollections->lastPage(),
                    'per_page' => $paginatedCollections->perPage(),
                    'total' => $paginatedCollections->total(),
                    'data' => $SellList
                ],
                'message' => 'Collection List fetched successfully'
            ], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
        } 
    }

    public function billCollectionUpdate(Request $request, $id)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $billCollection = BillCollection::find($id);
            if ($billCollection) {
                $party = Supplier::where('id', $billCollection->party_id)->first();
                if ($party) {
                    $updateAmount = ($party->amount - $billCollection->amount) + $request->amount;
                    $party->amount -= $updateAmount;
                    $party->save();
                }
                // $billCollection->party_id = $request->party_id;
                // $billCollection->bill_id = $request->bill_id;
                $billCollection->date = $request->date;
                $billCollection->amount = $request->amount;
                $billCollection->note = $request->note;
                $billCollection->collection_type = json_encode($request->collection_type ?? []);
                $billCollection->save();
                CollectionType::where('collection_id', $billCollection->id)->delete();  
                foreach ($request->collection_type as $collection) {
                    $collectionType = new CollectionType();
                    $collectionType->collection_id = $billCollection->id;
                    $collectionType->bill_id = $billCollection->bill_id;
                    $collectionType->name = $collection['name'] ?? null;
                    $collectionType->remark = $collection['remark'] ?? null;
                    $collectionType->amount = $collection['amount'] ?? 0;
                    $collectionType->save();
                }
                return response()->json(['success' => 'true', 'data' => $billCollection, 'message' => 'Bill Collection Add Successfully'], 200);
            } else {
                return response()->json(['success' => 'false', 'data' => [], 'message' => 'Bill Collection Not Found'], 200);
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

    public function billReturn(Request $request,$id)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $order = Order::with([
                'supplier',
                'orderProduct.product' => function ($query) {
                    $query->select('*'); 
                }
            ])->where('id', $id)->first();
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
            $mainOrderFind = Order::where('id', $orderID)->first();
            $order = new ReturnOrder();
            // Get the last created order that matches the prefix pattern
            $prefix = 'SR-';
            $lastOrder = ReturnOrder::where('bill_id', 'LIKE', "{$prefix}%")
                ->orderBy('id', 'desc')
                ->first();
            if ($lastOrder) {
                $lastNumber = (int) str_replace($prefix, '', $lastOrder->bill_id);
                $orderNumber = $lastNumber + 1;
            } else {
                $orderNumber = 1;
            }
            // Generate the bill_id
            $order->bill_id = $prefix . $orderNumber;
            $order->order_id = $orderID;
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
                    $orderDetail = new ReturnOrderDetail();
                    $findProduct = Product::find($product['product_id']);
                    $orderDetail->order_id = $orderID;
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

            // if (count($request->collection_type) > 0) {
                $findTransaction = Transaction::where('bill_id', $orderID)->where('is_bill',1)->where('transaction_type', 'sale')->first();
                if ($findTransaction) {
                    $Transaction = Transaction::find($findTransaction->id);
                } else {
                    $Transaction = new Transaction();
                }
                // $Transaction->party_id = $request->supplier_id;
                // $Transaction->total_amount = $request->final_amount;
                $Transaction->pending_amount = ($Transaction->pending_amount ?? 0) - $request->final_amount;
                if ($Transaction->pending_amount == 0) {
                    $Transaction->type = 'sale paid';
                    $mainOrderFind->bill_status = 1;
                    $mainOrderFind->save();
                } else {
                    $Transaction->type = 'sale partial';
                }
                // $Transaction->bill_id = $order->id;
                // }
                $Transaction->transaction_type = 'sale';
                $Transaction->save();
            $returnTransaction = new Transaction();
            $returnTransaction->total_amount = $mainOrderFind->final_amount;
            $returnTransaction->pending_amount = $request->final_amount;
            $returnTransaction->transaction_type = 'return';
            $returnTransaction->type = 'sale return';
            $returnTransaction->party_id = $mainOrderFind->supplier_id;
            $returnTransaction->bill_id = $order->id;
            $returnTransaction->transaction_no = $order->bill_id;
            $returnTransaction->save();

            return response()->json(['success' => 'true', 'data' => $order, 'message' => 'Order Return successfully'], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
        }   
    }

    public function billView(Request $request,$id)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $order = Order::with([
                'supplier',
                'orderProduct.product' => function ($query) {
                    $query->select('id', 'name', 'short_name'); 
                }
            ])->where('id', $id)->first();
            
            if ($order) {
                if ($order->order_type == 'retailer') {
                    $order->return_amount = ReturnOrder::where('order_id', $id)->sum('final_amount');
                    foreach ($order->orderProduct as $key => $product) {
                        $returnOrder = ReturnOrderDetail::where('order_id', $id)->where('product_id', $product->product_id)->first();
                        $order->orderProduct[$key]->return_order = $returnOrder;
                    }
                    $collections = BillCollection::where('bill_id', $id)->get();
                    foreach ($collections as $key => $collection) {
                        $collection->collection_type = json_decode($collection->collection_type);
                    }
                    $paymentIn = PaymentInBill::where('bill_id', $id)->get();
                    $paymentInAmount = PaymentInBill::where('bill_id', $id)->sum('amount');
                    $creditTransfer = TransferAmount::with('creditedParty', 'debitedParty')->where('to_transfer_id', $order->supplier_id)->where('type', 'bill')->where('bill_id', $id)->sum('amount');
                    $creditTransferData = TransferAmount::with('debitedParty','creditedParty')->where('to_transfer_id', $order->supplier_id)->where('type', 'bill')->where('bill_id', $id)->get();
                    $debitTransfer = TransferAmount::where('from_transfer_id', $order->supplier_id)->where('type', 'bill')->where('from_bill_id', $id)->sum('amount');
                    $debitTransferData = TransferAmount::with('debitedParty','creditedParty')->where('from_transfer_id', $order->supplier_id)->where('type', 'bill')->where('from_bill_id', $id)->get();
    
                    return response()->json(['success' => 'true', 'data' => [
                        'order' => $order,
                        'collection' => $collections,
                        'payment_in' => $paymentIn,
                        'paymentInAmount' => $paymentInAmount,
                        'creditAmount' => $creditTransfer,
                        'debitAmount' => $debitTransfer,
                        'creditTransferData' => $creditTransferData,
                        'debitTransferData' => $debitTransferData
                    ], ' message' => 'Order View Successfully'], 200);
                } else {
                    $order->return_amount = ReturnOrder::where('order_id', $id)->sum('final_amount');
                    foreach ($order->orderProduct as $key => $product) {
                        $returnOrder = ReturnOrderDetail::where('order_id', $id)->where('product_id', $product->product_id)->first();
                        $order->orderProduct[$key]->return_order = $returnOrder;
                    }
                    $collections = BillCollection::where('bill_id', $id)->get();
                    foreach ($collections as $key => $collection) {
                        $collection->collection_type = json_decode($collection->collection_type);
                    }
                    $paymentIn = PaymentInBill::where('bill_id', $id)->get();
                    $paymentInAmount = PaymentInBill::where('bill_id', $id)->sum('amount');
                    $creditTransfer = TransferAmount::with('creditedParty', 'debitedParty')->where('to_transfer_id', $order->supplier_id)->where('type', 'bill')->where('bill_id', $id)->sum('amount');
                    $creditTransferData = TransferAmount::with('debitedParty','creditedParty')->where('to_transfer_id', $order->supplier_id)->where('type', 'bill')->where('bill_id', $id)->get();
                    $debitTransfer = TransferAmount::where('from_transfer_id', $order->supplier_id)->where('type', 'bill')->where('from_bill_id', $id)->sum('amount');
                    $debitTransferData = TransferAmount::with('debitedParty','creditedParty')->where('from_transfer_id', $order->supplier_id)->where('type', 'bill')->where('from_bill_id', $id)->get();
    
                    return response()->json(['success' => 'true', 'data' => [
                        'order' => $order,
                        'collection' => $collections,
                        'payment_in' => $paymentIn,
                        'paymentInAmount' => $paymentInAmount,
                        'creditAmount' => 0,
                        'debitAmount' => 0,
                        'creditTransferData' => [],
                        'debitTransferData' => []
                    ], ' message' => 'Order View Successfully'], 200);
                }
                
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

    public function transferAmount(Request $request)
    {
        // from in amount is minus and to in amount is plus
        $data = $this->get_admin_by_token($request);
        if ($data) {
            // return $request->all();
            $transfer = new TransferAmount();
            $transfer->date = $request->date;
            $transfer->from_transfer_id = $request->from_id;
            $transfer->to_transfer_id = $request->to_id;
            $transfer->amount = $request->amount;
            if ($request->type == 'bill') {
                $transfer->bill_id = $request->bill_id;
                $transfer->from_bill_id = $request->from_bill_id;
            }
            $fromParty = Supplier::where('id', $request->from_id)->first();
            if ($fromParty) {
                $fromParty->amount = $fromParty->amount - $request->amount;
                $fromParty->save();
            }
            $toParty = Supplier::where('id', $request->to_id)->first();
            if ($toParty) {
                $toParty->amount = $toParty->amount + $request->amount;
                $toParty->save();
            }
            $transfer->type = $request->type;
            $transfer->note = $request->note;
            $transfer->created_by = $data['data']->id;
            // $transfer->transaction_type = 'transfer';
            $transfer->save();
            $findTransaction = Transaction::where('bill_id', $request->bill_id)->where('is_bill',1)->where('transaction_type', 'sale')->first();
            $findTransaction->pending_amount = ($findTransaction->pending_amount ?? 0) + $request->amount;
            if ($findTransaction->pending_amount == 0)
            {
                $findTransaction->type = 'sale paid';
                $mainOrderFind = Order::where('id', $request->bill_id)->first();
                $mainOrderFind->bill_status = 1;
                $mainOrderFind->save();
            } else {
                $findTransaction->type = 'sale partial';
                $mainOrderFind = Order::where('id', $request->bill_id)->first();
                $mainOrderFind->bill_status = 0;
                $mainOrderFind->save();
            }
            $findTransaction->save();

            $fromfindTransaction = Transaction::where('bill_id', $request->from_bill_id)->where('is_bill',1)->where('transaction_type', 'sale')->first();
            $fromfindTransaction->pending_amount = ($fromfindTransaction->pending_amount ?? 0) - $request->amount;
            if ($fromfindTransaction->pending_amount == 0)
            {
                $fromfindTransaction->type = 'sale paid';
                $mainOrderFind = Order::where('id', $request->from_bill_id)->first();
                $mainOrderFind->bill_status = 1;
                $mainOrderFind->save();
            } else {
                $fromfindTransaction->type = 'sale partial';
                $mainOrderFind = Order::where('id', $request->from_bill_id)->first();
                $mainOrderFind->bill_status = 0;
                $mainOrderFind->save();
            }
            $fromfindTransaction->save();
            return response()->json(['success' => 'true', 'data' => $transfer, 'message' => 'Amount Transfer Successfully'], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
        } 
    }

    public function transferAmountList(Request $request)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $perPage = $request->get('per_page', 25);
            $transfer = TransferAmount::orderByDesc('id')->where('from_transfer_id', $request->party_id)->get();
            foreach ($transfer as $key => $transfers) {
                $fromParty = Supplier::where('id', $transfers->from_transfer_id)->first();
                $toParty = Supplier::where('id', $transfers->to_transfer_id)->first();
                $transfers->from_party = $fromParty->name ?? 'Unknown';
                $transfers->to_party = $toParty->name ?? 'Unknown';
            }
            return response()->json(['success' => 'true', 'data' => $transfer, 'message' => 'Transfer Amount List Fetch successfully'], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
        } 
    }

    public function expenseAdd(Request $request)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $expense = new Expense();
            $prefix = 'EX-';
            $lastOrder = Expense::where('bill_id', 'LIKE', "{$prefix}%")
                ->orderBy('id', 'desc')
                ->first();
            if ($lastOrder) {
                $lastNumber = (int) str_replace($prefix, '', $lastOrder->bill_id);
                $orderNumber = $lastNumber + 1;
            } else {
                $orderNumber = 1;
            }
            // Generate the bill_id
            $expense->bill_id = $prefix . $orderNumber;
            $expense->date = $request->date;
            $expense->name = $request->name;
            $expense->payment_type = $request->payment_type;
            $expense->total_amount = $request->total_amount;
            $expense->note = $request->note;
            $expense->type = 'category';
            $expense->cash_payment = $request->cash_payment;
            $expense->bank_payment = $request->bank_payment;
            // $expense->created_by = $data['data']->id;
            $expense->save();

            $transaction = new Transaction();
            $transaction->transaction_type = 'expense';
            $transaction->party_id = 0;
            $transaction->total_amount = $request->total_amount;
            $transaction->pending_amount = $request->total_amount;
            $transaction->type = 'expense';
            $transaction->bill_id = $expense->id;
            $transaction->is_bill = 1;
            $transaction->transaction_no = $expense->bill_id;
            $transaction->date = $request->date;
            
            $transaction->save();

            
            foreach ($request->expense_types as $expenseType) {
                $expanseType = new ExpenseDetail();
                $expanseType->expense_id = $expense->id;
                $expanseType->category = $request->name;
                $expanseType->type = $expenseType['type'] ?? null;
                $expanseType->qty = $expenseType['qty'] ?? null;
                $expanseType->rate = $expenseType['rate'] ?? null;
                $expanseType->amount = $expenseType['amount'] ?? 0;
                $expanseType->tax_id = $expenseType['tax_id'] ?? 0;
                $expanseType->save();
            }

            if ($request->payment_type == 'manual') {
                if ($request->cash_payment > 0) {
                    $bankTransactionCash = new BankTransaction();
                    $bankTransactionCash->withdraw_from = "Cash";
                    $bankTransactionCash->p_type = 'expense_payment_cash';
                    $bankTransactionCash->deposit_to = $expense->id;
                    $bankTransactionCash->balance = $request->cash_payment;
                    $bankTransactionCash->date = $request->date ?? now();
                    $bankTransactionCash->save();
                } 
                if ($request->bank_payment > 0) {
                    $bank = Banks::where('is_default', 1)->first();
                    $bankTransactionCash = new BankTransaction();
                    $bankTransactionCash->withdraw_from = $bank->id;
                    $bankTransactionCash->p_type = 'expense_payment_bank';
                    $bankTransactionCash->deposit_to = $expense->id;
                    $bankTransactionCash->balance = $request->bank_payment;
                    $bankTransactionCash->date = $request->date ?? now();
                    $bankTransactionCash->save();
                    $bank->total_amount = $bank->total_amount - $request->bank_payment;
                    $bank->save();
                }
            } else {
                if ($request->payment_type == 'cash') {
                    $bankTransactionCash = new BankTransaction();
                    $bankTransactionCash->withdraw_from = "Cash";
                    $bankTransactionCash->p_type = 'expense_payment_cash';
                    $bankTransactionCash->deposit_to = $expense->id;
                    $bankTransactionCash->balance = $request->total_amount;
                    $bankTransactionCash->date = $request->date ?? now();
                    $bankTransactionCash->save();
                } else {
                    $bank = Banks::where('is_default', 1)->first();
                    $bankTransactionCash = new BankTransaction();
                    $bankTransactionCash->withdraw_from = $bank->id;
                    $bankTransactionCash->p_type = 'expense_payment_bank';
                    $bankTransactionCash->deposit_to = $expense->id;
                    $bankTransactionCash->balance = $request->total_amount;
                    $bankTransactionCash->date = $request->date ?? now();
                    $bankTransactionCash->save();
                    $bank->total_amount = $bank->total_amount - $request->total_amount;
                    $bank->save();
                }
            }

            
            return response()->json(['success' => 'true', 'data' => $expense, 'message' => 'Expanses Add successfully'], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
        } 
    }

    public function expenseList(Request $request)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $perPage = $request->get('per_page', 25);
            if ($request->type == 'transaction') {
                $query = Expense::query();
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
                $expense = $query->orderByDesc('id')->paginate($perPage);
                foreach ($expense as $key => $transaction) {
                    $transaction->expense_detail = ExpenseDetail::where('expense_id', $transaction->id)->get();
                }
                
            } elseif ($request->type == 'category') {
               
                $expense = ExpanseCategory::select('name', 'type')->paginate($perPage);
                foreach ($expense as $key => $expenses) {
                    $expenses->type = $expenses->type ?? 'category';
                    $expenses->total_amount = Expense::where('name', $expenses->name)->sum('total_amount');
                    $getAllExpanse = Expense::where('name', $expenses->name)->get();
                    // $expenseIds = $getAllExpanse->pluck('id')->toArray();
                    // $expenses->expense_items = ExpenseDetail::with('expense')->whereIn('expense_id', $expenseIds)->get();
                    $expenses->expense_items = Expense::where('name', $expenses->name)->get();
                }
                // $expense = Expense::select('type', 'name', DB::raw('SUM(total_amount) as total_amount'))
                // ->where('type', $request->type)
                // ->groupBy('type', 'name')
                // ->orderByDesc('id')
                // ->paginate($perPage);
                // foreach ($expense as $key => $expenses) {
                //     $getAllExpanse = Expense::where('name', $expenses->name)->get();
                //     $expenses->expense_items = Expense::where('name', $expenses->name)->get();
                // }
            } elseif ($request->type == 'item') {
                $expense = ExpenseDetail::select('type', DB::raw('SUM(amount) as total_amount'))
                ->groupBy('type')
                ->paginate($perPage);
            
                foreach ($expense as $itemExpense) {
                    // $itemExpense->category_name = $itemExpense->category->name ?? 'Uncategorized';
                    $getAllExpanse = ExpenseDetail::where('type', $itemExpense->type)->get();
                    $expenseIds = $getAllExpanse->pluck('expense_id')->toArray();
                    $itemExpense->expense_category = Expense::whereIn('id',$expenseIds)->get();
                }
            }
        
            $expanseList = [
                'current_page' => $expense->currentPage(),
                'last_page' => $expense->lastPage(),
                'per_page' => $expense->perPage(),
                'total' => $expense->total(),
                'data' => $expense->items(),
            ];
            return response()->json(['success' => 'true', 'data' => $expanseList, 'message' => 'Expanses List Fetch successfully'], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
        } 
    }

    public function expenseDetail(Request $request)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $perPage = $request->get('per_page', 25);
            $expense = Expense::where('id', $request->expanse_id)
            ->paginate($perPage);
            foreach ($expense as $key => $expenses) {
                $expenses->expense_types = ExpenseDetail::where('expense_id', $expenses->id)->get()->map(function($i){
                    $tax = Tax::where('id', $i->tax_id)->first();
                    if($tax){
                        $i->tax_type = $tax->name;
                        $i->gst_amount = ($i->amount * $tax->value) / 100;
                        $i->value =   $tax->value;
                    }else{
                        $i->tax_type = '';
                        $i->gst_amount = '';
                        $i->value = '';
                    }
                    return $i;
                }); ;
            }
        
            $expanseList = [
                'current_page' => $expense->currentPage(),
                'last_page' => $expense->lastPage(),
                'per_page' => $expense->perPage(),
                'total' => $expense->total(),
                'data' => $expense->items(),
            ];
            return response()->json(['success' => 'true', 'data' => $expanseList, 'message' => 'Expanses List Fetch successfully'], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
        } 

    }

    public function updateExpense(Request $request, $id)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $expense = Expense::where('id', $id)->first();
            if ($expense) {
                // Store old values for transaction reversal
                $oldExpense = $expense->replicate();
                
                // Update expense
                $expense->date = $request->date;
                $expense->name = $request->name;
                $expense->payment_type = $request->payment_type;
                $expense->total_amount = $request->total_amount;
                $expense->note = $request->note;
                $expense->type = $request->type;
                $expense->cash_payment = $request->cash_payment ?? 0;
                $expense->bank_payment = $request->bank_payment ?? 0;
                $expense->save();

                // First, reverse old transactions
                $this->reverseOldTransactions($oldExpense);

                // Handle new transactions based on payment type
                if ($request->payment_type == 'manual') {
                    // Manual payment - handle both cash and bank
                    if ($request->cash_payment > 0) {
                        $this->createCashTransaction($expense, $request->cash_payment, $request->date);
                    }
                    
                    if ($request->bank_payment > 0) {
                        $this->createBankTransaction($expense, $request->bank_payment, $request->date);
                    }
                } else {
                    // Single payment type - cash or bank
                    if ($request->payment_type == 'cash') {
                        $this->createCashTransaction($expense, $request->total_amount, $request->date);
                    } else if ($request->payment_type == 'bank') {
                        $this->createBankTransaction($expense, $request->total_amount, $request->date);
                    }
                }
                
                // Update main transaction record
                $transaction = Transaction::where('bill_id', $id)->where('type', 'expense')->first();
                if ($transaction) {
                    $transaction->transaction_type = 'expense';
                    $transaction->party_id = 0;
                    $transaction->total_amount = $request->total_amount;
                    $transaction->pending_amount = $request->total_amount;
                    $transaction->type = 'expense';
                    $transaction->bill_id = $expense->id;
                    $transaction->is_bill = 1;
                    $transaction->date = $request->date;
                    $transaction->save();
                }
                
                // Update expense details
                ExpenseDetail::where('expense_id', $id)->delete();
                foreach ($request->expense_types as $expenseType) {
                    $expanseType = new ExpenseDetail();
                    $expanseType->expense_id = $expense->id;
                    $expanseType->category = $request->name;
                    $expanseType->type = $expenseType['type'] ?? null;
                    $expanseType->qty = $expenseType['qty'] ?? null;
                    $expanseType->rate = $expenseType['rate'] ?? null;
                    $expanseType->amount = $expenseType['amount'] ?? 0;
                    $expanseType->tax_id = $expenseType['tax_id'] ?? 0;
                    $expanseType->save();
                }
                
                return response()->json(['success' => 'true', 'data' => $expense, 'message' => 'Expense updated successfully'], 200);
            } else {
                return response()->json(['success' => 'true', 'data' => [], 'message' => 'Expense not found'], 200);
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

    private function reverseOldTransactions($oldExpense)
    {
        // Reverse old cash transactions
        $oldCashTransaction = BankTransaction::where('p_type', 'expense_payment_cash')
            ->where('deposit_to', $oldExpense->id)
            ->first();
        if ($oldCashTransaction) {
            $oldCashTransaction->delete();
        }

        // Reverse old bank transactions and update bank balance
        $oldBankTransaction = BankTransaction::where('p_type', 'expense_payment_bank')
            ->where('deposit_to', $oldExpense->id)
            ->first();
        if ($oldBankTransaction) {
            $bank = Banks::find($oldBankTransaction->withdraw_from);
            if ($bank) {
                // Add back the old amount to bank balance
                $bank->total_amount += $oldBankTransaction->balance;
                $bank->save();
            }
            $oldBankTransaction->delete();
        }
    }

    private function createCashTransaction($expense, $amount, $date)
    {
        $bankTransactionCash = new BankTransaction();
        $bankTransactionCash->withdraw_from = "Cash";
        $bankTransactionCash->p_type = 'expense_payment_cash';
        $bankTransactionCash->deposit_to = $expense->id;
        $bankTransactionCash->balance = $amount;
        $bankTransactionCash->date = $date ?? now();
        $bankTransactionCash->save();
    }

    private function createBankTransaction($expense, $amount, $date)
    {
        $bank = Banks::where('is_default', 1)->first();
        if ($bank) {

            $bankTransactionBank = new BankTransaction();
            $bankTransactionBank->withdraw_from = $bank->id;
            $bankTransactionBank->p_type = 'expense_payment_bank';
            $bankTransactionBank->deposit_to = $expense->id;
            $bankTransactionBank->balance = $amount;
            $bankTransactionBank->date = $date ?? now();
            $bankTransactionBank->save();

            // Update bank balance
            $bank->total_amount -= $amount;
            $bank->save();
        }
    }

    public function deleteExpense(Request $request, $id)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $transaction = Transaction::where('id', $id)->where('transaction_type','expense')->first();
            if ($transaction) {
                $expense = Expense::where('id', $transaction->bill_id)->first();
                ExpenseDetail::where('expense_id', $expense->id)->delete();
                if ($expense->payment_type == 'cash') {
                    $bankTransactionCash = BankTransaction::where('p_type', 'expense_payment_cash')->where('deposit_to', $expense->id)->delete();
                } else {
                    $bank = Banks::where('is_default', 1)->first();
                    $bankTransactionCash = BankTransaction::where('p_type', 'expense_payment_bank')->where('deposit_to', $expense->id)->delete();
                    $finalBankAmount = $bank->total_amount + $expense->total_amount;
                    $bank->total_amount = $finalBankAmount;
                    $bank->save();
                }
                $transaction->delete();
                $expense->delete();
                return response()->json(['success' => 'true', 'data' => [], 'message' => 'Expanses Delete successfully'], 200);
            } else {
                return response()->json(['success' => 'true', 'data' => [], 'message' => 'Expanses Not Found'], 200);
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

    public function duePaymentList(Request $request)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $PurchaseInvoice = PurchaseInvoice::where('party_id', $request->party_id)->get();
            // if (count($PurchaseInvoice) > 0) {
                $saleMinnusTransactions = Transaction::where('party_id', $request->party_id)
                ->where('transaction_type', 'sale')
                ->where('pending_amount', '<', 0)
                ->sum('pending_amount');
                $totalPendingAmount = round(abs($saleMinnusTransactions), 2);
                $PurchaseReturnInvoice = PurchaseReturnInvoice::where('party_id', $request->party_id)->sum('total_payable_amount');
                $duePaymentCash = DuePayment::where('party_id', $request->party_id)->sum('cash_amount');
                $duePaymentBank = DuePayment::where('party_id', $request->party_id)->sum('bank_amount');
                $duePayment = DuePayment::where('party_id', $request->party_id)->get();
                $paidAmount = $duePaymentCash + $duePaymentBank - $PurchaseReturnInvoice;
                $totalAmount = $PurchaseInvoice->sum('total_payable_amount') + $totalPendingAmount;
                $remainingAmount = $totalAmount - $paidAmount;
                $payment = [
                    'remaining_amount' => $remainingAmount,
                    'paid_amount' => $duePayment->sum('paid_amount') + $PurchaseInvoice->sum('payment_amount') - $PurchaseReturnInvoice,
                    'total_amount' => $PurchaseInvoice->sum('total_payable_amount') + $totalPendingAmount,
                    'lable' => 'Purchase Amount is ' . $PurchaseInvoice->sum('total_payable_amount') . ' and Sale Minus Transaction Amount is ' . $saleMinnusTransactions,
                ];
            // } else {
            //     $payment = (object)[];
            // }
            return response()->json(['success' => 'true', 'data' => $payment, 'message' => 'Payment Add successfully'], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
        }
    }

    public function duePaymentStore(Request $request)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $duePayment = new DuePayment();
            $prefix = 'PO-';
            $lastOrder = DuePayment::where('bill_id', 'LIKE', "{$prefix}%")
                ->orderBy('id', 'desc')
                ->first();
            if ($lastOrder) {
                $lastNumber = (int) str_replace($prefix, '', $lastOrder->bill_id);
                $orderNumber = $lastNumber + 1;
            } else {
                $orderNumber = 1;
            }
            // Generate the bill_id
            $duePayment->bill_id = $prefix . $orderNumber;
            $duePayment->date = $request->date;
            $duePayment->party_id = $request->party_id;
            $duePayment->total_amount = $request->total_amount;
            $duePayment->remaining_amount = $request->remaining_amount;
            $duePayment->payment_type = $request->payment_type;
            if ($request->payment_type == 'cash') {
                $duePayment->cash_amount = $request->cash_amount;
                $amount = $request->cash_amount;
            } elseif ($request->payment_type == 'bank') {
                $duePayment->bank_amount = $request->bank_amount;
                $duePayment->bank_id = $request->bank_id;
                $amount = $request->bank_amount;
            } elseif ($request->payment_type == 'manual') {
                $duePayment->cash_amount = $request->cash_amount;
                $duePayment->bank_amount = $request->bank_amount;
                $duePayment->bank_id = $request->bank_id;
                $amount = $request->cash_amount + $request->bank_amount;
            }
            $duePayment->note = $request->note;
            $duePayment->save();

            $transaction = new Transaction();
            $transaction->type = 'due payment';
            $transaction->date = $request->date;
            $transaction->party_id = $request->party_id;
            $transaction->total_amount = $amount;
            $transaction->pending_amount = 0;
            $transaction->bill_id = $duePayment->id;
            $transaction->is_bill = 1;
            $transaction->transaction_type = 'duePayment';
            $transaction->transaction_no = $duePayment->bill_id;
            $transaction->save();

            if ($request->payment_type == 'cash') {
                $bankTransactionCash = new BankTransaction();
                $bankTransactionCash->withdraw_from = "Cash";
                $bankTransactionCash->p_type = 'due_payment_cash';
                $bankTransactionCash->deposit_to = $duePayment->id;
                $bankTransactionCash->balance = $request->cash_amount;
                $bankTransactionCash->date = $request->date ?? now();
                $bankTransactionCash->save();
            } elseif ($request->payment_type == 'bank') {
                $bank = Banks::where('id', $request->bank_id)->first();
                $bankTransactionCash = new BankTransaction();
                $bankTransactionCash->withdraw_from = $bank->id;
                $bankTransactionCash->p_type = 'due_payment_bank';
                $bankTransactionCash->deposit_to = $duePayment->id;
                $bankTransactionCash->balance = $request->bank_amount;
                $bankTransactionCash->date = $request->date ?? now();
                $bankTransactionCash->save();
                $bank->total_amount = $bank->total_amount - $request->bank_amount;
                $bank->save();
            } elseif ($request->payment_type == 'manual') {
                $bankTransactionCash = new BankTransaction();
                $bankTransactionCash->withdraw_from = "Cash";
                $bankTransactionCash->p_type = 'due_payment_cash';
                $bankTransactionCash->deposit_to = $duePayment->id;
                $bankTransactionCash->balance = $request->cash_amount;
                $bankTransactionCash->date = $request->date ?? now();
                $bankTransactionCash->save();

                $bank = Banks::where('id', $request->bank_id)->first();
                $bankTransactionCash1 = new BankTransaction();
                $bankTransactionCash1->withdraw_from = $bank->id;
                $bankTransactionCash1->p_type = 'due_payment_bank';
                $bankTransactionCash1->deposit_to = $duePayment->id;
                $bankTransactionCash1->balance = $request->bank_amount;
                $bankTransactionCash1->date = $request->date ?? now();
                $bankTransactionCash1->save();
                $bank->total_amount = $bank->total_amount - $request->bank_amount;
                $bank->save();
            }
            return response()->json(['success' => 'true', 'data' => $duePayment, 'message' => 'Due Payment Add successfully'], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
        }
    }

    public function duePaymentView(Request $request, $id)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $transaction = Transaction::where('id', $id)->where('transaction_type', 'duePayment')->first();
            if ($transaction) {
                $duePayment = DuePayment::find($transaction->bill_id);
                return response()->json(['success' => 'true', 'data' => $duePayment, 'message' => 'Due Payment View successfully'], 200);
            } else {
                return response()->json(['success' => 'false', 'message' => 'Transaction Not Found'], 200);
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

    public function duePaymentDelete(Request $request, $id)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $transaction = Transaction::where('id', $id)->where('transaction_type', 'duePayment')->first();
            if ($transaction) {
                $duePayment = DuePayment::find($transaction->bill_id);
                if ($duePayment->payment_type == 'cash') {
                    $paymentTransaction = BankTransaction::where('p_type', 'due_payment_cash')->where('deposit_to', $transaction->bill_id)->delete();
                } else if ($duePayment->payment_type == 'bank') {
                    $paymentTransaction = BankTransaction::where('p_type', 'due_payment_bank')->where('deposit_to', $transaction->bill_id)->delete();
                    $bank = Banks::where('id', $duePayment->bank_id)->first();
                    $bank->total_amount = $bank->total_amount + $duePayment->bank_amount;
                    $bank->save();
                } else if ($duePayment->payment_type == 'manual') {
                    $paymentTransaction = BankTransaction::where('p_type', 'due_payment_cash')->where('deposit_to', $transaction->bill_id)->delete();
                    $paymentTransaction1 = BankTransaction::where('p_type', 'due_payment_bank')->where('deposit_to', $transaction->bill_id)->delete();
                    $bank = Banks::where('id', $duePayment->bank_id)->first();
                    $bank->total_amount = $bank->total_amount + $duePayment->bank_amount;
                    $bank->save();
                }
                $duePayment->delete();
                $transaction->delete();
                return response()->json(['success' => 'true', 'data' => $duePayment, 'message' => 'Due Payment Delete successfully'], 200);
            } else {
                return response()->json(['success' => 'false', 'message' => 'Transaction Not Found'], 200);
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

    public function duePaymentUpdate(Request $request, $id)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $transaction = Transaction::where('id', $id)->where('transaction_type', 'duePayment')->first();
            if ($transaction) {
                $duePayment = DuePayment::find($transaction->bill_id);
                $duePayment->date = $request->date;
                $duePayment->party_id = $request->party_id;
                $duePayment->total_amount = $request->total_amount;
                $duePayment->remaining_amount = $request->remaining_amount;
                $duePayment->payment_type = $request->payment_type;
                if ($request->payment_type == 'cash') {
                    $duePayment->cash_amount = $request->cash_amount;
                    $amount = $request->cash_amount;
                } elseif ($request->payment_type == 'bank') {
                    $duePayment->bank_amount = $request->bank_amount;
                    $duePayment->bank_id = $request->bank_id;
                    $amount = $request->bank_amount;
                } elseif ($request->payment_type == 'manual') {
                    $duePayment->cash_amount = $request->cash_amount;
                    $duePayment->bank_amount = $request->bank_amount;
                    $duePayment->bank_id = $request->bank_id;
                    $amount = $request->cash_amount + $request->bank_amount;
                }
                $duePayment->note = $request->note;
                $duePayment->save();
    
                // Transaction Update
                // $transaction = new Transaction();
                $transaction->type = 'due payment';
                $transaction->date = $request->date;
                $transaction->party_id = $request->party_id;
                $transaction->total_amount = $amount;
                $transaction->pending_amount = 0;
                $transaction->bill_id = $duePayment->id;
                $transaction->is_bill = 1;
                $transaction->transaction_type = 'duePayment';
                $transaction->save();
    
                // Bank And Cash Transaction
                if ($request->payment_type == 'cash') {
                    $paymentTransaction = BankTransaction::where('p_type', 'due_payment_cash')->where('deposit_to', $duePayment->id)->first();
                    if ($paymentTransaction) {
                        $bankTransactionCash = BankTransaction::find($paymentTransaction->id);
                    } else {
                        $bankTransactionCash = new BankTransaction();
                    }
                    $bankTransactionCash->withdraw_from = "Cash";
                    $bankTransactionCash->p_type = 'due_payment_cash';
                    $bankTransactionCash->deposit_to = $duePayment->id;
                    $bankTransactionCash->balance = $request->cash_amount;
                    $bankTransactionCash->date = $request->date ?? now();
                    $bankTransactionCash->save();
                } elseif ($request->payment_type == 'bank') {
                    $bank = Banks::where('id', $request->bank_id)->first();
                    $paymentTransaction = BankTransaction::where('p_type', 'due_payment_bank')->where('deposit_to', $duePayment->id)->first();
                    if ($paymentTransaction) {
                        $bankTransactionCash = BankTransaction::find($paymentTransaction->id);
                        $oldAmount = $bankTransactionCash->balance;
                    } else {
                        $bankTransactionCash = new BankTransaction();
                        $oldAmount = 0;
                    }
                    $bankTransactionCash->withdraw_from = $bank->id;
                    $bankTransactionCash->p_type = 'due_payment_bank';
                    $bankTransactionCash->deposit_to = $duePayment->id;
                    $bankTransactionCash->balance = $request->bank_amount;
                    $bankTransactionCash->date = $request->date ?? now();
                    $bankTransactionCash->save();
                    $bank->total_amount = ($bank->total_amount + $oldAmount) - $request->bank_amount;
                    $bank->save();
                } elseif ($request->payment_type == 'manual') {
                    $paymentCashTransaction = BankTransaction::where('p_type', 'due_payment_cash')->where('deposit_to', $duePayment->id)->first();
                    if ($paymentCashTransaction) {
                        $bankTransactionCash = BankTransaction::find($paymentCashTransaction->id);
                    } else {
                        $bankTransactionCash = new BankTransaction();
                    }
                    $bankTransactionCash->withdraw_from = "Cash";
                    $bankTransactionCash->p_type = 'due_payment_cash';
                    $bankTransactionCash->deposit_to = $duePayment->id;
                    $bankTransactionCash->balance = $request->cash_amount;
                    $bankTransactionCash->date = $request->date ?? now();
                    $bankTransactionCash->save();
    
                    $bank = Banks::where('id', $request->bank_id)->first();
                    $paymentBankTransaction = BankTransaction::where('p_type', 'due_payment_bank')->where('deposit_to', $duePayment->id)->first();
                    if ($paymentBankTransaction) {
                        $bankTransactionBank = BankTransaction::find($paymentBankTransaction->id);
                        $oldAmount = $paymentBankTransaction->balance;
                    } else {
                        $bankTransactionBank = new BankTransaction();
                        $oldAmount = 0;
                    }
                    $bankTransactionBank->withdraw_from = $bank->id;
                    $bankTransactionBank->p_type = 'due_payment_bank';
                    $bankTransactionBank->deposit_to = $duePayment->id;
                    $bankTransactionBank->balance = $request->bank_amount;
                    $bankTransactionBank->date = $request->date ?? now();
                    $bankTransactionBank->save();
                    $bank->total_amount = ($bank->total_amount + $oldAmount) - $request->bank_amount;
                    $bank->save();
                }
                return response()->json(['success' => 'true', 'data' => $duePayment, 'message' => 'Due Payment Updated successfully'], 200);
            } else {
                return response()->json(['success' => 'true', 'message' => 'Transaction Not Found'], 200);
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
