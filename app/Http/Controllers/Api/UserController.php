<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Pharmacy;
use App\User;

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
}
