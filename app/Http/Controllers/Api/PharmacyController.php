<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Pharmacy;
use App\Order;

class PharmacyController extends Controller
{

    public function checkEmail(Request $request){
        $user = User::where('email',$request->email)->first();
        if($user)
             return response()->json(['status'=>false],200);
        return response()->json(['status'=>true],200);
    }

    public function checkPhone(Request $request){
        $user = User::where('phone',$request->phone)->first();
        if($user)
             return response()->json(['status'=>false],200);
        return response()->json(['status'=>true],200);
    }

    public function addPharmacy(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'address' => 'required',
            'phone' => 'required|numeric',
            'user_name' => 'required',
            'user_address' => 'required',
            'user_phone' => 'required',
            'user_password' => 'required',
            'user_email' => 'required',
        ]);
        $Data = User::where('email',$validatedData['user_email'])->first();
        if($Data)
            return response()->json(['status'=>false,'message'=>'account already use'],200);
        $user = new User;
        $user->name = $validatedData['user_name'];
        $user->email = $validatedData['user_email'];
        $user->address = $validatedData['user_address'];
        $user->phone = $validatedData['user_phone'];
        $user->password = bcrypt($validatedData['user_password']);
        $user->role = 'vendor';
        $user->save();
        $token = $user->createToken('My Token', ['vendor'])->accessToken;
        $pharmacy =  $user->pharmacy()->create($validatedData);
            return response()->json(['status'=>true,'token'=>$token], 201);

        // if($user == null)
        //     return response()->json(['error'=>'user not found'],404);
        // if (isVendor($user)) {
        //     if ($user->pharmacy == null) {

        //     }
        //     return response()->json(['error' => 'this user already related to pharmacy'], 403);
        // }
        // return response()->json(['error' => 'user is not a vendor'], 403);
    }

    public function getPharmacy(){
        $pharmacy = Pharmacy::whereHas("user", function($q){
            $q->where("active","=",1);
         })->with('user')->withCount('order')->get();
        return response()->json($pharmacy,200);
    }

    public function getPendingPharmacy(){
        $pharmacy = Pharmacy::whereHas("user", function($q){
            $q->where("active","=",0);
         })->with('user')->get();
        return response()->json($pharmacy,200);
    }

    public function findNearestPharmacy(Request $request){
        $validatedData = $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);


        $lat = $validatedData['lat'] ;
        $lng = $validatedData['lng'];
        $distance = 5;

        $data = Pharmacy::getByDistance($lat,$lng,$distance);


        if(empty($data)) {
            return response()->json(['error','there are no pharmacies nearest of you'],200);
          }

          $ids = [];

          //Extract the id's
          foreach($data as $q)
          {
              if(Pharmacy::find($q->id)['availability'])
                 array_push($ids, $q->id);
          }

        //   $test = Pharmacy::select('name')->get()->toArray;
        return response()->json($ids[0],200);
    }

    public function deletePharmacy($id){

        $pharmacy = Pharmacy::find($id);
        $pharmacy->delete();

        return response()->json(['Done'],200);
    }

     // on Order TimeOut or pharmacy not available
     public function pharmacyNotAvailible(Request $request){
        if(!isActive()){
            return response()->json(['failed'=>'your pharmacy not active right now '],404);
        }
        $validatedData = $request->validate([
            'order_id' => 'required|integer'
        ]);

        $order = Order::find($validatedData['order_id']);
        if($order->status != 'pending')
            return;
        $pharmacy = $order->pharmacy;

        $pharmacy->availability = 0;

        $pharmacy->save();

        //search for new pharmacy

        return response()->json(['success','pharmacy is not availible right now '],200);
    }

    public function pharmacyAvailible(Request $request){
        if(!isActive()){
            return response()->json(['failed'=>'your pharmacy not active right now '],404);
        }
        $pharmacy = auth()->user()->pharmacy;

        $pharmacy->availability = 1;

        $pharmacy->save();

        return response()->json(['success','pharmacy is availible right now '],200);
    }
}
