<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\BillCollection;
use App\Models\Expense;
use App\Models\Order;
use App\Models\ReturnOrder;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use App\Models\PurchaseReturnInvoice;
use App\Models\Supplier;
use App\Models\PaymentInBill;
use App\Models\TransferAmount;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SupplierController extends Controller
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
            $supplier = Supplier::orderByDesc('id')->where('deleted_at', 0)->get();
            return response()->json(['success' => 'true','data' => $supplier,'message' => 'Supplier List Fetch successfully'], 200);
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
            ]);
            if ($validator->fails()) {
                return response()->json(['success' => 'false', 'data' => $validator->errors ()], 200);
            }
            $supplier = new Supplier();
            $supplier->name = $request->name;
            $supplier->phone_number = $request->phone_number;
            $supplier->address = $request->address;
            $supplier->state_id = $request->state_id;
            $supplier->amount = 0;
            $supplier->save();
            return response()->json(['success' => 'true','data' => $supplier,'message' => 'Supplier created successfully'], 200);
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
            $validator = Validator::make($request->all(), [
                'name' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['success' => 'false', 'data' => $validator->errors ()], 200);
            }
            $supplier = Supplier::find($id);
            if (!$supplier) {
                return response()->json(['success' => 'false','message' => 'Supplier not found'], 200);
            }
            $supplier->name = $request->name;
            $supplier->phone_number = $request->phone_number;
            $supplier->address = $request->address;
            $supplier->state_id = $request->state_id;
            $supplier->save();
            return response()->json(['success' => 'true','data' => $supplier,'message' => 'Supplier updated successfully'], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
        }
    }

    public function bills(Request $request)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $order = Order::where('supplier_id', $request->party_id)->where('bill_status', 0)->orderByDesc('id')->get();
            foreach ($order as $key => $orders) {
                $collection = BillCollection::where('party_id', $request->party_id)->where('bill_id', $orders->id)->sum('amount');
                $returnBill = ReturnOrder::where('supplier_id', $request->party_id)->where('order_id', $orders->id)->sum('final_amount');
                $expanses = Expense::where('party_id', $request->party_id)->where('bill_id', $orders->id)->sum('total_amount');
                $debitTransfer = TransferAmount::where('from_transfer_id', $request->party_id)->where('type', 'bill')->where('bill_id', $orders->id)->sum('amount');
                $creditTransfer = TransferAmount::where('to_transfer_id', $request->party_id)->where('type', 'bill')->where('from_bill_id', $orders->id)->sum('amount');
                $paymentBill = PaymentInBill::where('bill_id', $orders->id)->sum('amount');
                $orders->pending = $orders->final_amount - $collection - $returnBill - $expanses - $debitTransfer - $paymentBill + $creditTransfer;
            }
            return response()->json(['success' => 'true','data' => $order,'message' => 'Bills List Fetch successfully'], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
        }
    }

    public function delete(Request $request, $id)
    {
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $supplier = Supplier::find($id);
            $order = Order::where('supplier_id', $id)->count();
            $returnOrder = ReturnOrder::where('supplier_id', $id)->count();
            $purchase = PurchaseInvoice::where('party_id', $id)->count();
            $purchaseReturn = PurchaseReturnInvoice::where('party_id', $id)->count();
            if ($order > 0 || $returnOrder > 0 || $purchase > 0 || $purchaseReturn > 0 ) {
                return response()->json(['success' => 'false','message' => 'Cannot delete this party because related transactions exist.'], 200);
            }
            if (!$supplier) {
                return response()->json(['success' => 'false','message' => 'Party not found'], 200);
            }
            $supplier->deleted_at = 1;
            $supplier->save();
            // Uncomment the line below if you want to actually delete the supplier
            // $supplier->delete();
            return response()->json(['success' => 'true','message' => 'Party deleted successfully'], 200);
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
