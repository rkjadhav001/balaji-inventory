<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Banks;
use App\Models\BankTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BanksController extends Controller
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

    public function add_bank(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'bank_name' => 'required',
            'opening_balance' => 'required',
            'date' => 'required', 
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $this->get_admin_by_token($request); 
        if($data){
            $checkOld = Banks::where('bank_name', $request->bank_name)->first();
            if($checkOld){
                return response()->json(['success' => 'false', 'message' => 'Bank already exists'], 200);
            }
            $input = [
                'bank_name' => $request->bank_name,
                'opening_balance' => $request->opening_balance,
                'date' => $request->date,
                'total_amount' => $request->opening_balance,
            ];
    
            $bank =  Banks::create($input); 
            if($request->opening_balance > 0){
                $bankData = [
                    'deposit_to' => $bank->id,
                    'p_type' => 'opening_balance',
                    'balance' => $request->opening_balance,
                ]; 
                BankTransaction::create($bankData);
            } 
            return response()->json(['success' => 'true', 'message' => 'Bank Add successfully'], 200);
        }else{
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
        }
        
    }

    public function udpateBank(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bank_name' => 'required',
            'opening_balance' => 'required',
            'date' => 'required', 
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $this->get_admin_by_token($request); 
        $checkOld = Banks::where('bank_name', $request->bank_name)->where('id','<>',$request->id)->first();
        if($checkOld){
            return response()->json(['success' => 'false', 'message' => 'Bank already exists'], 200);
        }
        if($data){
            $bank = Banks::find($request->id);
            $bank->bank_name = $request->bank_name;
            $previousOpeningBalance = $bank->opening_balance;
            $bank->opening_balance = $request->opening_balance;
            $bank->date = $request->date;
            $bank->total_amount = ($bank->total_amount - $previousOpeningBalance) + $request->opening_balance;
            $bank->save();
            
            if($request->opening_balance > 0){
                $bankData = [
                    'deposit_to' => $bank->id,
                    'p_type' => 'opening_balance',
                    'balance' => $request->opening_balance,
                ]; 
                $existingTransaction = BankTransaction::where('deposit_to', $request->id)
                ->where('p_type', 'opening_balance')
                ->first();
        
                if ($existingTransaction) {
                    // BankTransaction::where('deposit_to', $request->id)->where('p_type', 'opening_balance')->update($bankData);
                    $existingTransaction->update($bankData);
                } else {
                    BankTransaction::create($bankData);
                }
            } 
            return response()->json(['success' => 'true', 'message' => 'Bank update successfully'], 200);
        }else{
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
        }
        
    }

    public function bank_transaction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'deposit_to' => 'required',
            'p_type' => 'required',
            'balance' => 'required', 
            'withdraw_from' => 'required',
            'date' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        $data = $this->get_admin_by_token($request);
        if($data){ 
            $input = [
                'withdraw_from' => $request->withdraw_from,
                'deposit_to' => $request->deposit_to,
                'p_type' => $request->p_type,
                'balance' => $request->balance,
                'date' =>  $request->date,
                'description' => $request->description,
            ];
            BankTransaction::create($input);
            if ($request->p_type == 'cash_to_bank') {     
                $banks = Banks::where('id',$request->deposit_to)->first();
                if($banks){
                    Banks::where('id',$banks->id)->update(['total_amount' => $banks->total_amount + $request->balance]);
                }
            } elseif ($request->p_type == 'bank_to_cash') {
                $banks = Banks::where('id',$request->withdraw_from)->first();
                if($banks){
                    Banks::where('id',$banks->id)->update(['total_amount' => $banks->total_amount - $request->balance]);
                }
            } elseif ($request->p_type == 'bank_to_bank') {
                $fromBank = Banks::where('id',$request->withdraw_from)->first();
                if($fromBank){
                    Banks::where('id',$fromBank->id)->update(['total_amount' => $fromBank->total_amount - $request->balance]);
                }
                $toBank = Banks::where('id',$request->deposit_to)->first();
                if($toBank){
                    Banks::where('id',$toBank->id)->update(['total_amount' => $toBank->total_amount + $request->balance]);
                }
            }

            return response()->json(['success' => 'true', 'message' => 'Bank Transaction Add successfully'], 200);
        }else{ 
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
            
        }  
    }



    public function update_bank_transaction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'deposit_to' => 'required',
            'p_type' => 'required',
            'balance' => 'required', 
            'withdraw_from' => 'required',
            'date' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        $data = $this->get_admin_by_token($request);
        if($data){ 
            $input = [
                'withdraw_from' => $request->withdraw_from,
                'deposit_to' => $request->deposit_to,
                'p_type' => $request->p_type,
                'balance' => $request->balance,
                'date' =>  $request->date,
                'description' => $request->description,
            ];

            $oldTransaction = BankTransaction::where('id', $request->id)->first();
            BankTransaction::where('id', $request->id)->update($input);

            if ($request->p_type == 'cash_to_bank') {     
                $banks = Banks::where('id',$request->deposit_to)->first();
                if($banks){
                    $oldBalance = $banks->total_amount - $oldTransaction->balance;
                    Banks::where('id',$banks->id)->update(['total_amount' => $oldBalance + $request->balance]);
                }
            } elseif ($request->p_type == 'bank_to_cash') {
                $banks = Banks::where('id',$request->withdraw_from)->first();
                if($banks){
                    $oldBalance = $banks->total_amount - $oldTransaction->balance;
                    Banks::where('id',$banks->id)->update(['total_amount' => $oldBalance - $request->balance]);
                }
            } elseif ($request->p_type == 'bank_to_bank') {
                $fromBank = Banks::where('id',$request->withdraw_from)->first();
                if($fromBank){
                    $oldBalance = $fromBank->total_amount - $oldTransaction->balance;
                    Banks::where('id',$fromBank->id)->update(['total_amount' => $oldBalance - $request->balance]);
                }
                $toBank = Banks::where('id',$request->deposit_to)->first();
                if($toBank){
                    $oldBalance = $toBank->total_amount - $oldTransaction->balance;
                    Banks::where('id',$toBank->id)->update(['total_amount' => $oldBalance + $request->balance]);
                }
            }

            return response()->json(['success' => 'true', 'message' => 'Bank Transaction Update successfully'], 200);
        }else{ 
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
            
        }  
    }

    public function bank_transaction_list(Request $request)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $bank = Banks::where('id', $request->bank_id)->first();
            if ($bank) {
                $bankTransactions = BankTransaction::where('p_type', '<>', 'sale_cash')->where('p_type', '<>', 'purchase_cash')
                ->where(function ($query) use ($request) {
                    $query->where(function ($subQuery) use ($request) {
                        $subQuery->where('p_type', 'cash_to_bank')
                            ->where('deposit_to', $request->bank_id);
                    })
                    ->orWhere(function ($subQuery) use ($request) {
                        $subQuery->where('p_type', 'bank_to_cash')
                            ->where('withdraw_from', $request->bank_id);
                    })
                    ->orWhere(function ($subQuery) use ($request) {
                        $subQuery->where('p_type', 'bank_to_bank')
                            ->where(function ($innerQuery) use ($request) {
                                $innerQuery->where('deposit_to', $request->bank_id)
                                    ->orWhere('withdraw_from', $request->bank_id);
                            });
                    })
                    ->orWhere(function ($subQuery) use ($request) {
                        $subQuery->where('p_type', 'sale_bill')
                            ->where('deposit_to', $request->bank_id);
                    })
                    ->orWhere(function ($subQuery) use ($request) {
                        $subQuery->where('p_type', 'opening_balance')
                            ->where('deposit_to', $request->bank_id);
                    })
                    ->orWhere(function ($subQuery) use ($request) {
                        $subQuery->where('p_type', 'payment_in_bank')
                            ->where('deposit_to', $request->bank_id);
                    })
                    ->orWhere(function ($subQuery) use ($request) {
                        $subQuery->where('p_type', 'purchase_bill')
                            ->where('withdraw_from', $request->bank_id);
                    })
                    ->orWhere(function ($subQuery) use ($request) {
                        $subQuery->where('p_type', 'due_payment_bank')
                            ->where('withdraw_from', $request->bank_id);
                    });
                })
                ->get();
            
                $bankTransactions->each(function ($transaction) use ($bank) {
                    // $transaction->bank_name = $bank->bank_name;
                    if ($transaction->p_type == 'cash_to_bank') { 
                        $bank = Banks::where('id', $transaction->deposit_to)->first();
                        $transaction->bank = $bank;
                        $transaction->title = 'Deposited';
                        $transaction->credit_debit = "Credit";
                        $transaction->bank_to = '-';
                    } elseif ($transaction->p_type == 'bank_to_cash') {
                        $bank = Banks::where('id', $transaction->withdraw_from)->first();
                        $transaction->bank = $bank;
                        $transaction->title = 'Withdrawal';
                        $transaction->credit_debit = "Debit";
                        $transaction->bank_to = '-';
                    } elseif ($transaction->p_type == 'bank_to_bank') {
                        if ($transaction->deposit_to == $bank->id) {
                            $bank = Banks::where('id', $transaction->withdraw_from)->first();
                            $transaction->bank = $bank;
                            $transaction->title = 'From Bank - '. $bank->bank_name;
                            $transaction->credit_debit = "Credit";
                            $toBank = Banks::where('id', $transaction->deposit_to)->first();
                            $transaction->bank_to = $toBank->bank_name;
                        } else {
                            $bank = Banks::where('id', $transaction->withdraw_from)->first();
                            $transaction->bank = $bank;
                            $transaction->title = 'To Bank - '. $bank->bank_name;
                            $transaction->credit_debit = "Debit";
                            $transaction->bank_to = '-';
                        }
                    } elseif ($transaction->p_type == 'sale_bill') {
                        $bank = Banks::where('id', $transaction->deposit_to)->first();
                        $transaction->bank = $bank;
                        $transaction->title = 'Sale';
                        $transaction->credit_debit = "Credit";
                        $transaction->bank_to = '-';
                    } elseif ($transaction->p_type == 'opening_balance') {
                        $bank = Banks::where('id', $transaction->deposit_to)->first();
                        $transaction->bank = $bank;
                        $transaction->title = 'Opening Balance';
                        $transaction->credit_debit = "Credit";
                        $transaction->bank_to = '-';
                    } elseif ($transaction->p_type == 'payment_in_bank') {
                        $bank = Banks::where('id', $transaction->deposit_to)->first();
                        $transaction->bank = $bank;
                        $transaction->title = 'Payment In';
                        $transaction->credit_debit = "Credit";
                        $transaction->bank_to = '-';
                    } elseif ($transaction->p_type == 'purchase_bill') {
                        $bank = Banks::where('id', $transaction->withdraw_from)->first();
                        $transaction->bank = $bank;
                        $transaction->title = 'Purchase';
                        $transaction->credit_debit = "Debit";
                        $transaction->bank_to = '-';
                    } elseif ($transaction->p_type == 'due_payment_bank') {
                        $bank = Banks::where('id', $transaction->withdraw_from)->first();
                        $transaction->bank = $bank;
                        $transaction->title = 'Due Payment';
                        $transaction->credit_debit = "Debit";
                        $transaction->bank_to = '-';
                    }
                });

                return response()->json([
                    'success' => 'true',
                    'data' => $bankTransactions,
                    'message' => 'List fetched successfully'
                ], 200);
            }

            return response()->json([
                'success' => 'true',
                'data' => [],
                'message' => 'List fetched successfully'
            ], 200);
        } else {
            return response()->json([
                'success' => 'false',
                'data' => [['code' => 'auth-001', 'message' => 'Unauthorized.']]
            ], 200);
        }
    }

    public function cashTransactionList(Request $request)
    {
        $data = $this->get_admin_by_token($request);
        if($data){
            $cashTransaction = BankTransaction::where('p_type', 'cash_to_bank')->Orwhere('p_type', 'bank_to_cash')->Orwhere('p_type', 'sale_cash')->Orwhere('p_type', 'payment_in_cash')->Orwhere('p_type', 'purchase_cash')->Orwhere('p_type', 'due_payment_cash')->get();
            $cashTransaction->each(function ($transaction)  {
                if ($transaction->p_type == 'cash_to_bank') { 
                    $bank = Banks::where('id', $transaction->deposit_to)->first();
                    $transaction->title = 'Bank Deposit '. '('. $bank->bank_name. ')';
                    $transaction->bank = $bank;               
                    $transaction->credit_debit = 'Debit';
                } elseif ($transaction->p_type == 'bank_to_cash') {
                    $bank = Banks::where('id', $transaction->withdraw_from)->first();
                    $transaction->bank = $bank;
                    $transaction->title = 'Bank Withdrawal '. '('. $bank->bank_name. ')';
                    $transaction->credit_debit = 'Credit';
                } elseif ($transaction->p_type == 'sale_cash') {
                    $transaction->bank = (object)[];
                    $transaction->title = 'Sale';
                    $transaction->credit_debit = 'Credit';
                } elseif ($transaction->p_type == 'payment_in_cash') {
                    $transaction->bank = (object)[];
                    $transaction->title = 'Payment In';
                    $transaction->credit_debit = 'Credit';
                } elseif ($transaction->p_type == 'purchase_cash') {
                    $transaction->bank = (object)[];
                    $transaction->title = 'Purchase';
                    $transaction->credit_debit = 'Debit';
                } elseif ($transaction->p_type == 'due_payment_cash') {
                    $transaction->bank = (object)[];
                    $transaction->title = 'Due Payment';
                    $transaction->credit_debit = 'Debit';
                }
                // return $transaction;
            });
            return response()->json(['success' => 'true','data' => $cashTransaction, 'message' => 'List Fetch successfully'], 200);
        } else{ 
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
            
        }  
    }

    public function bank_transaction_details(Request $request)
    {
        $data = $this->get_admin_by_token($request);
        if($data){
            $bankTransaction = BankTransaction::where('id',$request->bank_transaction_id)->get();
            $bank = Banks::where('id',$bankTransaction->bank_id)->first();
            if($bank){
                $bankTransaction['bank_name'] = $bank->bank_name;
            }else{
                $bankTransaction['bank_name'] = '-';
            }    
            return response()->json(['success' => 'true','data' => $bankData, 'message' => 'List Fetch successfully'], 200); 
        }else{
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
        }
    }

    public function bank_list(Request $request)
    {
        
        $data = $this->get_admin_by_token($request);
        
        if($data){
            $bankData = Banks::all(); 
            return response()->json(['success' => 'true','data' => $bankData, 'message' => 'Bank Add successfully'], 200);
        }else{
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
        }
        
    }

    public function defaultBankChange(Request $request)
    {
        $data = $this->get_admin_by_token($request);
        if($data){
            $bankData = Banks::where('id',$request->id)->first(); 
            if($bankData){
                Banks::where('id',$request->id)->update(['is_default' => 1]);
                Banks::where('id','<>',$request->id)->update(['is_default' => 0]);
                return response()->json(['success' => 'true','message' => 'Default Bank Change successfully'], 200);
            }else{
                return response()->json(['success' => 'false','message' => 'Bank not found'], 200);
            }
        }else{
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
        }
        
    }
}
