<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CollectionType;
use App\Models\ExpanseCategory;
use App\Models\Order;
use App\Models\PaymentType;
use App\Models\User;
use Illuminate\Http\Request;

class ExpanseCategoryController extends Controller
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

    public function addCategory(Request $request){
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $checkDuplicate = ExpanseCategory::where('name', $request->name)->first();
            if ($checkDuplicate) {
                return response()->json(['success' => 'false','message' => 'Category already exists'], 200);
            } 
            $category = new ExpanseCategory();
            $category->name = $request->name;
            $category->type = $request->type;
            $category->save();
            return response()->json(['success' => 'true','data' => $category,'message' => 'Category created successfully'], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
        }
    }

    public function listCategory(Request $request){
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $categories = ExpanseCategory::where('type', $request->type)->where('name', 'like', '%' . $request->search . '%')->get();
            return response()->json(['success' => 'true','data' => $categories,'message' => 'Categories list'], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
        }
    }

    public function transactionList(Request $request){
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $perPage = $request->get('per_page', 25);

            $transactions = CollectionType::where('name', 'Expanses')->paginate($perPage);
            foreach ($transactions as $key => $transaction) {
                $bill = Order::where('id', $transaction->bill_id)->first();
                $transaction->date = $bill->date;
            }
            $transactionList = [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
                'data' => $transactions->items(),
            ];
            return response()->json(['success' => 'true','data' => $transactionList,'message' => 'Transactions list'], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
        }
    }

    public function addPaymentType(Request $request){
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $checkDuplicate = PaymentType::where('name', $request->name)->first();
            if ($checkDuplicate) {
                return response()->json(['success' => 'false','message' => 'Payment Type already exists'], 200);
            } 
            $paymentType = new PaymentType();
            $paymentType->name = $request->name;
            $paymentType->save();
            return response()->json(['success' => 'true','data' => $paymentType,'message' => 'Payment Type created successfully'], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
        }
    }

    public function listPaymentType(Request $request){
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $paymentTypes = PaymentType::where('name', 'like', '%' . $request->search . '%')->get();
            return response()->json(['success' => 'true','data' => $paymentTypes,'message' => 'Payment Types list'], 200);
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
