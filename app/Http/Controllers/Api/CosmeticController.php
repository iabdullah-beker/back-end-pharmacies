<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Cosmetic;

class CosmeticController extends Controller
{
    public function addCosmetic(Request $request)
    {
        $validatedCosmetic = $request->validate([
            'name' => 'required|unique:cosmetics',
            'price' => 'required|numeric',
            'image' => 'required',
            'description' => 'required'
        ]);

        $cosmetic = auth()->user()->cosmetic()->create($validatedCosmetic);

        return response()->json($cosmetic,201);
    }

    public function getCosmetic() {
        $cosmetic = Cosmetic::paginate(10);

        return response()->json($cosmetic,200);
    }
}
