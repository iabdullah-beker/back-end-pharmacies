<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Cosmetic;
use App\Group;

class CosmeticController extends Controller
{





    public function addCosmetic(Request $request)
    {
        $validatedCosmetic = $request->validate([
            'name' => 'required|unique:cosmetics',
            'price' => 'required|numeric',
            'image' => 'required',
            'description' => 'required',
            'group_id' => 'required'
        ]);

        $cosmetic = auth()->user()->cosmetic()->create($validatedCosmetic);

        return response()->json($cosmetic,201);
    }

    public function getCosmetic($id) {
        $group = Group::find($id);
        if(!$group)
        return response()->json(['error'=>'not found'],404);
        $cosmetic = $group->cosmetics;
        return response()->json($cosmetic,200);
    }
}