<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ExpanseCategory;
use App\Models\ExpanseItem;
use App\Models\ExpenseDetail;
use App\Models\User;
use App\Models\Tax;
use Illuminate\Http\Request;

class ExpanseItemController extends Controller
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

    public function addItem(Request $request){
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $checkDuplicate = ExpanseItem::where('name', $request->name)->first();
            if ($checkDuplicate) {
                return response()->json(['success' => 'false','message' => 'Item already exists'], 200);
            } 
            $item = new ExpanseItem();
            $item->name = $request->name;
            $item->price = $request->price;
            $item->tax = $request->tax ?? 0;
            $item->tax_id = $request->tax_id ?? 0;
            $item->final_price = $request->price + ($request->price * $request->tax / 100);
            $item->save();
            return response()->json(['success' => 'true','data' => $item,'message' => 'Item created successfully'], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
        }
    }

    public function listItem(Request $request){
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $items = ExpanseItem::all()->map(function($i){
                $tax = Tax::where('id', $i->tax_id)->first();
                if($tax){
                    $i->tax_type = $tax->name;
                    $i->gst_amount = ($i->price * $tax->value) / 100;
                    $i->value =   $tax->value;
                }else{
                    $i->tax_type = '';
                    $i->gst_amount = '';
                    $i->value = '';
                }
                return $i;
            }); 


             
            // $items = ExpanseCategory::all();
            return response()->json(['success' => 'true','data' => $items,'message' => 'Items list'], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
        }
    }

    public function updateItem(Request $request, $id){
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $item = ExpanseItem::find($id);
            if (!$item) {
                return response()->json(['success' => 'false','message' => 'Item not found'], 200);
            }
            $checkDuplicate = ExpanseItem::where('name', $request->name)->where('id','<>',$item->id)->first();
            if ($checkDuplicate) {
                return response()->json(['success' => 'false','message' => 'Item already exists'], 200);
            }
            $checkName = ExpenseDetail::where('type', $item->name)->first();
            if ($checkName) {
                if ($checkName->type != $request->name) {
                    return response()->json(['success' => 'false','message' => 'Item already exists in transaction you can not change name'], 200);
                }
            } 
            // $item = new ExpanseItem();
            $item->name = $request->name;
            $item->price = $request->price;
            $item->tax = $request->tax ?? 0;
            $item->tax_id = $request->tax_id ?? 0;
            $item->final_price = $request->price + ($request->price * $request->tax / 100);
            $item->save();
            return response()->json(['success' => 'true','data' => $item,'message' => 'Item update successfully'], 200);
        } else {
            $errors = [];
            array_push($errors, ['code' => 'auth-001', 'message' => 'Unauthorized.']);
            return response()->json([
                'success' => 'false',
                'data' => $errors
            ], 200);
        }
    }

    public function deleteItem(Request $request, $id){
        $data = $this->get_admin_by_token($request);
        if ($data) {
            $item = ExpanseItem::find($id);
            if (!$item) {
                return response()->json(['success' => 'false','message' => 'Item not found'], 200);
            }
            $checkDuplicate = ExpenseDetail::where('type', $item->name)->first();
            if ($checkDuplicate) {
                return response()->json(['success' => 'false','message' => 'Item already exists in transaction you can not delete it'], 200);
            }
            $item->delete();
            return response()->json(['success' => 'true','data' => $item,'message' => 'Item deleted successfully'], 200);
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
