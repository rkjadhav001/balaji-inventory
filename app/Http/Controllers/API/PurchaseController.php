<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseProduct;
use App\Models\User;
use Illuminate\Http\Request;

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
            $purchase->total_purchase_amount = $request->total_purchase_amount;
            $purchase->total_gst_amount = $request->total_gst_amount;
            $purchase->total_payable_amount = $request->total_payable_amount;
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
                $purchaseProduct->save();
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
}
