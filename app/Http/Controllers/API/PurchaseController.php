<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseProduct;
use App\Models\PurchaseReturnInvoice;
use App\Models\PurchaseReturnProduct;
use App\Models\Transaction;
use App\Models\BankTransaction;
use App\Models\Banks;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
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
            $purchase = PurchaseInvoice::orderByDesc('id')->get();
            return response()->json(['success' => 'true', 'data' => $purchase, 'message' => 'Purchase Invoices fetch successfully'], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
        }
    }

    public function store(Request $request)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $purchase = new PurchaseInvoice();
            $purchase->date = $request->date;
            $purchase->party_id = $request->party_id;
            $purchase->total_purchase_amount = $request->total_purchase_amount;
            $purchase->total_gst_amount = $request->total_gst_amount;
            $purchase->total_payable_amount = $request->total_payable_amount;
            $purchase->adjustment = $request->adjustment ?? 0;
            $purchase->ref_no = $request->ref_no ?? null;
            if ($request->payment_type) {
                if ($request->payment_type == 'bank') {
                    $purchase->payment_type = $request->payment_type;
                    $purchase->payment_amount = $request->payment_amount;
                    $purchase->bank_id = $request->bank_id;
                } else {
                    $purchase->payment_type = $request->payment_type;
                    $purchase->payment_amount = $request->payment_amount;
                }
            }
            $purchase->remark = $request->remark;
            $purchase->gst = $request->gst;
            $purchase->total_box = $request->total_box;
            $purchase->total_patti = $request->total_patti;
            $purchase->total_packet = $request->total_packet;
            $purchase->save();
        
            foreach ($request->products as $product) {
                $purchaseProduct = new PurchaseProduct();
                $purchaseProduct->purchase_invoice_id = $purchase->id;
                $purchaseProduct->product_id = $product['product_id'];
                $findProduct = Product::where('id',$product['product_id'])->first();
                $purchaseProduct->purchase_price = $product['purchase_price'];
                $purchaseProduct->box = $product['box'];
                $boxQty = $product['box'] * $findProduct->packet;
                $purchaseProduct->patti = $product['patti'];
                $pattiQty = $product['patti'] * $findProduct->per_patti_piece;
                $purchaseProduct->packet = $product['packet'];
                $TotalQty = $boxQty + $pattiQty + $product['packet'];
                $purchaseProduct->qty = $TotalQty;
                $findProduct->available_stock += $TotalQty;
                $findProduct->save();
                $purchaseProduct->total_amount = $product['total_amount'];
                $purchaseProduct->party_id = $request->party_id;
                $purchaseProduct->save();
            }
            if ($request->payment_amount > 0) {
                $transaction = new Transaction();
                $pending_amount = $request->total_payable_amount - $request->payment_amount;
                if ($pending_amount == 0) {
                    $transaction->type = 'purchase paid';
                } else {
                    $transaction->type = 'purchase partial';
                }
                $transaction->date = $request->date;
                $transaction->party_id = $request->party_id;
                $transaction->total_amount = $request->total_payable_amount;
                $transaction->pending_amount = $pending_amount;
                $transaction->bill_id = $purchase->id;
                $transaction->is_bill = 1;
                $transaction->transaction_type = 'purchase';
                $transaction->save();
            } else {
                $transaction = new Transaction();
                $transaction->type = 'purchase unpaid';
                $transaction->date = $request->date;
                $transaction->party_id = $request->party_id;
                $transaction->total_amount = $request->total_payable_amount;
                $transaction->pending_amount = $request->total_payable_amount;
                $transaction->bill_id = $purchase->id;
                $transaction->is_bill = 1;
                $transaction->transaction_type = 'purchase';
                $transaction->save();
            }

            if ($request->payment_amount > 0) {
                if ($request->payment_type == 'bank') {
                    $bank = Banks::where('id', $request->bank_id)->first();
                    $bankTransaction = new BankTransaction();
                    $bankTransaction->withdraw_from = $bank->id;
                    $bankTransaction->p_type = 'purchase_bill';
                    $bankTransaction->deposit_to = $purchase->id;
                    $bankTransaction->balance = $request->payment_amount;
                    $bankTransaction->date = $request->date;
                    $bankTransaction->save();
                    $bank->total_amount -= $request->payment_amount;
                    $bank->save();
                } else {
                    $bankTransaction = new BankTransaction();
                    $bankTransaction->withdraw_from = "Cash";
                    $bankTransaction->p_type = 'purchase_cash';
                    $bankTransaction->deposit_to = $purchase->id;
                    $bankTransaction->balance = $request->payment_amount;
                    $bankTransaction->date = $request->date;
                    $bankTransaction->save();
                }
            }
            
            return response()->json(['success' => 'true', 'data' => $purchase, 'message' => 'Purchase created successfully'], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
        }
    }

    public function view(Request $request, $id)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $purchase = PurchaseInvoice::with('purchaseDetails')->where('id',$id)->first();
            foreach ($purchase->purchaseDetails as $key => $purchaseDetail) {
                $purchaseDetail->product;
            }
            if ($purchase) {
                return response()->json(['success' => 'true', 'data' => $purchase, 'message' => 'Purchase Invoice fetch successfully'], 200);
            } else {
                return response()->json(['success' => 'true', 'data' => [], 'message' => 'Purchase Invoice not found'], 200);
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

    public function update(Request $request,$id)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $purchase = PurchaseInvoice::where('id', $id)->first();
            $purchase->date = $request->date;
            $purchase->total_purchase_amount = $request->total_purchase_amount;
            $purchase->total_gst_amount = $request->total_gst_amount;
            $purchase->total_payable_amount = $request->total_payable_amount;
            $purchase->gst = $request->gst;
            $purchase->total_box = $request->total_box;
            $purchase->total_patti = $request->total_patti;
            $purchase->total_packet = $request->total_packet;
            $purchase->adjustment = $request->adjustment ?? 0;
            $purchase->ref_no = $request->ref_no ?? null;
              if ($request->payment_type) {
                if ($request->payment_type == 'bank') {
                    $purchase->payment_type = $request->payment_type;
                    $purchase->payment_amount = $request->payment_amount;
                    $purchase->bank_id = $request->bank_id;
                } else {
                    $purchase->payment_type = $request->payment_type;
                    $purchase->payment_amount = $request->payment_amount;
                }
            }
            $purchase->save();
        
            foreach ($request->products as $product) {
                if (isset($product['purchase_product_id'])) {
                    $purchaseProduct = PurchaseProduct::where('id', $product['purchase_product_id'])->first();
                    if ($purchaseProduct) {
                        $purchaseProduct->purchase_invoice_id = $purchase->id;
                        $purchaseProduct->product_id = $product['product_id'];
                        $findProduct = Product::where('id',$product['product_id'])->first();
                        $purchaseProduct->purchase_price = $product['purchase_price'];
                        $purchaseProduct->box = $product['box'];
                        $boxQty = $product['box'] * $findProduct->packet;
                        $purchaseProduct->patti = $product['patti'];
                        $pattiQty = $product['patti'] * $findProduct->per_patti_piece;
                        $purchaseProduct->packet = $product['packet'];
                        $TotalQty = $boxQty + $pattiQty + $product['packet'];
                        $oldQty = $purchaseProduct->qty;
                        $purchaseProduct->qty = $TotalQty;
                        $findProduct->available_stock += $TotalQty - $oldQty;
                        $findProduct->save();
                        $purchaseProduct->total_amount = $product['total_amount'];
                        $purchaseProduct->save();
                    }
                } else {
                    $purchaseProduct = new PurchaseProduct();
                    $purchaseProduct->purchase_invoice_id = $purchase->id;
                    $purchaseProduct->product_id = $product['product_id'];
                    $findProduct = Product::where('id',$product['product_id'])->first();
                    $purchaseProduct->purchase_price = $product['purchase_price'];
                    $purchaseProduct->box = $product['box'];
                    $boxQty = $product['box'] * $findProduct->packet;
                    $purchaseProduct->patti = $product['patti'];
                    $pattiQty = $product['patti'] * $findProduct->per_patti_piece;
                    $purchaseProduct->packet = $product['packet'];
                    $TotalQty = $boxQty + $pattiQty + $product['packet'];
                    $purchaseProduct->qty = $TotalQty;
                    $findProduct->available_stock += $TotalQty;
                    $findProduct->save();
                    $purchaseProduct->total_amount = $product['total_amount'];
                    $purchaseProduct->party_id = $request->party_id;
                    $purchaseProduct->save();
                }
            }


            $transaction = Transaction::where('bill_id', $purchase->id)->where('transaction_type', 'purchase')->first();
            if ($transaction) {
                $pending_amount = $request->total_payable_amount - $request->payment_amount;
                if ($pending_amount == 0) {
                    $transaction->type = 'purchase paid';
                } else {
                    $transaction->type = 'purchase partial';
                }
                $transaction->date = $request->date;
                $transaction->party_id = $request->party_id;
                $transaction->total_amount = $request->total_payable_amount;
                $transaction->pending_amount = $pending_amount;
                $transaction->bill_id = $purchase->id;
                $transaction->is_bill = 1;
                $transaction->transaction_type = 'purchase';
                $transaction->save();
            } else {
                $transaction1 = new Transaction();
                $transaction1->type = 'purchase unpaid';
                $transaction1->date = $request->date;
                $transaction1->party_id = $request->party_id;
                $transaction1->total_amount = $request->total_payable_amount;
                $transaction1->pending_amount = $request->total_payable_amount;
                $transaction1->bill_id = $purchase->id;
                $transaction1->is_bill = 1;
                $transaction1->transaction_type = 'purchase';
                $transaction1->save();
            }

            if ($request->payment_amount > 0) {
                if ($request->payment_type == 'bank') {
                    $bankTransaction = BankTransaction::where('withdraw_from', $purchase->id)
                    ->where('p_type', 'purchase_bill')
                    ->first();
                    $bank = Banks::where('id', $request->bank_id)->first();
                    if ($bankTransaction) {
                        $bankTransaction = BankTransaction::find($bankTransaction->id);
                        $oldPending = $bankTransaction->balance;
                    } else {
                        $bankTransaction = new BankTransaction();
                        $oldPending = 0;
                    }
                    $bank->total_amount += $request->payment_amount;
                    $bankTransaction->withdraw_from = $bank->id;
                    $bankTransaction->p_type = 'purchase_bill';
                    $bankTransaction->deposit_to = $purchase->id;
                    $bankTransaction->balance = $request->payment_amount;
                    $bankTransaction->date = $request->date;
                    $bankTransaction->save();
                    $finalBankAmount = $bank->total_amount + $oldPending;
                    $bank->total_amount = $finalBankAmount - $request->payment_amount;
                    $bank->save();
                } else {
                    $bankTransaction = BankTransaction::where('withdraw_from', $purchase->id)
                    ->where('p_type', 'purchase_bill')
                    ->first();
                    if ($bankTransaction) {
                        $bankTransaction = BankTransaction::find($bankTransaction->id);
                    } else {
                        $bankTransaction = new BankTransaction();
                    }
                    // $bankTransaction = new BankTransaction();
                    $bankTransaction->withdraw_from = "Cash";
                    $bankTransaction->p_type = 'purchase_cash';
                    $bankTransaction->deposit_to = $purchase->id;
                    $bankTransaction->balance = $request->payment_amount;
                    $bankTransaction->date = $request->date;
                    $bankTransaction->save();
                }
            }

            return response()->json(['success' => 'true', 'data' => $purchase, 'message' => 'Purchase created successfully'], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
        }
    }


    public function purchaseProductList(Request $request)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $purchaseProduct = PurchaseProduct::where('party_id', $request->party_id)->select(
                    'product_id',
                    DB::raw('SUM(box) as total_box'),
                    DB::raw('SUM(patti) as total_patti'),
                    DB::raw('SUM(packet) as total_packet'),
                    DB::raw('SUM(qty) as total_qty'),
                    DB::raw('SUM(total_amount) as total_amount')
                )
                ->with('product')
                ->groupBy('product_id')
                ->orderByDesc('product_id')
                ->get();
    
            return response()->json([
                'success' => 'true',
                'data' => $purchaseProduct,
                'message' => 'Purchase Products fetched successfully'
            ], 200);
        } else {
            return response()->json([
                'success' => 'false',
                'data' => [['code' => 'auth-001', 'message' => 'Unauthorized.']]
            ], 200);
        }
    }

    public function returnStore(Request $request)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $purchase = new PurchaseReturnInvoice();
            $purchase->date = $request->date;
            $purchase->party_id = $request->party_id;
            $purchase->total_purchase_amount = $request->total_purchase_amount;
            $purchase->total_gst_amount = $request->total_gst_amount;
            $purchase->total_payable_amount = $request->total_payable_amount;
            $purchase->gst = $request->gst;
            $purchase->total_box = $request->total_box;
            $purchase->total_patti = $request->total_patti;
            $purchase->total_packet = $request->total_packet;
            // $purchase->payment_type = $request->payment_type;
            if ($request->payment_type) {
                if ($request->payment_type == 'bank') {
                    $purchase->payment_type = $request->payment_type;
                    $purchase->payment_amount = $request->payment_amount;
                    $purchase->bank_id = $request->bank_id;
                } else {
                    $purchase->payment_type = $request->payment_type;
                    $purchase->payment_amount = $request->payment_amount;
                }
            }
            $purchase->save();
        
            foreach ($request->products as $product) {
                $purchaseProduct = new PurchaseReturnProduct();
                $purchaseProduct->purchase_return_invoice_id = $purchase->id;
                $purchaseProduct->product_id = $product['product_id'];
                $purchaseProduct->party_id = $request->party_id;
                $findProduct = Product::where('id',$product['product_id'])->first();
                $purchaseProduct->purchase_price = $product['purchase_price'];
                $purchaseProduct->box = $product['box'];
                $boxQty = $product['box'] * $findProduct->packet;
                $purchaseProduct->patti = $product['patti'];
                $pattiQty = $product['patti'] * $findProduct->per_patti_piece;
                $purchaseProduct->packet = $product['packet'];
                $TotalQty = $boxQty + $pattiQty + $product['packet'];
                $purchaseProduct->qty = $TotalQty;
                $findProduct->available_stock -= $TotalQty;
                $findProduct->save();
                $purchaseProduct->total_amount = $product['total_amount'];
                $purchaseProduct->save();
            }


            if ($request->total_payable_amount > 0) {
                $transaction = new Transaction();
                $pending_amount = $request->total_payable_amount - $request->total_payable_amount;
                $transaction->type = 'purchase return';
                $transaction->date = $request->date;
                $transaction->party_id = $request->party_id;
                $transaction->total_amount = $request->total_payable_amount;
                $transaction->pending_amount = $request->total_payable_amount;
                $transaction->bill_id = $purchase->id;
                $transaction->is_bill = 1;
                $transaction->transaction_type = 'purchase return';
                $transaction->save();
            }

            if ($request->payment_amount > 0) {
                if ($request->payment_type == 'bank') {
                    $bank = Banks::where('id', $request->bank_id)->first();
                    $bankTransaction = new BankTransaction();
                    $bankTransaction->withdraw_from = $bank->id;
                    $bankTransaction->p_type = 'purchase_return_bank';
                    $bankTransaction->deposit_to = $purchase->id;
                    $bankTransaction->balance = $request->payment_amount;
                    $bankTransaction->date = $request->date;
                    $bankTransaction->save();
                    $bank->total_amount += $request->payment_amount;
                    $bank->save();
                } else {
                    $bankTransaction = new BankTransaction();
                    $bankTransaction->withdraw_from = "Cash";
                    $bankTransaction->p_type = 'purchase_return_cash';
                    $bankTransaction->deposit_to = $purchase->id;
                    $bankTransaction->balance = $request->payment_amount;
                    $bankTransaction->date = $request->date;
                    $bankTransaction->save();
                }
            }

            return response()->json(['success' => 'true', 'data' => $purchase, 'message' => 'Purchase Return created successfully'], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
        }
    }

    public function returnList(Request $request)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $purchase = PurchaseReturnInvoice::orderByDesc('id')->get();
            return response()->json(['success' => 'true', 'data' => $purchase, 'message' => 'Purchase Invoices fetch successfully'], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
        }
    }
    public function returnView(Request $request, $id)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
             $purchaseReturnInvoice = PurchaseReturnInvoice::with('party')->where('id',$id)->first();
            if ($purchaseReturnInvoice) {
                $purchaseProduct = PurchaseProduct::where('party_id', $purchaseReturnInvoice->party_id)->select(
                    'product_id',
                    DB::raw('SUM(box) as total_box'),
                    DB::raw('SUM(patti) as total_patti'),
                    DB::raw('SUM(packet) as total_packet'),
                    DB::raw('SUM(qty) as total_qty'),
                    DB::raw('SUM(total_amount) as total_amount')
                )
                ->with('product')
                ->groupBy('product_id')
                ->orderByDesc('product_id')
                ->get();
    
                foreach ($purchaseProduct as $key => $product) {
                    $returnProduct = PurchaseReturnProduct::where('purchase_return_invoice_id', $id)->where('product_id', $product->product_id)->first();
                    if ($returnProduct) {
                        $product->added_qty = [
                            'box' => $returnProduct->box,
                            'patti' => $returnProduct->patti,
                            'packet' => $returnProduct->packet
                        ];
                    } else {
                        $product->added_qty = [
                            'box' => 0,
                            'patti' => 0,
                            'packet' => 0
                        ];
                    }
                }
                $purchaseReturnInvoice->return_products = $purchaseProduct;
                return response()->json(['success' => 'true', 'data' => $purchaseReturnInvoice, 'message' => 'Purchase Invoice fetch successfully'], 200);
            } else {
                return response()->json(['success' => 'true', 'data' => [], 'message' => 'Purchase Invoice not found'], 200);
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

    public function returnUpdate(Request $request,$id)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $purchase = PurchaseReturnInvoice::where('id', $id)->first();
            $purchase->date = $request->date;
            $purchase->total_purchase_amount = $request->total_purchase_amount;
            $purchase->total_gst_amount = $request->total_gst_amount;
            $purchase->total_payable_amount = $request->total_payable_amount;
            $purchase->gst = $request->gst;
            $purchase->total_box = $request->total_box;
            $purchase->total_patti = $request->total_patti;
            $purchase->total_packet = $request->total_packet;
            if ($request->payment_type) {
                if ($request->payment_type == 'bank') {
                    $purchase->payment_type = $request->payment_type;
                    $purchase->payment_amount = $request->payment_amount;
                    $purchase->bank_id = $request->bank_id;
                } else {
                    $purchase->payment_type = $request->payment_type;
                    $purchase->payment_amount = $request->payment_amount;
                }
            }
            $purchase->save();
        
            foreach ($request->products as $product) {
                $purchaseProduct = PurchaseReturnProduct::where('purchase_return_invoice_id', $id)->where('product_id', $product['product_id'])->first();
                if ($purchaseProduct) {
                    $purchaseProduct->purchase_return_invoice_id = $purchase->id;
                    $purchaseProduct->product_id = $product['product_id'];
                    $findProduct = Product::where('id',$product['product_id'])->first();
                    $purchaseProduct->purchase_price = $product['purchase_price'];
                    $purchaseProduct->box = $product['box'];
                    $boxQty = $product['box'] * $findProduct->packet;
                    $purchaseProduct->patti = $product['patti'];
                    $pattiQty = $product['patti'] * $findProduct->per_patti_piece;
                    $purchaseProduct->packet = $product['packet'];
                    $TotalQty = $boxQty + $pattiQty + $product['packet']; 
                    $oldQty = $purchaseProduct->qty;
                    $purchaseProduct->qty = $TotalQty;
                    $purchaseProduct->total_amount = $product['total_amount'];
                    $purchaseProduct->save();
                    $findProduct->available_stock += $oldQty;
                    $findProduct->available_stock -= $TotalQty;
                    $findProduct->save();
                } else {
                    $purchaseProduct = new PurchaseReturnProduct();
                    $purchaseProduct->purchase_return_invoice_id = $purchase->id;
                    $purchaseProduct->product_id = $product['product_id'];
                    $findProduct = Product::where('id',$product['product_id'])->first();
                    $purchaseProduct->purchase_price = $product['purchase_price'];
                    $purchaseProduct->box = $product['box'];
                    $boxQty = $product['box'] * $findProduct->packet;
                    $purchaseProduct->patti = $product['patti'];
                    $pattiQty = $product['patti'] * $findProduct->per_patti_piece;
                    $purchaseProduct->packet = $product['packet'];
                    $TotalQty = $boxQty + $pattiQty + $product['packet']; 
                    $purchaseProduct->qty = $TotalQty;
                    $purchaseProduct->total_amount = $product['total_amount'];
                    $purchaseProduct->save();
                    $findProduct->available_stock -= $TotalQty;
                    $findProduct->save();
                }
            }

            if ($request->total_payable_amount > 0) {
                $transaction = Transaction::where('bill_id', $id)->where('transaction_type', 'purchase return')->first();
                $pending_amount = $request->total_payable_amount - $request->total_payable_amount;
                $transaction->type = 'purchase return';
                $transaction->date = $request->date;
                $transaction->party_id = $request->party_id;
                $transaction->total_amount = $request->total_payable_amount;
                $transaction->pending_amount = $request->total_payable_amount;
                // $transaction->bill_id = $purchase->id;
                $transaction->is_bill = 1;
                $transaction->transaction_type = 'purchase return';
                $transaction->save();
            }
            
            if ($request->payment_amount > 0) {
                if ($request->payment_type == 'bank') {
                    $bankTransaction = BankTransaction::where('deposit_to', $purchase->id)
                    ->where('p_type', 'purchase_return_bank')
                    ->first();
                    $bank = Banks::where('id', $request->bank_id)->first();
                    if ($bankTransaction) {
                        $bankTransaction = BankTransaction::find($bankTransaction->id);
                        $oldPending = $bankTransaction->balance;
                    } else {
                        $bankTransaction = new BankTransaction();
                        $oldPending = 0;
                    }
                    $bank->total_amount += $request->payment_amount;
                    $bankTransaction->withdraw_from = $bank->id;
                    $bankTransaction->p_type = 'purchase_return_bank';
                    $bankTransaction->deposit_to = $purchase->id;
                    $bankTransaction->balance = $request->payment_amount;
                    $bankTransaction->date = $request->date;
                    $bankTransaction->save();

                    $finalBankAmount = $bank->total_amount - $oldPending;
                    $bank->total_amount = $finalBankAmount + $request->payment_amount;
                    $bank->save();
                } else {
                    $bankTransaction = BankTransaction::where('deposit_to', $purchase->id)
                    ->where('p_type', 'purchase_return_cash')
                    ->first();
                    if ($bankTransaction) {
                        $bankTransaction = BankTransaction::find($bankTransaction->id);
                    } else {
                        $bankTransaction = new BankTransaction();
                    }
                    // $bankTransaction = new BankTransaction();
                    $bankTransaction->withdraw_from = "Cash";
                    $bankTransaction->p_type = 'purchase_return_cash';
                    $bankTransaction->deposit_to = $purchase->id;
                    $bankTransaction->balance = $request->payment_amount;
                    $bankTransaction->date = $request->date;
                    $bankTransaction->save();
                }
            }
            return response()->json(['success' => 'true', 'data' => $purchase, 'message' => 'Purchase Invoice updated successfully'], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
        }
    }

    public function purchasePartyList(Request $request)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $purchaseParty = PurchaseInvoice::select('party_id')->distinct()->get();
            return response()->json(['success' => 'true', 'data' => $purchaseParty, 'message' => 'Purchase Party fetched successfully'], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
        }
    }
    
    public function deletePurchaseTransaction(Request $request, $id) {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $transaction = Transaction::where('id', $id)->where('transaction_type', 'purchase')->first();
            if ($transaction) {
                $purchase = PurchaseInvoice::where('id', $transaction->bill_id)->first();
                $purchaseProducts = PurchaseProduct::where('purchase_invoice_id', $transaction->bill_id)->get();
                foreach ($purchaseProducts as $purchaseProduct) {
                    $findProduct = Product::where('id', $purchaseProduct->product_id)->first();
                    $findProduct->available_stock -= $purchaseProduct->qty;
                    $findProduct->save();
                    $purchaseProduct->delete();
                }
                if ($purchase->payment_type == 'bank') {
                    $bank = Bank::where('id', $purchase->bank_id)->first();
                    $bank->total_amount += $purchase->payment_amount;
                    $bank->save();
                    $bankTransaction = BankTransaction::where('deposit_to', $purchase->id)->where('p_type', 'purchase')->delete();
                } else {
                    $bankTransaction = BankTransaction::where('deposit_to', $purchase->id)->where('p_type', 'purchase_cash')->delete();    
                }
                $transaction->delete();
                $purchase->delete();
                return response()->json(['success' => 'true', 'data' => $purchase, 'message' => 'Purchase Invoice deleted successfully'], 200);
            } else {
                return response()->json(['success' => 'false', 'message' => 'Purchase Transaction Not Found'], 200);
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

    public function purchaseReturnDelete(Request $request, $id) {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $transaction = Transaction::where('id', $id)->where('transaction_type', 'purchase return')->first();
            if ($transaction) {
                $purchase = PurchaseReturnInvoice::where('id', $transaction->bill_id)->first();
                $purchaseProducts = PurchaseReturnProduct::where('purchase_return_invoice_id', $transaction->bill_id)->get();
                foreach ($purchaseProducts as $purchaseProduct) {
                    $findProduct = Product::where('id', $purchaseProduct->product_id)->first();
                    $findProduct->available_stock += $purchaseProduct->qty;
                    $findProduct->save();
                    $purchaseProduct->delete();
                }
                if ($purchase->payment_type == 'bank') {
                    $bank = Bank::where('id', $purchase->bank_id)->first();
                    $bank->total_amount -= $purchase->payment_amount;
                    $bank->save();
                    $bankTransaction = BankTransaction::where('deposit_to', $purchase->id)->where('p_type', 'purchase_return_bank')->delete();
                } else {
                    $bankTransaction = BankTransaction::where('deposit_to', $purchase->id)->where('p_type', 'purchase_return_cash')->delete();    
                }
                $transaction->delete();
                $purchase->delete();
                return response()->json(['success' => 'true', 'data' => $purchase, 'message' => 'Purchase Return Invoice deleted successfully'], 200);
            } else {
                return response()->json(['success' => 'false', 'message' => 'Purchase Return Transaction Not Found'], 200);
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
