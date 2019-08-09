<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Promo;

class PromoController extends Controller
{
    public function addPromo(Request $request){
        $now = date('Y-m-d');
        $validatedData = $request->validate([
            'expireDate' =>'date_format:"Y-m-d"|required|after:'.$now,
            'code' => 'required|unique:promos'
        ]);

        // $validatedData['code'] = crypto(10);

        $validatedData['status'] = 'active';
            
        $promo = auth()->user()->promo()->create($validatedData);

        return response()->json($promo,201);
    }

    public function DeactivePromo($id){
        $promo = Promo::find($id);
        
        $promo->status = 'deactivated';

        $promo->save();

        return response()->json(['Card Deactivated Successfully'],200);
    }
}
