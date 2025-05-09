<?php

namespace App\Http\Controllers\Wholeseler;

use App\Http\Controllers\Controller;
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
use Illuminate\Support\Facades\Cookie;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class WholeSelerController extends Controller 

{
    public function index()
    {
        // $wholesaler = Wholesaler::where('unique_id',$unique_id)->firstOrFail();
        $products = Product::where('is_wholeseller',1)->get();
        $category = Category::all();

        return view('wholesaler.index', compact('products','category'));
    }


    public function getData(Request $request){
        $categoryName = $request->query('category');

        $products = [];
        
        if($categoryName == 0){
            $products = Product::where('is_wholeseller',1)->get();
        }else{
            $products = Product::where('is_wholeseller',1)->where('category_id',$categoryName)->get();
        }
       

        return response()->json($products);
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
        $customer = [
            'customerName' => $request->customerName,
            'customerPhone' => $request->customerPhone,
            'customerAddress' => $request->customerAddress,
        ];
        $customerJson = json_encode($customer);
        Cookie::queue('customer', $customerJson, 525600);
        // session()->put('customer', $customer);

        $checkWholsaler = Supplier::where('phone_number', $request->customerPhone)->first();
        if ($checkWholsaler) {
            $wholsalerID = $checkWholsaler->id;
        } else {
            $wholsaler = new Supplier();
            $wholsaler->name = $request->customerName;
            $wholsaler->phone_number = $request->customerPhone;
            $wholsaler->address = $request->customerAddress;
            $wholsaler->amount = 0;
            $wholsaler->save();
            $wholsalerID = $wholsaler->id;
        }
        $order = new Order();
        $order->order_id = $orderID;
        $order->wholesaler_id = $wholsalerID;
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
        session()->forget('cart');



        function sendMessage1($id, $message)
        {
            $content = array(
                "en" => $message
            );
            $fields = array(
                'app_id' => "fe4e3716-fcce-449e-8698-dc57dfb4ec41",
                'include_player_ids' => $id,
                'data' => array("foo" => "bar"),
                'headings' => array('en' => 'Order Status'),
                'contents' => $content,
                'android_group' => 'Hayat',
                'android_group_message' => array('en' => '$[notif_count] new messages'),
                'ios_category' => 'Hayat',
                'ios_badgeType' => 'Increase',
                'ios_badgeCount' => 1,
                'small_icon' => "ic_notification"
            );
            $fields = json_encode($fields);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json; charset=utf-8',
                'Authorization: Basic os_v2_app_7zhdofx4zzcj5buy3rl57nhmih5reyrfd2ruxkuyfoogpvf2o2twj67yvnaoomhsjnxnnypwl6qefu6nq4l5g3wl6d7dbbenybgxutq'
            ));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_HEADER, FALSE);
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            $response = curl_exec($ch);
            curl_close($ch);
        }
        $astro = DB::table('users')->where('role','admin')->whereNotNull('device_id')->pluck('device_id')->toArray();
        $message = '🔔 New Online Order Received! Order ID: '.$order->id;
        $response = sendMessage1($astro, $message);
        $return["allresponses"] = $response;
        $return = json_encode($return);

        return redirect()->route('Wholesaler.confirmOrder',$order->order_id);
    }

    public function cart()
    {
        $cart = session()->get('cart', []);
        $productIds = collect($cart)->pluck('product_id');
        // $wholesaler = Wholesaler::where('unique_id',$unique_id)->firstOrFail();
        $products = Product::whereIn('id', $productIds)->where('is_wholeseller',1)->get();
        return view('wholesaler.cart', compact('cart','products'));
    }

    public function addToCart(Request $request)
    {
        // return $request->all();
        $cart = session()->get('cart', []);
        foreach ($request->products as $product) {
            $found = false;
            if (($product['box'] ?? 0) > 0 || ($product['patti'] ?? 0) > 0 || ($product['packet'] ?? 0) > 0)
            {
                foreach ($cart as &$cartItem) {
                    if ($cartItem['product_id'] == $product['product_id']) {
                        $cartItem['box'] += $product['box'] ?? 0;
                        $cartItem['patti'] += $product['patti'] ?? 0;
                        $cartItem['packet'] += $product['packet'] ?? 0;
                        $cartItem['total_qty'] += $product['productTotalQty'];
                        $found = true;
                        break;
                    }
                }
            }
            if (!$found) {
                if (($product['box'] ?? 0) > 0 || ($product['patti'] ?? 0) > 0 || ($product['packet'] ?? 0) > 0)
                {
                    $cart[] = [
                        'product_id' => $product['product_id'],
                        'box' => $product['box'] ?? 0,
                        'patti' => $product['patti'] ?? 0,
                        'packet' => $product['packet'] ?? 0,
                        'total_qty' => $product['productTotalQty'] ?? 0,
                    ];
                }
            }
        }
        session()->put('cart', $cart);
        session()->save();
        return redirect()->route('Wholesaler.Cart')->with('success', 'Cart updated successfully');
        // return response()->json(['message' => 'Cart updated successfully', 'cart' => $cart]);
    }


    public function updateCart(Request $request)
{
    $cart = session()->get('cart', []);

    foreach ($cart as &$cartItem) {
        if ($cartItem['product_id'] == $request->product_id) {
            $cartItem['box'] = $request->box;
            $cartItem['patti'] = $request->patti;
            $cartItem['packet'] = $request->packet;
            $cartItem['total_qty'] = $request->total_qty;
            break;
        }
    }

    session()->put('cart', $cart);
    session()->save();

    return response()->json(['message' => 'Cart updated successfully', 'cart' => $cart]);
}

public function updateCartSession(Request $request)
{
    Session::put('totalProducts', $request->totalProducts);
    Session::put('totalAmount', $request->totalCost);
    Session::put('totalBoxCount', $request->totalBoxCount);
    Session::put('totalPattiCount', $request->totalPattiCount);
    Session::put('totalPacketCount', $request->totalPacketCount);

    return response()->json([
        'success' => true,
        'message' => 'Cart session updated successfully',
        'cartTotals' => [
            'totalProducts' => $request->totalProducts,
            'totalAmount' => $request->totalCost,
            'totalBoxCount' => $request->totalBoxCount,
            'totalPattiCount' => $request->totalPattiCount,
            'totalPacketCount' => $request->totalPacketCount,
        ]
    ]);
}


    public function removeCart(Request $request)
    {
        $cart = session()->get('cart', []);
        $cart = array_filter($cart, function ($item) use ($request) {
            return $item['product_id'] != $request->product_id;
        });
        session()->put('cart', array_values($cart));
        session()->save();
        return response()->json(['success' => 'Product removed from cart successfully']);
    }
    

    public function confirmOrder($order_id)
    {
        $order = Order::where('order_id',$order_id)->firstOrFail();
        return view('wholesaler.success',compact('order'));
    }

    public function invoice()
    {
        return view('pdf.invoice');
    }

    public function purchaseInvoice($id)
    {
        $purchase = PurchaseInvoice::with('purchaseDetails')->where('id',$id)->first();
        foreach ($purchase->purchaseDetails as $key => $purchaseDetail) {
            $purchaseDetail->product;
        }
        return view('pdf.purchase-invoice', compact('purchase'));
    }

    public function partyOrder($id)
    {
        $order = Order::with([
            'supplier',
            'orderProduct.product' => function ($query) {
                $query->select('id', 'name', 'short_name'); 
            }
        ])->where('order_type', 'retailer')->where('id', $id)->first();
        foreach ($order->orderProduct as $key => $product) {
            $returnOrder = ReturnOrderDetail::where('order_id', $id)->where('product_id', $product->product_id)->first();
            $order->orderProduct[$key]->return_order = $returnOrder;
        }
        $collections = BillCollection::where('bill_id', $id)->get();
        foreach ($collections as $key => $collection) {
            $collection->collection_type = json_decode($collection->collection_type);
        }
        $returnOrder = ReturnOrder::with([
            'supplier',
            'returnOrderProducts.product' => function ($query) {
                $query->select('id', 'name', 'short_name'); 
            }
        ])->where('order_id', $id)->first();
        $expanses = Expense::where('bill_id', $id)->get();
        foreach ($expanses as $key => $expanse) {
            $expanse->expanse_type = ExpenseDetail::where('expense_id', $expanse->id)->get();
        }
        return view('pdf.party-order', compact('order', 'collections', 'returnOrder', 'expanses'));
    }

    public function expanseTransaction(Request $request)
    {
        $query = Expense::query();
        if ($request->filter_type == 'today' && $request->has('date')) {
            $query->whereDate('date', Carbon::parse($request->date)->format('Y-m-d'));
        } elseif ($request->filter_type == 'monthly' && $request->has('month')) {
            $query->whereMonth('date', Carbon::parse($request->month)->format('m'))
                        ->whereYear('date', Carbon::parse($request->month)->format('Y'));
        } elseif ($request->filter_type == 'weekly') {
            $date = $request->has('date') ? Carbon::parse($request->date) : Carbon::now();
            $query->whereBetween('date', [
                $date->copy()->startOfWeek(),
                $date->copy()->endOfWeek()
            ]);
        } elseif ($request->filter_type == 'yearly' && $request->has('date')) {
            $query->whereYear('date', Carbon::parse($request->date)->format('Y'));
        } elseif ($request->filter_type == 'custom' && $request->has(['from_date', 'to_date'])) {
            $query->whereBetween('date', [
                Carbon::parse($request->from_date)->startOfDay(),
                Carbon::parse($request->to_date)->endOfDay()
            ]);
        }
        $expenses = $query->orderByDesc('id')->get();
        foreach ($expenses as $key => $transaction) {
            $transaction->expense_detail = ExpenseDetail::where('expense_id', $transaction->id)->get();
        }
        // return $expenses;
        return view('pdf.expanse-transaction', compact('expenses'));
    }

    public function expanseTransactionSummery(Request $request)
    {
        $query = Expense::query();
        if ($request->filter_type == 'today' && $request->has('date')) {
            $query->whereDate('date', Carbon::parse($request->date)->format('Y-m-d'));
        } elseif ($request->filter_type == 'monthly' && $request->has('month')) {
            $query->whereMonth('date', Carbon::parse($request->month)->format('m'))
                        ->whereYear('date', Carbon::parse($request->month)->format('Y'));
        } elseif ($request->filter_type == 'weekly') {
            $date = $request->has('date') ? Carbon::parse($request->date) : Carbon::now();
            $query->whereBetween('date', [
                $date->copy()->startOfWeek(),
                $date->copy()->endOfWeek()
            ]);
        } elseif ($request->filter_type == 'yearly' && $request->has('date')) {
            $query->whereYear('date', Carbon::parse($request->date)->format('Y'));
        } elseif ($request->filter_type == 'custom' && $request->has(['from_date', 'to_date'])) {
            $query->whereBetween('date', [
                Carbon::parse($request->from_date)->startOfDay(),
                Carbon::parse($request->to_date)->endOfDay()
            ]);
        }
        $expenses = $query->orderBy('date', 'asc')->get();
        foreach ($expenses as $key => $transaction) {
            $transaction->expense_detail = ExpenseDetail::where('expense_id', $transaction->id)->get();
        }
        return view('pdf.expense-transaction-summery', compact('expenses'));
    }

    public function printA4(Request $request)
    {
        $order = Order::where('order_id',$request->order_id)->firstOrFail();
        return view('pdf.print-a4',compact('order'));
    }

}
