<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Product;

class ProductController extends Controller
{
    public function addProduct(Request $request){
        $validatedData = $request->validate([
            'data' => 'nullable',
            'image' => 'nullable',
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

    public function updateProduct(Request $request){
        $validatedData = $request->validate([
            'count' => 'required|integer',
            'id' => 'required|integer'
        ]);

            $product = Product::find($validatedData['id']);
            $count = $product->count;
            $product->count = $validatedData['count'];
            $product->price = $product->price / $count;
            $product->price = $product->price * $validatedData['count'] ;
            $product->save();
            return response()->json($product,200);

    }
    public function deleteProduct(Request $request){
        $validatedData = $request->validate([
            'id' => 'required|integer'
        ]);
        $product = Product::find($validatedData['id']);
        $product->delete();
        return response()->json(['status'=>true,'msg'=>'product deleted']);
    }
}
