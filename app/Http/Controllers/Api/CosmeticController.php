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

    public function getCosmetics(){
        $cosmetics = Cosmetic::with('group')->paginate(20);

        return response()->json($cosmetics,200);
    }

    public function getCosmeticsGroup(){
        $cosmetics = Cosmetic::with('group')->get();

        return response()->json($cosmetics,200);
    }

    public function deleteCosmetic(Request $request){
        $validatedData = $request->validate([
            'id' => 'required'
        ]);

        $cosmetic = Cosmetic::find($validatedData['id']);
        $cosmetic->delete();

        return response()->json(['status'=>true],200);
    }
}
