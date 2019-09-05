<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Package;
use function GuzzleHttp\json_decode;
use App\Cosmetic;

class PackageController extends Controller
{
    public function addPackage(Request $request){
        $validatedPackage = $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'image' => 'nullable',
            'pharmacy_ids' => 'required',
            'cosmetic_ids' => 'required'
        ]);

        $cosmetic_ids = json_decode($validatedPackage['cosmetic_ids']);
        $pharmacy_ids = json_decode($validatedPackage['pharmacy_ids']);
        $package = auth()->user()->package()->create($validatedPackage);
        $package->cosmetics()->attach($cosmetic_ids);
        $package->pharmacy()->attach($pharmacy_ids);
        return response()->json($package,201);
    }

    public function getPackages(){

        $packages = Package::all();
        return response()->json($packages,200);
    }

    public function getPackagesWeb(){

        $packages = Package::with('cosmetics')->with('pharmacy')->paginate(20);
        return response()->json($packages,200);
    }

    public function getPackageData($id){
        // $cosmetics = array();

        $package = Package::with('cosmetics')->find($id);
        if($package == null)
            return response()->json(['error' => 'package not found'], 404);
        // $cosmetics = $package->cosmetics;
        // $cosmetic_ids = json_decode($packages['cosmetic_ids']);
        // foreach ($cosmetic_ids as $cosmetic_id) {
        //     $cosmetic = Cosmetic::find($cosmetic_id);
        //     array_push($cosmetics, $cosmetic);
        // }
        return response()->json($package,200);
    }

    public function deletePackage(Request $request){
        $validatedData = $request->validate([
            'id' => 'required'
        ]);

        $package = Package::find($validatedData['id']);
        $package->delete();

        return response()->json(['status'=>true],200);
    }

    public function getPackageById($id){
        $package = Package::find($id);

        $pharmacy = $package->pharmacy()->paginate(20);

        $cosmetics = $package->cosmetics()->paginate(20);

        return response()->json([
            'package'=>$package,
            'pharmacy'=>$pharmacy,
            'cosmetics'=>$cosmetics
        ],200);
    }
}
