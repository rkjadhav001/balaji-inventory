<?php

namespace App\Http\Controllers\Wholeseler;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetial;
use App\Models\Product;
use App\Models\Wholesaler;
use Illuminate\Http\Request;

class WholeSelerController extends Controller
{
    public function index($unique_id)
    {
        $wholesaler = Wholesaler::where('unique_id',$unique_id)->firstOrFail();
        $products = Product::where('is_wholeseller',1)->get();
        return view('wholesaler.index', compact('products','wholesaler'));
    }

    public function order(Request $request)
    {
        // return $request->all();
        $totalBox = 0;
        $totalPatti = 0;
        $totalPacket = 0;
        // $finalAmount = 0;
        // Generate a unique order ID
        $orderID = strtoupper('ORD-' . uniqid());
        foreach ($request->products as $product) {
            if (($product['box'] ?? 0) > 0 || ($product['patti'] ?? 0) > 0 || ($product['packet'] ?? 0) > 0) {
                $totalBox += $product['box'] ?? 0;
                $totalPatti += $product['patti'] ?? 0;
                $totalPacket += $product['packet'] ?? 0;
            }
        }
        $order = new Order();
        $order->order_id = $orderID;
        $order->wholesaler_id = $request->wholesaler_id;
        $order->total_box = $totalBox;
        $order->total_patti = $totalPatti;
        $order->total_packet = $totalPacket;
        $order->products = $request->totalProducts;
        $order->final_amount = $request->totalAmount;
        $order->order_type = 'wholesaler';
        $order->save();
        foreach ($request->products as $product) {
            if (($product['box'] ?? 0) > 0 || ($product['patti'] ?? 0) > 0 || ($product['packet'] ?? 0) > 0) {
                $orderDetail = new OrderDetial();
                $findProduct = Product::find($product['product_id']);
                $orderDetail->order_id = $order->id;
                $orderDetail->product_id = $product['product_id'];
                $orderDetail->box = $product['box'] ?? 0;
                $orderDetail->patti = $product['patti'] ?? 0;
                $orderDetail->packet = $product['packet'] ?? 0;
                $orderDetail->price = $findProduct->selling_price ?? 0;
                $orderDetail->total_qty = $product['productTotalQty'] ?? 0;
                $orderDetail->total_cost = $product['productTotalCost'] ?? 0;
                $orderDetail->save();
            }
        }
        return redirect()->route('Wholesaler.confirmOrder',$order->order_id);
    }

    public function confirmOrder($order_id)
    {
        $order = Order::where('order_id',$order_id)->firstOrFail();
        return view('wholesaler.success',compact('order'));
    }
}
