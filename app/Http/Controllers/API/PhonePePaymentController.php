<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Model\Order;
use App\Model\OrderTransaction;
use Illuminate\Http\Request;
use Razorpay\Api\Api;
use App\CPU\CartManager;
use App\CPU\Helpers;
use App\CPU\OrderManager;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Mail;
use App\Model\Admin;
use App\Model\Seller;

class PhonePePaymentController extends Controller
{
    public function makePhonePePayment(Request $request)
    {
      // dd($user = auth('customer')->user());
      $comman_id=rand(00000001, 99999999);
                
      $unique_id = OrderManager::gen_unique_id();
      $order_ids = [];
      foreach (CartManager::get_cart_group_ids() as $group_id) {
          $data = [
              'payment_method' => 'phone_pay',
              'order_status' => 'failed',
              'payment_status' => 'unpaid',
              'comman_id'=>$comman_id,
              'transaction_ref' => null,
              'order_group_id' => $unique_id,
              'cart_group_id' => $group_id
          ];
          $order_id = OrderManager::generate_order($data);
          array_push($order_ids, $order_id);
      }
        $order = Order::find($order_id);
        $request->session()->put('order_id', $order_id);
        $finalAmount = $order->order_amount * 100;
        // $finalAmount = 1 * 100;
        $data = array (
            'merchantId' => 'M224GN4QQ15GL',
            'merchantTransactionId' => uniqid(),
            'merchantUserId' => 'MUID123',
            'amount' => $finalAmount,
            'redirectUrl' => route('phonepe.payment.callback'),
            'redirectMode' => 'POST',
            'callbackUrl' => route('phonepe.payment.callback'),
            'mobileNumber' => '9999999999',
            'paymentInstrument' => 
            array (
            'type' => 'PAY_PAGE',
            ),
        );

        $encode = base64_encode(json_encode($data));

        $saltKey = 'bf5b6808-c704-46b3-82a1-0c09a8e168d9';
        $saltIndex = 1;

        $string = $encode.'/pg/v1/pay'.$saltKey;
        $sha256 = hash('sha256',$string);

        $finalXHeader = $sha256.'###'.$saltIndex;
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://api.phonepe.com/apis/hermes/pg/v1/pay',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => false,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS => json_encode(['request' => $encode]),
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'X-VERIFY: '.$finalXHeader
          ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        $rData = json_decode($response);
        return redirect()->to($rData->data->instrumentResponse->redirectInfo->url);

    }

    public function phonePeCallback(Request $request)
    {
        $input = $request->all();
        // dd($input['transactionId']);
        $saltKey = 'bf5b6808-c704-46b3-82a1-0c09a8e168d9';
        $saltIndex = 1;
        $finalXHeader = hash('sha256','/pg/v1/status/'.$input['merchantId'].'/'.$input['transactionId'].$saltKey).'###'.$saltIndex;
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://api.phonepe.com/apis/hermes/pg/v1/status/'.$input['merchantId'].'/'.$input['transactionId'],
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => false,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'accept: application/json',
            'X-VERIFY: '.$finalXHeader,
            'X-MERCHANT-ID: '.$input['merchantId']
          ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        
        $responseData = json_decode($response);
        // dd($responseData);
        $user = auth('customer')->user();
        if ($responseData->success == true) {

          
          // return 'payment successfully';
          $order_id = $request->session()->get('order_id');
          $data = Order::find($order_id);
          $data->order_status = 'confirmed';
          $data->payment_status = 'paid';
          $data->transaction_ref = $responseData->data->transactionId;
          $data->save();

          // $user = Helpers::get_customer($req);
          Mail::to($user->email)->send(new \App\Mail\OrderPlaced($order_id));
          Mail::to('orders@blazescanner.com')->send(new \App\Mail\OrderPlaced($order_id));
          if ($data['seller_is'] == 'seller') {
              $seller = Seller::where(['id' => $seller_data->seller_id])->first();
          } else {
              $seller = Admin::where(['admin_role_id' => 1])->first();
          }
          Mail::to($seller->email)->send(new \App\Mail\OrderReceivedNotifySeller($order_id));
          Mail::to('orders@blazescanner.com')->send(new \App\Mail\OrderReceivedNotifySeller($order_id));

          CartManager::cart_clean();
          return view('web-views.checkout-complete');
        } else {
          // return redirect()->route('home')->with('error','Payment process failed');
          Toastr::error('Payment process failed');
          return redirect()->route('home');
        }
        // return redirect()->route('order.successfully',$data->unique_id);
        // flash(translate('Your order has been placed successfully. Please submit payment information from purchase history'))->success();
        // return redirect()->route('order_confirmed');
        // return redirect()->route('user.dashboard');
    }
}
