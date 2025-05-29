<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentInBill;
use App\Models\PaymentInType;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\Banks;
use App\Models\BankTransaction;
use App\Models\User;
use Illuminate\Http\Request;

class PaymentInController extends Controller
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
    
    public function paymentInStore(Request $request)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $transaction = new Transaction();
            $transaction->party_id = $request->party_id;
            $transaction->bill_id = 0;
            $transaction->total_amount = $request->final_amount;
            $transaction->pending_amount = 0;
            $transaction->type = 'paymentIn';
            $transaction->transaction_type = 'payment_in';
            $transaction->date = now();
            $transaction->save();

            $party = Supplier::where('id', $request->supplier_id)->first();
            if ($party) {
                $party->amount -= $request->final_amount;
                $party->save();
            }

            foreach ($request->bills as $key => $bills) {
                $biilTransaction = Transaction::where('bill_id', $bills['bill_id'])->where('transaction_type', 'sale')->first();
                if (isset($biilTransaction)) {
                    $biilTransaction->pending_amount -= $bills['amount'];
                    if ($biilTransaction->pending_amount == 0) {
                        $biilTransaction->type = 'sale paid';
                        $mainOrderFind = Order::where('id', $bills['bill_id'])->first();
                        $mainOrderFind->bill_status = 1;
                        $mainOrderFind->save();
                    } else {
                        $biilTransaction->type = 'sale partial';
                    }
                    $biilTransaction->save();

                    $PaymentIn = new PaymentInBill();
                    $PaymentIn->transaction_id = $transaction->id;
                    $PaymentIn->bill_id = $bills['bill_id'];
                    $PaymentIn->bill_amount = $bills['bill_amount'];
                    $PaymentIn->current_amount = $bills['current_amount'];
                    $PaymentIn->amount = $bills['amount'];
                    $PaymentIn->payment_type = $bills['amount'];
                    $PaymentIn->save();
                }
            }

            if ($request->payment_type == 'Manual') {
                $paymentType = new PaymentInType();
                $paymentType->payment_in_tran_id = $transaction->id;
                $paymentType->name = 'Cash';
                $paymentType->amount = $request->payment_cash_amount;
                $paymentType->save();
                // Bank Transaction Add In Cash
                $bank = Banks::where('is_default', 1)->first();
                $bankTransactionCash = new BankTransaction();
                $bankTransactionCash->withdraw_from = $transaction->id;
                $bankTransactionCash->p_type = 'payment_in_cash';
                $bankTransactionCash->deposit_to = "Cash";
                $bankTransactionCash->balance = $request->payment_cash_amount;
                $bankTransactionCash->date = $request->date ?? now();
                $bankTransactionCash->save();
                $bank->total_amount = $bank->total_amount + $request->payment_cash_amount;
                $bank->save();

                $paymentType = new PaymentInType();
                $paymentType->payment_in_tran_id = $transaction->id;
                $paymentType->name = 'Bank';
                $paymentType->amount = $request->payment_bank_amount;
                $paymentType->save();

                // Bank Transaction Add In Bank
                $Bankbank = Banks::where('is_default', 1)->first();
                $bankTransaction = new BankTransaction();
                $bankTransaction->withdraw_from = $transaction->id;
                $bankTransaction->p_type = 'payment_in_bank';
                $bankTransaction->deposit_to = $Bankbank->id;
                $bankTransaction->balance = $request->payment_bank_amount;
                $bankTransaction->date = $request->date ?? now();
                $bankTransaction->save();
                $Bankbank->total_amount = $Bankbank->total_amount + $request->payment_bank_amount;
                $Bankbank->save();
            } else {
                $paymentType = new PaymentInType();
                $paymentType->payment_in_tran_id = $transaction->id;
                if ($request->payment_type == 'Cash') {
                    $paymentType->name = 'Cash';

                    $bank = Banks::where('is_default', 1)->first();
                    $bankTransactionCash = new BankTransaction();
                    $bankTransactionCash->withdraw_from = $transaction->id;
                    $bankTransactionCash->p_type = 'payment_in_cash';
                    $bankTransactionCash->deposit_to = "Cash";
                    $bankTransactionCash->balance = $request->payment_amount;
                    $bankTransactionCash->date = $request->date ?? now();
                    $bankTransactionCash->save();
                    $bank->total_amount = $bank->total_amount + $request->payment_amount;
                    $bank->save();
                } elseif ($request->payment_type == 'Bank') {
                    $paymentType->name = 'Bank';

                    $bank = Banks::where('is_default', 1)->first();
                    $bankTransaction = new BankTransaction();
                    $bankTransaction->withdraw_from = $transaction->id;
                    $bankTransaction->p_type = 'payment_in_bank';
                    $bankTransaction->deposit_to = $bank->id;
                    $bankTransaction->balance = $request->payment_amount;
                    $bankTransaction->date = $request->date ?? now();
                    $bankTransaction->save();
                    $bank->total_amount = $bank->total_amount + $request->payment_amount;
                    $bank->save();
                }
                $paymentType->amount = $request->payment_amount;
                $paymentType->save();
            }
            


            return response()->json(['success' => 'true', 'data' => $transaction, 'message' => 'Payment in successfully'], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
        }
    }

    public function paymentInView(Request $request)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $biilTransaction = Transaction::where('id', $request->bill_id)->where('transaction_type', 'payment_in')->first();
            if ($biilTransaction) {
                $PaymentIn = PaymentInBill::where('transaction_id', $request->bill_id)->get();
               foreach ($PaymentIn as $key => $value) {
                    $saleBill = Transaction::where('bill_id', $value->bill_id)->where('is_bill',1)->where('transaction_type', 'sale')->first();
                    $value->current_amount = $saleBill->pending_amount;
                }
                $biilTransaction->payments = $PaymentIn;
                $paymentInTypes = PaymentInType::where('payment_in_tran_id', $request->bill_id)->get();
                if(count($paymentInTypes) == 1) {
                    $paymentInTypeName = $paymentInTypes[0]->name;
                } else {
                    // $paymentInTypes = $paymentInTypes;
                    $paymentInTypeName = 'Manual';
                }
                $biilTransaction->payment_type = $paymentInTypeName;
                $biilTransaction->payment_type_data = $paymentInTypes;
                return response()->json(['success' => 'true', 'data' => $biilTransaction, 'message' => 'Payment in fetch successfully'], 200);
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

    public function paymentInUpdate(Request $request, $id)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $transaction = Transaction::find($id);
            if (!$transaction) {
                return response()->json(['success' => 'false', 'data' => [], 'message' => 'Transaction Not Found'], 200);
            }
            $oldAmount = $transaction->total_amount;
            $transaction->party_id = $request->party_id;
            $transaction->bill_id = 0;
            $transaction->total_amount = $request->final_amount;
            $transaction->pending_amount = 0;
            $transaction->type = 'paymentIn';
            $transaction->transaction_type = 'payment_in';
            $transaction->date = now();
            $transaction->save();

            $party = Supplier::where('id', $request->supplier_id)->first();
            if ($party) {
                $oldFinalAmount = $oldAmount - $request->final_amount;
                $party->amount -= $oldFinalAmount;
                $party->save();
            }

            foreach ($request->bills as $key => $bills) {
                $biilTransaction = Transaction::where('bill_id', $bills['bill_id'])->where('transaction_type', 'sale')->first();
                if (isset($biilTransaction)) {

                    $checkOldPayment = PaymentInBill::where('transaction_id', $transaction->id)->where('bill_id', $bills['bill_id'])->first();
                    if ($checkOldPayment) {
                        $PaymentIn = PaymentInBill::where('id', $checkOldPayment->id)->first();
                        $oldInAMount = $PaymentIn->amount;
                        $PaymentIn->transaction_id = $transaction->id;
                        $PaymentIn->bill_id = $bills['bill_id'];
                        $PaymentIn->bill_amount = $bills['bill_amount'];
                        $PaymentIn->current_amount = $bills['current_amount'];
                        $PaymentIn->amount = $bills['amount'];
                        $PaymentIn->save();
                    } else {
                        $PaymentIn = new PaymentInBill();
                        $oldInAMount = 0;
                        $PaymentIn->transaction_id = $transaction->id;
                        $PaymentIn->bill_id = $bills['bill_id'];
                        $PaymentIn->bill_amount = $bills['bill_amount'];
                        $PaymentIn->current_amount = $bills['current_amount'];
                        $PaymentIn->amount = $bills['amount'];
                        $PaymentIn->save();
                    }

                    $newAmount = ($biilTransaction->pending_amount + $oldInAMount) - $bills['amount'];
                    $biilTransaction->pending_amount = $newAmount;
                    if ($biilTransaction->pending_amount == 0) {
                        $biilTransaction->type = 'sale paid';
                        $mainOrderFind = Order::where('id', $bills['bill_id'])->first();
                        $mainOrderFind->bill_status = 1;
                        $mainOrderFind->save();
                    } else {
                        $biilTransaction->type = 'sale partial';
                    }
                    $biilTransaction->save();

                }
            }
            $paymentInTypes = PaymentInType::where('payment_in_tran_id', $id)->delete();
            if ($request->payment_type == 'Manual') {
                $paymentType = new PaymentInType();
                $paymentType->payment_in_tran_id = $transaction->id;
                $paymentType->name = 'Cash';
                $paymentType->amount = $request->payment_cash_amount;
                $paymentType->save();

                // Bank Transaction Add In Cash
                $bank = Banks::where('is_default', 1)->first();
                $bankTransactionCash = BankTransaction::where('withdraw_from', $transaction->id)->where('p_type', 'payment_in_cash')->first();
                if (!$bankTransactionCash) {
                    BankTransaction::where('withdraw_from', $transaction->id)->where('p_type', 'payment_in_cash')->delete();
                    $bankTransactionCash = new BankTransaction();
                }
                $oldCashAmount = $bankTransactionCash->balance;
                $bankTransactionCash->withdraw_from = $transaction->id;
                $bankTransactionCash->p_type = 'payment_in_cash';
                $bankTransactionCash->deposit_to = "Cash";
                $bankTransactionCash->balance = $request->payment_cash_amount;
                $bankTransactionCash->date = $request->date ?? now();
                $bankTransactionCash->save();
                $bank->total_amount = ($bank->total_amount - $oldCashAmount) + $request->payment_cash_amount;
                $bank->save();

                $paymentType = new PaymentInType();
                $paymentType->payment_in_tran_id = $transaction->id;
                $paymentType->name = 'Bank';
                $paymentType->amount = $request->payment_bank_amount;
                $paymentType->save();

                // Bank Transaction Add In Bank
                $Bankbank = Banks::where('is_default', 1)->first();
                $bankTransaction = BankTransaction::where('withdraw_from', $transaction->id)->where('p_type', 'payment_in_bank')->first();
                if (!$bankTransaction) {
                    BankTransaction::where('withdraw_from', $transaction->id)->where('p_type', 'payment_in_bank')->delete();
                    $bankTransaction = new BankTransaction();
                }
                $oldBankAmount = $bankTransaction->balance;
                $bankTransaction->withdraw_from = $transaction->id;
                $bankTransaction->p_type = 'payment_in_bank';
                $bankTransaction->deposit_to = $Bankbank->id;
                $bankTransaction->balance = $request->payment_bank_amount;
                $bankTransaction->date = $request->date ?? now();
                $bankTransaction->save();
                $Bankbank->total_amount = ($Bankbank->total_amount - $oldBankAmount) + $request->payment_bank_amount;
                $Bankbank->save();

            } else {
                $paymentType = new PaymentInType();
                $paymentType->payment_in_tran_id = $transaction->id;
                if ($request->payment_type == 'Cash') {
                    $paymentType->name = 'Cash';
                    $bank = Banks::where('is_default', 1)->first();
                    $bankTransactionCash = BankTransaction::where('withdraw_from', $transaction->id)->where('p_type', 'payment_in_cash')->first();
                    if (!$bankTransactionCash) {
                        BankTransaction::where('withdraw_from', $transaction->id)->where('p_type', 'payment_in_bank')->Orwhere('p_type', 'payment_in_cash')->delete();
                        $bankTransactionCash = new BankTransaction();
                    }
                    $bankTransactionCash->withdraw_from = $transaction->id;
                    $bankTransactionCash->p_type = 'payment_in_cash';
                    $bankTransactionCash->deposit_to = "Cash";
                    $bankTransactionCash->balance = $request->payment_cash_amount;
                    $bankTransactionCash->date = $request->date ?? now();
                    $bankTransactionCash->save();
                    $bank->total_amount = $bank->total_amount + $request->payment_cash_amount;
                    $bank->save();
                } elseif ($request->payment_type == 'Bank') {
                    $paymentType->name = 'Bank';

                    $Bankbank = Banks::where('is_default', 1)->first();
                    $bankTransaction = BankTransaction::where('withdraw_from', $transaction->id)->where('p_type', 'payment_in_bank')->first();
                    if (!$bankTransaction) {
                        BankTransaction::where('withdraw_from', $transaction->id)->where('p_type', 'payment_in_bank')->Orwhere('p_type', 'payment_in_cash')->delete();
                        $bankTransactionCash = new BankTransaction();
                    }
                    $oldBankAmount = $bankTransaction->balance;
                    $bankTransaction->withdraw_from = $transaction->id;
                    $bankTransaction->p_type = 'payment_in_bank';
                    $bankTransaction->deposit_to = $Bankbank->id;
                    $bankTransaction->balance = $request->payment_bank_amount;
                    $bankTransaction->date = $request->date ?? now();
                    $bankTransaction->save();
                    $Bankbank->total_amount = ($Bankbank->total_amount - $oldBankAmount) + $request->payment_bank_amount;
                    $Bankbank->save();
                }
                $paymentType->amount = $request->payment_amount;
                $paymentType->save();
            }

            return response()->json(['success' => 'true', 'data' => $transaction, 'message' => 'Payment in update successfully'], 200);
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
