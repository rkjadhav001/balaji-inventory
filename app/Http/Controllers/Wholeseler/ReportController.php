<?php

namespace App\Http\Controllers\Wholeseler;

use App\Http\Controllers\Controller;
use App\Models\AppConfig;
use App\Models\BillCollection;
use App\Models\Expense;
use App\Models\ExpenseDetail;
use App\Models\Order;
use App\Models\OrderDetial;
use App\Models\Product;
use App\Models\PurchaseInvoice;
use App\Models\ReturnOrder;
use App\Models\ReturnOrderDetail;
use App\Models\Wholesaler;
use App\Models\Supplier;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ReportController extends Controller 
{
    public function hsnWiseReport(Request $request)
    {
        $appStateId = AppConfig::where('name', 'state_id')->value('value');
        $appContact = AppConfig::where('name', 'contact_number')->value('value');
        $appName = AppConfig::where('name', 'name')->value('value');

        $orders = Order::with(['orderProduct.product.tax', 'supplier'])
            ->where('order_type', 'retailer')
            ->get();

        $hsnReport = [];

        foreach ($orders as $order) {
            foreach ($order->orderProduct as $orderProduct) {
                $product = $orderProduct->product;
                if (!$product || !$product->tax) continue;
                $hsnCode = $product->hsn;
                $gstRate = $product->tax->value;
                $qty = $orderProduct->total_qty;
                $rate = $orderProduct->price;
                $amount = $qty * $rate;
                $sellGstType = $product->sell_gst_type;
                if ($sellGstType === 'included') {
                    $taxableAmount = ($qty * $rate) / (1 + ($gstRate / 100));
                } else {
                    $taxableAmount = $qty * $rate;
                }
                // Determine GST Split
                $supplierStateId = optional($order->supplier)->state_id;
                $isSameState = $supplierStateId == $appStateId;
                $cgst = $isSameState ? $taxableAmount  * ($gstRate / 2) / 100 : 0;
                $sgst = $isSameState ? $taxableAmount  * ($gstRate / 2) / 100 : 0;
                $igst = !$isSameState ? $taxableAmount  * $gstRate / 100 : 0;
                // $total = $amount + $cgst + $sgst + $igst;
                $total = $sellGstType === 'included'
                ? ($qty * $rate)
                : ($taxableAmount + $cgst + $sgst + $igst);

                if (!isset($hsnReport[$hsnCode])) {
                    $hsnReport[$hsnCode] = [
                        'hsn_code' => $hsnCode,
                        'qty' => 0,
                        'taxable' => 0,
                        'gst' => $gstRate,
                        'cgst' => 0,
                        'sgst' => 0,
                        'igst' => 0,
                        'total' => 0
                    ];
                }
                $hsnReport[$hsnCode]['qty'] += $qty;
                $hsnReport[$hsnCode]['taxable'] += $taxableAmount;
                $hsnReport[$hsnCode]['cgst'] += $cgst;
                $hsnReport[$hsnCode]['sgst'] += $sgst;
                $hsnReport[$hsnCode]['igst'] += $igst;
                $hsnReport[$hsnCode]['total'] += $total;
            }
        }
        return view('reports.hsn-wise', [
            'appName' => $appName,
            'appContact' => $appContact,
            'hsnReport' => $hsnReport
        ]);
    }

    public function itemWiseReport(Request $request)
    {
        $appStateId = AppConfig::where('name', 'state_id')->value('value');
        $appContact = AppConfig::where('name', 'contact_number')->value('value');
        $appName = AppConfig::where('name', 'name')->value('value');
        $orders = Order::with(['orderProduct.product.tax', 'supplier'])
            ->where('order_type', 'retailer')
            ->get();

        $itemReport = [];

        foreach ($orders as $order) {
            foreach ($order->orderProduct as $orderProduct) {
                $product = $orderProduct->product;
                if (!$product || !$product->tax) continue;
                $hsnCode = $product->hsn;
                $productName = $product->name;
                $gstRate = $product->tax->value;
                $qty = $orderProduct->total_qty;
                $rate = $orderProduct->price;
                $amount = $qty * $rate;
                $sellGstType = $product->sell_gst_type;
                if ($sellGstType === 'included') {
                    $taxableAmount = ($qty * $rate) / (1 + ($gstRate / 100));
                } else {
                    $taxableAmount = $qty * $rate;
                }
                // Determine GST Split
                $supplierStateId = optional($order->supplier)->state_id;
                $isSameState = $supplierStateId == $appStateId;
                $cgst = $isSameState ? $taxableAmount  * ($gstRate / 2) / 100 : 0;
                $sgst = $isSameState ? $taxableAmount  * ($gstRate / 2) / 100 : 0;
                $igst = !$isSameState ? $taxableAmount  * $gstRate / 100 : 0;
                // $total = $amount + $cgst + $sgst + $igst;
                $total = $sellGstType === 'included'
                ? ($qty * $rate)
                : ($taxableAmount + $cgst + $sgst + $igst);

                if (!isset($itemReport[$productName])) {
                    $itemReport[$productName] = [
                        'product_name' => $productName,
                        'qty' => 0,
                        'taxable' => 0,
                        'gst' => $gstRate,
                        'cgst' => 0,
                        'sgst' => 0,
                        'igst' => 0,
                        'total' => 0
                    ];
                }
                $itemReport[$productName]['qty'] += $qty;
                $itemReport[$productName]['taxable'] += $taxableAmount;
                $itemReport[$productName]['cgst'] += $cgst;
                $itemReport[$productName]['sgst'] += $sgst;
                $itemReport[$productName]['igst'] += $igst;
                $itemReport[$productName]['total'] += $total;
            }
        }
        return view('reports.item-wise', [
            'appName' => $appName,
            'appContact' => $appContact,
            'itemReport' => $itemReport
        ]);
    }

}
