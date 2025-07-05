<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CollectionType;
use App\Models\Expense;
use App\Models\Order;
use App\Models\Product;
use App\Models\PurchaseInvoice;
use App\Models\BankTransaction;
use App\Models\PaymentInType;
use App\Models\Banks;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
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

    public function cashTransactionList(Request $request)
    {
        $cashTransaction = BankTransaction::whereIn('p_type', [
            'cash_to_bank', 'bank_to_cash', 'sale_cash',
            'payment_in_cash', 'purchase_cash', 'due_payment_cash', 'expense_payment_cash'
        ])->get();

        $total = 0;

        $cashTransaction->each(function ($transaction) use (&$total) {
            $transaction->bank = (object)[];
            
            if ($transaction->p_type == 'cash_to_bank') {
                $bank = Banks::find($transaction->deposit_to);
                $transaction->title = 'Bank Deposit (' . ($bank->bank_name ?? '-') . ')';
                $transaction->bank = $bank;
                $transaction->credit_debit = 'Debit';
            } elseif ($transaction->p_type == 'bank_to_cash') {
                $bank = Banks::find($transaction->withdraw_from);
                $transaction->title = 'Bank Withdrawal (' . ($bank->bank_name ?? '-') . ')';
                $transaction->bank = $bank;
                $transaction->credit_debit = 'Credit';
            } elseif ($transaction->p_type == 'sale_cash') {
                $transaction->title = 'Sale';
                $transaction->credit_debit = 'Credit';
            } elseif ($transaction->p_type == 'payment_in_cash') {
                $transaction->title = 'Payment In';
                $transaction->credit_debit = 'Credit';
            } elseif ($transaction->p_type == 'purchase_cash') {
                $transaction->title = 'Purchase';
                $transaction->credit_debit = 'Debit';
            } elseif ($transaction->p_type == 'due_payment_cash') {
                $transaction->title = 'Due Payment';
                $transaction->credit_debit = 'Debit';
            } elseif ($transaction->p_type == 'expense_payment_cash') {
                $transaction->title = 'Expense';
                $transaction->credit_debit = 'Debit';
            }

            // Calculate total balance
            if ($transaction->credit_debit === 'Credit') {
                $total += $transaction->balance;
            } elseif ($transaction->credit_debit === 'Debit') {
                $total -= $transaction->balance;
            }
        });

        // Optional: If you also want to return the full list with balance
        // return response()->json(['data' => $cashTransaction, 'cash_balance' => $total]);

        return $total;
    }


    
    public function index(Request $request)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $purchases = PurchaseInvoice::orderByDesc('id')->count();
            $lowStock = Product::where('status', 1)
            ->whereRaw('CAST(available_stock AS UNSIGNED) <= CAST(low_stock AS UNSIGNED)')
            ->whereRaw('CAST(available_stock AS UNSIGNED) > 0')->count();
            $outofStock = Product::where('status', 1)->whereRaw('CAST(available_stock AS UNSIGNED) <= 0')->count();
            $supplierOrder = Order::with('supplier')->where('order_type', 'retailer')->orderByDesc('id')->count();
            $wholesalerNewOrder = Order::where('order_type', 'wholesaler')->orderByDesc('id')->where('status',0)->count();
            $wholesalerOrder = Order::where('order_type', 'wholesaler')->orderByDesc('id')->count();
            // $gpayAmount = CollectionType::where('name', 'G-Pay')->sum('amount');
            $cashAmountTotal = $this->cashTransactionList($request);
            $gpayAmount = Banks::sum('total_amount');
            $expnaseAmount = Expense::sum('total_amount');
            $list = [
                'purchases' => $purchases,
                'lowStock' => $lowStock,
                'outofStock' => $outofStock,
                'supplierOrder' => $supplierOrder,
                'wholesalerNewOrder' => $wholesalerNewOrder,
                'wholesalerOrder' => $wholesalerOrder,
                'cashAmount' => $cashAmountTotal,
                'gpayAmount' => $gpayAmount,
                'expnaseAmount' => $expnaseAmount
            ];
            return response()->json(['success' => 'true', 'data' => $list, 'message' => 'Dashboard List Fetch successfully'], 200);
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
