<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Mail\OrderCancel;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use DB;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Review;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use App\CPU\ImageManager;
use Mail;

class UserController extends Controller
{
    public function account_address()
    {
        if(!auth('customer')->check())
        {
            return view('Web.login');
        }
        if(auth('customer')->user()->id != '')
        {
            $addresscount = Address::where('user_id', auth('customer')->user()->id)->whereNull('deleted_at')->count();
            if($addresscount > 0)
            {
                $address = Address::where('user_id', auth('customer')->user()->id)->whereNull('deleted_at')->orderby('id', 'DESC')->get();
                return view('Web.my-address',compact('address'));
            }
            else
            {
                return view('Web.add-address');
            }
        }
        else
        {
            return view('Web.login');
        }
    }

    public function user_account()
    {
        if(!auth('customer')->check())
        {
            return view('Web.login');
        }
        if(auth('customer')->user()->id != '')
        {
            $user = User::where('id', auth('customer')->user()->id)->first();
            return view('Web.my-profile',compact('user'));
        }
        else
        {
            return view('Web.login');
        }
      
    }

    public function order_account()
    {
        if(!auth('customer')->check())
        {
            return view('Web.login');
        }
        if(auth('customer')->user()->id != '')
        {
            $orders = Order::where('user_id', auth('customer')->user()->id)->where('payment_status','!=','failed')->orderby('id','DESC')->get();
            return view('Web.my-order',compact('orders'));
        }
        else
        {
            return view('Web.login');
        }
      
    }

    public function order_accountdetail($id)
    {
        if(!auth('customer')->check())
        {
            return view('Web.login');
        }
        if(auth('customer')->user()->id != '')
        {
            $orders = Order::where('id', $id)->first();
            $userdetailcount = Address::where('id', $orders->address_id)->count();
            $userdetail = Address::where('id', $orders->address_id)->first();
            $order_parts = OrderDetail::where('order_id', $orders->order_id)->orderby('id','DESC')->get();
            return view('Web.order-detail',compact('orders','order_parts','userdetail','userdetailcount'));
        }
        else
        {
            return view('Web.login');
        }
      
    }

    public function order_cancel(Request $request)
    {
        if(auth('customer')->user()->id != '')
        {
            Order::where(['order_id' => $request->order_idd])->update([
                'order_status' => '2',
                'cancel_reason' => $request->cancel_reason
            ]);

            $orders = Order::where('order_id', $request->order_idd)->first();

            OrderDetail::where(['order_id' => $request->order_idd])->update([
                'order_status' => '2'
            ]);
            $orderData = array(
                'order_id' =>  $orders->order_id,
                'shipping_address' =>  $orders->shipping_address ,
                'order_amount' =>  $orders->order_amount ,
                'cancel_reason' =>  $orders->cancel_reason ,
            );
            $Email='oneself.order@gmail.com';
            Mail::to(auth('customer')->user()->email,'One Self')->send(new OrderCancel($orderData, $Email));

            Toastr::success('Success! Order Cancelled Successfully');
            return redirect()->back();
        }
        else
        {
            return view('Web.login');
        }
    }

    public function user_update(Request $request)
    {
        if(!auth('customer')->check())
        {
            return view('Web.login');
        }
        $data=$request->input();

        if(auth('customer')->user()->id != '')
        {
            $id = auth('customer')->user()->id;
            $banner = User::find($id);
            $banner->name = $request->name ? $request->name : '';
            $banner->username = $request->name ? $request->name : '';
            $banner->phone_no = $request->phone_no ? $request->phone_no : '';
            $banner->email = $request->email ? $request->email : '';
            if ($request->has('image')) {
                $banner->image = ImageManager::update('modal/', $banner['image'], 'png', $request->file('image'));
            }
            $banner->save();

            Toastr::success('Profile Update Successfully');
            return redirect()->back();
        }
        else
        {
            return view('Web.login');
        }
      
    }

    public function add_address()
    {
        if(!auth('customer')->check())
        {
            return view('Web.login');
        }
        if(auth('customer')->user()->id != '')
        {
            return view('Web.add-address');
        }
        else
        {
            return view('Web.login');
        }
      
    }

    public function address_store(Request $request)
    {
        if(!auth('customer')->check())
        {
            return view('Web.login');
        }
        $data=$request->input();

        if(!empty($data['id']))
        {
            if(auth('customer')->user()->id != '')
            {
                $user = Address::find($request->id);
                $user -> contact_person_name = $request->contact_person_name;
                $user -> phone = $request->phone;
                $user -> address_type = $request->address_type;
                $user -> address = $request->address;
                $user -> country = $request->country;
                $user -> state = $request->state;
                $user -> city = $request->city;
                $user -> pincode = $request->pincode;
                $user->save();
                Toastr::success('Address Updated Successfully');
                // return redirect()->back();

                return redirect()->route('my-address');
            }
            else
            {
                return view('Web.login');
            }
        }
        else
        {
            if(auth('customer')->user()->id != '')
            {
                $id = auth('customer')->user()->id;
    
                $user = new Address();
                $user -> user_id = auth('customer')->user()->id;
                $user -> contact_person_name = $request->contact_person_name;
                $user -> phone = $request->phone;
                $user -> address_type = $request->address_type;
                $user -> address = $request->address;
                $user -> country = $request->country;
                $user -> state = $request->state;
                $user -> city = $request->city;
                $user -> pincode = $request->pincode;
                $user->save();
                Toastr::success('Address Added Successfully');
                // return redirect()->back();

                return redirect()->route('my-address');
            }
            else
            {
                return view('Web.login');
            }
        }
       
      
    }

    public function edit_address($id)
    {
        if(!auth('customer')->check())
        {
            return view('Web.login');
        }
        if(auth('customer')->user()->id != '')
        {
            $address = Address::where('id', $id)->first();
            return view('Web.add-address',compact('address'));
        }
        else
        {
            return view('Web.login');
        }
    }

    public function address_delete(Request $request)
    {
        if(auth('customer')->user()->id != '')
        {
            $product = Address::find($request->id);
            $product->deleted_at=now();
            $product->save();
            Toastr::success('Success! Deleted Successfully');
            return redirect()->back();
        }
        else
        {
            return view('Web.login');
        }
    }

    public function add_review(Request $request)
    {
        $data=$request->input();

        if(auth('customer')->user()->id != '')
        {
                $id = auth('customer')->user()->id;
    
                $user = new Review();
                $user -> user_id = auth('customer')->user()->id;
                $user -> order_id = $request->order_id;
                $user -> product_id = $request->product_id;
                $user -> rating = $request->rating;
                $user -> review = $request->review;
                $user -> age_range = $request->age_range;
                $user -> skin_concern = $request->skin_concern;
                $user -> skin_type = $request->skin_type;
                $user -> favourite_feature = $request->favourite_feature;
                $user -> created = date('Y-m-d H:i:s');
                $user->save();

                Toastr::success('Review Added Successfully');
                return redirect()->back();
        }
        else
        {
                return view('Web.login');
        }
    }


}