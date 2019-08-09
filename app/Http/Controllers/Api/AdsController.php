<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Ad;

class AdsController extends Controller
{
    public function addAds(Request $request)
    {
        if(!isActive()){
            return response()->json(['failed'=>'your pharmacy not active right now '],404);
        }
        $validateAd = $request->validate([
            'content' => 'required',
            'image' => 'nullable',
        ]);

        $pharmacy = auth()->user()->pharmacy;
        $ad = $pharmacy->ads()->create($validateAd);
        return response()->json($ad, 201);
    }

    public function AcceptAds(Request $request)
    {
        $now = date('Y-m-d');
        $validateData = $request->validate([
            'adId' => 'required|integer',
            'expireDate' => 'date_format:"Y-m-d"|required|after:' . $now,
        ]);

        $ads = Ad::find($validateData['adId']);
        $ads->accepted = 1;
        $ads->expireDate = $validateData['expireDate'];
        $ads->save();
        return response()->json(['success' => 'accepted'], 200);
    }

    public function RejectAds(Request $request)
    {
        $validateData = $request->validate([
            'adId' => 'required|integer',
        ]);

        $ads = Ad::find($validateData['adId']);

        $ads->delete();

        return response()->json(['success' => 'rejected'], 200);
    }

    public function getPendingAds()
    {
        $now = date('Y-m-d');
        $ads = Ad::where('accepted', 0)->where('expireDate', '>=', $now)->get();
        return response()->json($ads, 200);
    }

    public function getAcceptedAds()
    {
        $now = date('Y-m-d');
        $ads = Ad::where('accepted', 1)->where('expireDate', '>=', $now)->get();
        return response()->json($ads, 200);
    }
}
