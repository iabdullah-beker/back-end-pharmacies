<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Tip;

class TipsController extends Controller
{
    public function addTip(Request $request){
        $validatedData = $request->validate([
            'title' => 'required',
            'body' => 'required',
        ]);

        $tip = auth()->user()->tip()->create($validatedData);

       return response()->json($tip,201);
    }
}
