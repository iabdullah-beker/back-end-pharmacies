<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    public function addProduct(Request $request){
        $validatedData = $request->validate([
            'data' => 'required',
            'image' => 'required',
            'count' => 'nullable|integer',
            'price' => 'nullable|numeric',
            'product_id' => 'nullable|integer',
            'type' =>'required|in:med,cosmetic,package'
        ]);

        $user = auth()->user();
        // if($request['price'] && $request['count'])
        $validatedData['price'] = $request['price'] * $request['count'];
        $product = $user->product()->create($validatedData);
        return response()->json(['status'=>true,'data'=>$product],201);
    }

    public function getProduct(){
        $user = auth()->user();

        $products = $user->product()->get();
        $price = $user->product()->sum('price');
        return response()->json(['price'=>$price,'cart'=>$products],200);
    }
}
