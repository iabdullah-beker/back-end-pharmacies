<?php

use Illuminate\Http\Request;

header('Access-Control-Allow-Origin: *');
//Access-Control-Allow-Origin: *
header('Access-Control-Allow-Methods:  POST, GET, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers:  Content-Type, X-Auth-Token, Origin, Authorization');
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('register', 'Api\AuthController@register');
Route::post('login', 'Api\AuthController@login');
Route::post('/addpharmacy' , 'Api\PharmacyController@addPharmacy');
Route::post('/checkemail','Api\PharmacyController@checkEmail');
Route::post('/checkphone','Api\PharmacyController@checkPhone');
Route::get('post/{id}', function($id){
   $order= App\Order::find($id);
   $order->status = 'rejected';
   $order->save();
   return response()->json($order);
});
// All Routes
Route::group(['middleware' => ['auth:api']], function () {
    Route::get('/test', function () {
        return App\Order::with('pharmacy')->get()->where('user_id', auth()->user()->id);
    });
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});


//User and Admin Routes
Route::group(['middleware' =>['auth:api', 'scope:user,moderator,admin']] ,function(){
    Route::get('/getcosmetic', 'Api\CosmeticController@getCosmetic');
    Route::get('/acceptedads' , 'Api\AdsController@getAcceptedAds');
    Route::get('/getpackages' , 'Api\PackageController@getPackages');
    Route::get('/getpackagedata/{id}' , 'Api\PackageController@getPackageData');
});

// User Routes
Route::group(['middleware' => ['auth:api', 'scope:user']], function () {
    Route::post('/upload', 'Api\OrderController@upload');
    Route::post('/addorder', 'Api\OrderController@addOrder');
    Route::get('/getorderuser', 'Api\OrderController@getOrderForUser');
    Route::post('/addcomplaint', 'Api\ComplaintController@addComplaint');
    Route::post('/addrate', 'Api\RateController@addRate');
    Route::post('/nearest' , 'Api\PharmacyController@findNearestPharmacy');


});


// Vendor Routes
Route::group(['middleware' => ['auth:api', 'scope:vendor']], function () {
    // $user = auth()->user();
    // if(!$user->active) {
    //     return response()->json(['failed'=>'your pharmacy not active right now '],404);
    // }
    Route::get('/getordervendor', 'Api\OrderController@getOrderForVendor');
    Route::get('/getpendingorder', 'Api\OrderController@getPendingOrder');
    Route::post('/acceptorder', 'Api\OrderController@onAcceptOrder');
    Route::post('/rejectorder', 'Api\OrderController@onRejectOrder');
    Route::post('/pharmacynotavailible', 'Api\PharmacyController@pharmacyNotAvailible');
    Route::post('/pharmacyavailible', 'Api\PharmacyController@pharmacyAvailible');
    Route::post('/addalarm' , 'Api\AlarmController@addAlarm');
    Route::get('/getorderwithratevendor', 'Api\RateController@getOrdersWithRateForVendor');
    Route::post('/addads' , 'Api\AdsController@addAds');

});

// Moderator Routes
Route::group(['middleware' => ['auth:api', 'scope:moderator,admin']], function () {
    Route::get('/getorderadmin', 'Api\OrderController@getAllOrdersForAdmin');
    Route::get('/getpharmacy' , 'Api\PharmacyController@getPharmacy');
    Route::get('/deletepharmacy/{id}' , 'Api\PharmacyController@deletePharmacy');
    Route::post('/addtip' , 'Api\TipsController@addTip');
    Route::post('/addpromo' , 'Api\PromoController@addPromo');
    Route::get('/deactivepromo/{id}' , 'Api\PromoController@DeactivePromo');
    Route::get('/getallcomplaints', 'Api\ComplaintController@getAllComplaints');
    Route::get('/getorderwithrate', 'Api\RateController@getOrdersWithRate');
    Route::post('/upload', 'Api\OrderController@upload');
    Route::post('/addcosmetic', 'Api\CosmeticController@addCosmetic');
    Route::post('/acceptads' , 'Api\AdsController@AcceptAds');
    Route::post('/rejectads' , 'Api\AdsController@RejectAds');
    Route::get('/pendingads' , 'Api\AdsController@getPendingAds');
    Route::post('/addpackage' , 'Api\PackageController@addPackage');


});


// Admin Routes
Route::group(['middleware' => ['auth:api', 'scope:admin']], function () {

});

// Route::middleware(['auth:api',['scope:user']])->get('/user', function (Request $request) {
//     return $request->user();
// });
