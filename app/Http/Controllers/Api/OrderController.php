<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use function GuzzleHttp\json_decode;
use function GuzzleHttp\json_encode;
use App\Order;

class OrderController extends Controller
{
    // upload images
    public function upload(Request $request)
    {
        $request->validate([
            'image'=>'required'
        ]);
        $insert = array();
        if ($image = $request->file('image')) {
            foreach ($image as $files) {

            $destinationPath = 'orders/images/'; // upload path
            // $profileImage =  md5_file($files->getRealPath())->getClientOriginalExtension();

            $ImageName =  auth()->user()->name . md5_file($files->getRealPath()) . "." . $files->getClientOriginalExtension();
            $files->move($destinationPath, $ImageName);
             $insert[] = $ImageName;
            }
        }
        // $check = Image::insert($insert);
        return response()->json(['images'=> $insert],200);
    }
    // to add new order
    public function addOrder(Request $request){
       $orderData = $request->validate([
            'order_type'=>'required',
            'address'=>'required',
            'image'=>'nullable',
            'name'=>'nullable',
            'pharmacy_id'=>'required',
            'phone' => 'required',
            'cosmetic' =>'nullable',
            'package' =>'nullable',
        ]);
        // $orderData['image'] = json_decode($orderData['image']);
        // $order = new Order;

       $order =  auth()->user()->order()->create($orderData);

       if($request['cosmetic']!= null)
       {
           $cosmetic_ids = json_decode($orderData['cosmetic']);

           $order->cosmetics()->attach($cosmetic_ids);
       }

       if($request['package'] != null)
       {
           $package_ids = json_decode($orderData['package']);

           $order->packages()->attach($package_ids);
       }
       $fullData = null;
        if($order->cosmetics != null){
            if($order->packages !=null){
             $fullData = Order::with('cosmetics')->with('packages')->find($order->id);
            }
        }
        else if($order->packages != null)
        {
            $fullData = Order::with('packages')->find($order->id);
        }
        else {
            $fullData = $order;
        }
        // sendNotification($orderData);
        pushOrderNotification($fullData);
        // return response()->json([$order->packages,$order->cosmetics],200);
        return response()->json($fullData,201);
    }

    public function getOrderForVendor(){
        if(!isActive()){
            return response()->json(['failed'=>'your pharmacy not active right now '],404);
        }
        $pharmacy = auth()->user()->pharmacy;
        $orders = $pharmacy->order;
        return response()->json(['orders'=>$orders]);
    }

    public function getOrderForUser(){
        $orders = auth()->user()->order;
        return response()->json(['orders'=>$orders]);
    }

    public function getAllOrdersForAdmin(){
        $orders = Order::paginate(10);
        // $orders->name = json_decode($orders->name);
        return response()->json($orders,200);
    }

    // when vendor open website , get the pending orders that related to him!
    public function getPendingOrder(){
        if(!auth()->user()->active) {
            return response()->json(['failed'=>'your pharmacy not active right now '],404);
        }
        $pharmacy_id = auth()->user()->pharmacy->id;
        $orders = Order::where('pharmacy_id',$pharmacy_id)->where('status','pending')->get();

        return response()->json($orders,200);
    }

    //on accept order
    public function onAcceptOrder(Request $request){
        if(!isActive()){
            return response()->json(['failed'=>'your pharmacy not active right now '],404);
        }
        $validatedData = $request->validate([
            'order_id'=>'required|integer',
            'price' => 'required',
        ]);

        $order = Order::find($validatedData['order_id']);
        $order->status = 'accepted';
        $order->price = $validatedData['price'];
        $order->save();
        return response()->json(['success'=>'order accepted successfully'],200);
    }
    // on reject order
    public function onRejectOrder(Request $request){
        if(!isActive()){
            return response()->json(['failed'=>'your pharmacy not active right now '],404);
        }
        $validatedData = $request->validate([
            'order_id' => 'required|integer'
        ]);

        $order = Order::find($validatedData['order_id']);

        $order->status = 'rejected';

        $order->save();

        // search for new pharmacy
        return response()->json(['success'=>'order rejected successfully'],200);
    }



}
