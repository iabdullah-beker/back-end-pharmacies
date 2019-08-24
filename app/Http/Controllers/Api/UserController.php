<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Pharmacy;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function getNormalUsers(){
        $users = User::where('role','user')->get();

        return response()->json($users);
    }

    public function searchUserByName($name){
        $user = User::where('name','LIKE','%'.$name.'%')->where('role','user')->get();

        return response()->json($user);
    }

    public function searchUserByDate($start,$end){
        $user = User::whereBetween('created_at',array($start, $end))->where('role','user')->get();

        return response()->json($user);
    }


    public function getVendors(){
        $vendors = User::where('role','vendor')->with('pharmacy')->get();

        return response()->json($vendors);
    }

    public function searchVendorByName($name){
        $vendor = User::where('name','LIKE','%'.$name.'%')->where('role','vendor')->get();

        return response()->json($vendor);
    }

    public function searchUserById($id){

        $user = User::where('role','user')->find($id);
        $orders = $user->order()->paginate(20);
        return response()->json(['user'=>$user,'orders'=>$orders]);

    }

    public function searchVendorById($id){
        $user = User::where('role','vendor')->find($id);
        $pharmacy = Pharmacy::where('user_id',$user->id)->withCount('order')->get();
        return response()->json(['user'=>$user,'pharmacy'=>$pharmacy]);

    }

    public function updateData(Request $request){
        $validatedData = $request->validate([
            'email' => 'required|unique:users,email,'.auth()->user()->id.'|max:100|email',
            'name' => 'required',
            'address' => 'required',
            'phone' => 'numeric|min:11|required',
            'disease' => 'required',
            'dob' => 'date_format:"Y-m-d"|required',
            'gender' => 'required',
            'photo' => 'required'
        ]);

        $user = auth()->user();
        $user->email = $validatedData['email'] ;
        $user->name = $validatedData['name'] ;
        $user->address = $validatedData['address'] ;
        $user->phone = $validatedData['phone'] ;
        $user->disease = $validatedData['disease'] ;
        $user->dob = $validatedData['dob'] ;
        $user->gender = $validatedData['gender'] ;
        $user->photo = $validatedData['photo'] ;
        $user->save();

        return response()->json(['status'=>true,'data'=>$user]);

    }

    public function updatePharmacyData(Request $request){
        $validatedData = $request->validate([
            'name' => 'required',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'address' => 'required',
            'phone' => 'required|numeric',
        ]);

        $pharmacy = auth()->user()->pharmacy;
        $pharmacy->name = $validatedData['name'] ;
        $pharmacy->lat = $validatedData['lat'] ;
        $pharmacy->lng = $validatedData['lng'] ;
        $pharmacy->address = $validatedData['address'] ;
        $pharmacy->phone = $validatedData['phone'] ;
        $pharmacy->save();
        return response()->json(['status'=>true,'data'=>$pharmacy]);

    }

    public function changePassword(Request $request){
        $validatedData = $request->validate([
            'oldpassword' =>'required',
            'newpassword' => 'required|confirmed',
            'newpassword_confirmation' => 'required',
        ]);
        $current_password = auth()->user()->password;
        if(Hash::check($validatedData['oldpassword'], $current_password))
        {

                $user_id = Auth::user()->id;
                $obj_user = User::find($user_id);
                $obj_user->password = bcrypt($validatedData['newpassword']);
                $obj_user->save();
              return response()->json(['status'=>true]);
             }
            return response()->json(['status'=>false,'error'=>'old password is incorrect']);
    }

    public function numberNewPharmacy(){
        $users = User::where('active',0)->get();
        $count = $users->count();

        return response()->json(['count'=>$count]);
    }

    public function logout() {
        $accessToken = Auth::user()->token();
        DB::table('oauth_refresh_tokens')
            ->where('access_token_id', $accessToken->id)
            ->update([
                'revoked' => true
            ]);

        $accessToken->revoke();
        return response()->json(true, 204);
    }
}
