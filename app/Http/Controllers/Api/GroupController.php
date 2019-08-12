<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Category;
use App\Group;

class GroupController extends Controller
{
    public function addGroup(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required',
            'category_id' => 'required'
        ]);

        $category = Category::find($validatedData['category_id']) ;
        if($category){
            $group = $category->groups()->create($validatedData);
            return response()->json($group,201);
        }
        return response()->json(['message' => 'category not found'],404);

    }

    public function getGroup($id){
        $groups = Category::find($id)->groups;
        return response()->json($groups,200);
    }
}
