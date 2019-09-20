<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use function GuzzleHttp\json_decode;
use function GuzzleHttp\json_encode;
use App\Order;
use App\Pharmacy;

class OrderController extends Controller
{
    // upload images
    public function upload(Request $request)
    {
        $request->validate([
            'image'=>'required|max:4096',
            'image.*' => 'mimes:jpeg,png,jpg,gif,svg'
        ]);
        $insert = array();
        if ($image = $request->file('image')) {
            foreach ($image as $files) {

            $destinationPath = 'images/'; // upload path
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
            'address'=>'required',
            'pharmacy_id'=>'required',
            'phone' => 'required',
        ]);

        // $orderData['image'] = json_decode($orderData['image']);
        // $order = new Order;
        $orderData['order_type'] = 'medication';

        $user = auth()->user();
        // return $user->product()->get();

        $orderData['name'] = json_encode($user->product()->where('type','med')->get(['data'])) ;
        $orderData['image'] = json_encode($user->product()->where('type','med')->get(['image']));

        $orderData['cosmetic'] = json_encode($user->product()->where('type','cosmetic')
        ->get(['product_id']));
        $orderData['package'] = json_encode($user->product()->where('type','package')
        ->get(['product_id']));

        $orderData['price'] = $user->product->sum('price');
        // return $orderData;
        $order =  $user->order()->create($orderData);

       if($orderData['cosmetic'] != null)
       {
        $cosmetic = json_decode($orderData['cosmetic']);
        // return $cosmetic;
        $cosmetic_ids = array();
        foreach($cosmetic as $c){
            $cosmetic_ids [] = $c->product_id;
        }
        //    $ = json_decode($orderData['cosmetic']);

           $order->cosmetics()->attach($cosmetic_ids);
       }

       if($orderData['package'] != null)
       {
        $package = json_decode($orderData['package']);

        $package_ids = array();
        foreach($package as $p){
            $package_ids [] = $p->product_id;
        }

           $order->packages()->attach($package_ids);
       }
       $fullData = null;
        if(!$order->cosmetics != null){
            if($order->packages !=null){
             $fullData = Order::with('cosmetics')->with('packages')->with('user')->find($order->id);
            }
        }
        else if(!$order->packages != null)
        {
            $fullData = Order::with('packages')->with('user')->find($order->id);
        }
        else {
            $fullData = $order;
            $fullData->user;
        }
        // sendNotification($orderData);
        $fullData->name = json_decode($fullData->name);
        $fullData->image = json_decode($fullData->image);
        // return ($fullData);
        pushOrderNotification($fullData);
        // return response()->json([$order->packages,$order->cosmetics],200);
        return response()->json($fullData,201);
    }

    public function getOrderForVendor(){
        if(!isActive()){
            return response()->json(['failed'=>'your pharmacy not active right now '],404);
        }
        $pharmacy = auth()->user()->pharmacy;
        if(!$pharmacy)
        return response()->json(['error'=>'this user not related to pharmacy']);
        $orders = $pharmacy->order()
        ->with('cosmetics')
        ->with('packages')
        ->with('user')
        ->paginate(20);
        for($i = 0 ; $i<count($orders);$i++){
          $orders[$i]->name = json_decode($orders[$i]->name);
          $orders[$i]->image = json_decode($orders[$i]->image);
        }

          // foreach ($orders as $order) {
          //       for($j = 0 ; $j < count($order->name) ; $j++){
          //           $order->name[$j]->data = json_decode($order->name[$j]->data);
          //           $order->image[$j]->image = json_decode($order->image[$j]->image);
          //     }
          // }


        return response()->json($orders);
    }



    public function getOrderForUser(){
        $orders = auth()->user()->order;
        for($i = 0 ; $i<count($orders);$i++){
          $orders[$i]->name = json_decode($orders[$i]->name);
          $orders[$i]->image = json_decode($orders[$i]->image);
        }
        return response()->json($orders);
    }

    public function getAllOrdersForAdmin(){
        $orders = Order::with('pharmacy')
        ->with('packages')
        ->with('cosmetics')
        ->with('user')
        ->paginate(20);
        // $orders->name = json_decode($orders->name);
        for($i = 0 ; $i<count($orders);$i++){
          $orders[$i]->name = json_decode($orders[$i]->name);
          $orders[$i]->image = json_decode($orders[$i]->image);
        }
        return response()->json($orders,200);
    }

    // when vendor open website , get the pending orders that related to him!
    public function getPendingOrder(){
        if(!auth()->user()->active) {
            return response()->json(['failed'=>'your pharmacy not active right now '],404);
        }
        $pharmacy_id = auth()->user()->pharmacy->id;
        $orders = Order::where('pharmacy_id',$pharmacy_id)
        ->with('cosmetics')
        ->with('packages')
        ->with('user')
        ->where('status','3')->get();
        for($i = 0 ; $i<count($orders);$i++){
          $orders[$i]->name = json_decode($orders[$i]->name);
          $orders[$i]->image = json_decode($orders[$i]->image);
        }
        return response()->json($orders,200);
    }

    public function getSuspendingOrder(){
        if(!auth()->user()->active) {
            return response()->json(['failed'=>'your pharmacy not active right now '],404);
        }
        $pharmacy_id = auth()->user()->pharmacy->id;
        $orders = Order::where('pharmacy_id',$pharmacy_id)
        ->with('cosmetics')
        ->with('packages')
        ->with('user')
        ->where('status','4')
        ->paginate(20);
        for($i = 0 ; $i<count($orders);$i++){
          $orders[$i]->name = json_decode($orders[$i]->name);
          $orders[$i]->image = json_decode($orders[$i]->image);
        }
        return response()->json($orders,200);
    }

        // when vendor open website , get the accepted orders that related to him!
        public function getAcceptedOrder(){
            if(!auth()->user()->active) {
                return response()->json(['failed'=>'your pharmacy not active right now '],404);
            }
            $pharmacy_id = auth()->user()->pharmacy->id;
            $orders = Order::where('pharmacy_id',$pharmacy_id)
            ->with('cosmetics')
            ->with('packages')
            ->with('user')
            ->where('status','1')
            ->paginate(20);
            for($i = 0 ; $i<count($orders);$i++){
              $orders[$i]->name = json_decode($orders[$i]->name);
              $orders[$i]->image = json_decode($orders[$i]->image);
            }
            return response()->json($orders,200);
        }

            // when vendor open website , get the rejected orders that related to him!
    public function getRejectedOrder(){
        if(!auth()->user()->active) {
            return response()->json(['failed'=>'your pharmacy not active right now '],404);
        }
        $pharmacy_id = auth()->user()->pharmacy->id;
        $orders = Order::where('pharmacy_id',$pharmacy_id)
        ->with('cosmetics')
        ->with('packages')
        ->with('user')
        ->where('status','2')
        ->paginate(20);
        for($i = 0 ; $i<count($orders);$i++){
          $orders[$i]->name = json_decode($orders[$i]->name);
          $orders[$i]->image = json_decode($orders[$i]->image);
        }
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
            'alarm' => 'required',
            'time' => 'required'
        ]);

        $order = Order::find($validatedData['order_id']);
        $order->status = '1';
        $order->price = $validatedData['price'];
        $order->save();
        $pharmacy = $order->pharmacy;
        $details = array(
          'pharmacyName' => $pharmacy->name,
          'price' => $order->price,
          'tips' => $validatedData['alarm'],
          'time' => $validatedData['time']
        );
        $token = $order->user->token;
        // return $token;
        pushToMobile('Your order has been accepted','order in his way',$details,$token);
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

        $order->status = '2';

        $order->save();

        // search for new pharmacy
        return response()->json(['success'=>'order rejected successfully'],200);
    }


    public function getOrderByUserId($id){
        $orders = Order::where('user_id',$id)
        ->with('packages')
        ->with('cosmetics')
        ->with('user')
        ->paginate(20);
        return response()->json($orders);
    }

    //by Pharmacy ID
    public function getOrderPharmacy($id){

        $orders = Order::where('pharmacy_id',$id)
        ->with('packages')
        ->with('cosmetics')
        ->with('user')
        ->paginate(20);
        for($i = 0 ; $i<count($orders);$i++){
          $orders[$i]->name = json_decode($orders[$i]->name);
          $orders[$i]->image = json_decode($orders[$i]->image);
        }
        return response()->json($orders);
    }

     //by User ID
        public function getOrderPharmacyByUserId($id){
                $pharmacy = Pharmacy::where('user_id',$id)->get();
                // return $pharmacy[0]->id;
                $orders = Order::where('pharmacy_id', $pharmacy[0]->id)
                 ->with('packages')
                ->with('cosmetics')
                ->with('user')
                ->paginate(20);
                for($i = 0 ; $i<count($orders);$i++){
                  $orders[$i]->name = json_decode($orders[$i]->name);
                  $orders[$i]->image = json_decode($orders[$i]->image);
                }
                return response()->json($orders);
        }



        /* ADMIN SECTION */

        public function getSuspendingOrderAdmin(){
            $orders = Order::with('cosmetics')
            ->with('packages')
            ->with('user')
            ->where('status','4')
            ->paginate(20);
            for($i = 0 ; $i<count($orders);$i++){
              $orders[$i]->name = json_decode($orders[$i]->name);
              $orders[$i]->image = json_decode($orders[$i]->image);
            }
            return response()->json($orders,200);
        }

            // when vendor open website , get the accepted orders that related to him!
            public function getAcceptedOrderAdmin(){
                $orders = Order::with('cosmetics')
                ->with('packages')
                ->with('user')
                ->where('status','1')
                ->paginate(20);
                for($i = 0 ; $i<count($orders);$i++){
                  $orders[$i]->name = json_decode($orders[$i]->name);
                  $orders[$i]->image = json_decode($orders[$i]->image);
                }
                return response()->json($orders,200);
            }

                // when vendor open website , get the rejected orders that related to him!
        public function getRejectedOrderAdmin(){
            $orders = Order::with('cosmetics')
            ->with('packages')
            ->with('user')
            ->where('status','2')
            ->paginate(20);
            for($i = 0 ; $i<count($orders);$i++){
              $orders[$i]->name = json_decode($orders[$i]->name);
              $orders[$i]->image = json_decode($orders[$i]->image);
            }
            return response()->json($orders,200);
        }
}
