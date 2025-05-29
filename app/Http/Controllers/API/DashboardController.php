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
            $cashAmount = CollectionType::where('name', 'Cash')->sum('amount');
            $cashAmountLess = BankTransaction::where('p_type', 'cash_to_bank')->sum('balance');
            $cashAmountPlus = BankTransaction::where('p_type', 'bank_to_cash')->sum('balance');
            $cashAmountExpenseMinus = BankTransaction::where('p_type', 'expense_payment_cash')->sum('balance');
            $paymentInAmountPlus = PaymentInType::where('name', 'Cash')->sum('amount');
            // $gpayAmount = CollectionType::where('name', 'G-Pay')->sum('amount');
            $gpayAmount = Banks::sum('total_amount');
            $expnaseAmount = Expense::sum('total_amount');
            $list = [
                'purchases' => $purchases,
                'lowStock' => $lowStock,
                'outofStock' => $outofStock,
                'supplierOrder' => $supplierOrder,
                'wholesalerNewOrder' => $wholesalerNewOrder,
                'wholesalerOrder' => $wholesalerOrder,
                'cashAmount' => ($cashAmount - $cashAmountLess) + $cashAmountPlus + $paymentInAmountPlus - $cashAmountExpenseMinus,
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
