<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AppConfig;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseReturnInvoice;
use App\Models\ReturnOrder;
use App\Models\Order;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function saleLedgerPartyItemWise(Request $request)
    {
        $appStateId = AppConfig::where('name', 'state_id')->value('value');

        $orders = Order::with(['orderProduct.product.tax', 'supplier']) // 'unit' assumed to get 'Item Rate per'
            ->where('order_type', 'retailer')
            ->get();

        $reportRows = [];

        foreach ($orders as $order) {
            $invoiceNo = 'SA/'.$order->id ?? 'N/A';
            $referenceNo = 'SA/'.$order->id ?? 'N/A'; // or use another ref field
            $invoiceDate = $order->date->format('d/m/Y'); // or use created_at
            $partyName = optional($order->supplier)->name ?? 'Unknown';
            $salesLedger = 'Sales';

            foreach ($order->orderProduct as $orderProduct) {
                $product = $orderProduct->product;
                if (!$product || !$product->tax) continue;

                $qty = $orderProduct->total_qty;
                $rate = $orderProduct->price;
                $unit = 'pcs'; // adjust as per your schema
                $gstRate = $product->tax->value;
                $amount = $qty * $rate;

                $sellGstType = $product->sell_gst_type;
                $taxableAmount = $sellGstType === 'included'
                    ? $amount / (1 + ($gstRate / 100))
                    : $amount;

                $supplierStateId = optional($order->supplier)->state_id;
                $isSameState = $supplierStateId == $appStateId;

                $cgst = $isSameState ? $taxableAmount * ($gstRate / 2) / 100 : 0;
                $sgst = $isSameState ? $taxableAmount * ($gstRate / 2) / 100 : 0;
                $igst = !$isSameState ? $taxableAmount * $gstRate / 100 : 0;

                $totalAmount = $sellGstType === 'included'
                    ? $amount
                    : ($taxableAmount + $cgst + $sgst + $igst);

                $reportRows[] = [
                    'Invoice No.'     => $invoiceNo,
                    'Reference No.'   => $referenceNo,
                    'Date'            => $invoiceDate,
                    'Party Name'      => $partyName,
                    'Sales Ledger'    => $salesLedger,
                    'Name of Item'    => $product->name,
                    'Quantity'        => $qty,
                    'Item Rate'       => $rate,
                    'Item Rate per'   => $unit,
                    'Amount'          => round($taxableAmount, 2),
                    'CGST'            => round($cgst, 2),
                    'SGST'            => round($sgst, 2),
                    'IGST'            => round($igst, 2),
                    'TOTAL AMOUNT'    => round($totalAmount, 2),
                ];
            }
        }
        return view('reports.party-item-wise', compact('reportRows'));

        //    // HTML table string
        // $html = '<table border="1">
        // <tr>
        //     <th>Invoice No.</th>
        //     <th>Reference No.</th>
        //     <th>Date</th>
        //     <th>Party Name</th>
        //     <th>Sales Ledger</th>
        //     <th>Name of Item</th>
        //     <th>Quantity</th>
        //     <th>Item Rate</th>
        //     <th>Item Rate per</th>
        //     <th>Amount</th>
        //     <th>CGST</th>
        //     <th>SGST</th>
        //     <th>IGST</th>
        //     <th>TOTAL AMOUNT</th>
        // </tr>';

        // foreach ($reportRows as $row) {
        //     $html .= '<tr>';
        //     foreach ($row as $value) {
        //         $html .= '<td>' . htmlspecialchars($value) . '</td>';
        //     }
        //     $html .= '</tr>';
        // }

        // $html .= '</table>';

        // $filename = "party_item_wise_report_" . date('Y-m-d') . ".xls";

        // return response($html)
        // ->header('Content-Type', 'application/vnd.ms-excel')
        // ->header('Content-Disposition', "attachment; filename=\"$filename\"");
    }

    public function salePurchaseItemDetail(Request $request)
    {
        $appStateId = AppConfig::where('name', 'state_id')->value('value');
        $appContact = AppConfig::where('name', 'contact_number')->value('value');
        $appName = AppConfig::where('name', 'name')->value('value');

        $productId = $request->input('product_id');
        $report = [];

        // Sale data
        $orders = Order::with(['orderProduct.product'])
            ->where('order_type', 'retailer')
            ->get();

        foreach ($orders as $order) {
            foreach ($order->orderProduct as $orderProduct) {
                $product = $orderProduct->product;
                if (!$product) continue;
                if ($productId && $product->id != $productId) continue;

                $report[] = [
                    'product_name' => $product->name,
                    'type' => 'sale',
                    'date' => $order->date,
                    'box' => $orderProduct->box ?? 0,
                    'patti' => $orderProduct->patti ?? 0,
                    'packet' => $orderProduct->packet ?? 0,
                    'total' => $orderProduct->total_cost ?? 0,
                ];
            }
        }

        // Sale Return data
        $returnOrders = ReturnOrder::with(['returnOrderProducts.product'])->get();
        foreach ($returnOrders as $returnOrder) {
            foreach ($returnOrder->returnOrderProducts as $orderProductReturn) {
                $product = $orderProductReturn->product;
                if (!$product) continue;
                if ($productId && $product->id != $productId) continue;

                $report[] = [
                    'product_name' => $product->name,
                    'type' => 'sale return',
                    'date' => $returnOrder->created_at,
                    'box' => $orderProductReturn->box ?? 0,
                    'patti' => $orderProductReturn->patti ?? 0,
                    'packet' => $orderProductReturn->packet ?? 0,
                    'total' => $orderProductReturn->total_cost ?? 0,
                ];
            }
        }

        // Purchase data
        $purchases = PurchaseInvoice::with('purchaseDetails.product.tax')->get();
        foreach ($purchases as $purchase) {
            foreach ($purchase->purchaseDetails as $purchaseDetail) {
                $product = $purchaseDetail->product;
                if (!$product) continue;
                if ($productId && $product->id != $productId) continue;

                $report[] = [
                    'product_name' => $product->name,
                    'type' => 'purchase',
                    'date' => $purchase->date,
                    'box' => $purchaseDetail->box ?? 0,
                    'patti' => $purchaseDetail->patti ?? 0,
                    'packet' => $purchaseDetail->packet ?? 0,
                    'total' => $purchaseDetail->total_amount ?? 0,
                ];
            }
        }

        // Purchase Return data
        $purchaseReturns = PurchaseReturnInvoice::with('purchaseReturnDetails.product')->get();
        foreach ($purchaseReturns as $purchaseReturn) {
            foreach ($purchaseReturn->purchaseReturnDetails as $purchaseReturnDetail) {
                $product = $purchaseReturnDetail->product;
                if (!$product) continue;
                if ($productId && $product->id != $productId) continue;

                $report[] = [
                    'product_name' => $product->name,
                    'type' => 'purchase return',
                    'date' => $purchaseReturn->date,
                    'box' => $purchaseReturnDetail->box ?? 0,
                    'patti' => $purchaseReturnDetail->patti ?? 0,
                    'packet' => $purchaseReturnDetail->packet ?? 0,
                    'total' => $purchaseReturnDetail->total_amount ?? 0,
                ];
            }
        }

        // Sort by date descending
        $sortedReport = collect($report)->sortByDesc('date')->values();

        return response()->json([
            'success' => true,
            'data' => $sortedReport,
            'message' => 'Report fetched successfully'
        ], 200);
    }



}
