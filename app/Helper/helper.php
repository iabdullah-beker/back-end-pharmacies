<?php

use App\User;

function getCreat($query)
{

    \Illuminate\Support\Carbon::setLocale(\App::getLocale());
    return $query->created_at->diffForHumans();
}

function isActive()
{
    if (!auth()->user()->active) {
        return false;
    } else {
        return true;
    }
}
function isVendor($user)
{
    if ($user->role == 'vendor')
        return true;

    return false;
}

function isUser($user)
{
    if ($user->role == 'user')
        return true;

    return false;
}
function isAdmin($user)
{
    if ($user->role == 'admin')
        return true;

    return false;
}
function isModerator($user)
{
    if ($user->role == 'moderator')
        return true;

    return false;
}

// search

 function searchByName($name , $role){
    $vendor = User::where('name','LIKE','%'.$name.'%')->where('role',$role)->get();

    return response()->json($vendor);
}

 function searchByDate($start,$end , $role){
    $user = User::whereBetween('created_at',array($start, $end))->where('role',$role)->get();

    return response()->json($user);
}

//  function searchUserByName($name){
//     $user = User::where('name','LIKE','%'.$name.'%')->where('role','user')->get();

//     return response()->json($user);
// }

//  function searchUserByDate($start,$end){
//     $user = User::whereBetween('created_at',array($start, $end))->where('role','user')->get();

//     return response()->json($user);
// }




function pushOrderNotification($order)
{
    $url = "https://fcm.googleapis.com/fcm/send";
    $token = ["c2B5BvYNEUg:APA91bFe6Qy0yaV1eWtWMQ3jkwzF1PnKMR_oScvs0LobTdPnwtMVTN44E1Kh2tj4T0Kmhc-rVYB3rgVyap3X2302KDaSTZVHCDDKzueJlwKgC58FEtJBTaDXaqLtV5xV_oBCzlcGQVZe"];
    $serverKey = 'AAAAkT5ZT9A:APA91bEbd0juuIpm0S_5rYwhLpZhGoPgurzevvOIKB_u9v_X3jgQU6dDyq1oa6cBFNMKP_WepaWdhgvohghY32h87hfkpFN5oQP531-tsS3zR2q-nBgeYj4WKoRPhpH91HhS69NVVqP0';
    // $body = "Hello I am from Your php server";
    $link = env('APP_FRONT_END');
    $notification = array(
        'title' => 'you got new order', 'body' => 'please click on alert to get the order before reject after 3 minutes', "click_action" => $link, "icon" => "http://localhost:3000/favicon.ico", 'sound' => 'default',    'order' => $order
    );
    $arrayToSend = array('registration_ids' => $token, 'notification' => $notification, 'priority' => 'high');
    $json = json_encode($arrayToSend);
    $headers = array();
    $headers[] = 'Content-Type: application/json';
    $headers[] = 'Authorization: key=' . $serverKey;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //prevent return

    //Send the request
    curl_exec($ch);
    // //Close request
    // if ($response === FALSE) {
    //     die('FCM Send Error: ' . curl_error($ch));
    // }
    curl_close($ch);
}

function pushTips($tip)
{
    $url = "https://fcm.googleapis.com/fcm/send";
    $token = ["c2B5BvYNEUg:APA91bFe6Qy0yaV1eWtWMQ3jkwzF1PnKMR_oScvs0LobTdPnwtMVTN44E1Kh2tj4T0Kmhc-rVYB3rgVyap3X2302KDaSTZVHCDDKzueJlwKgC58FEtJBTaDXaqLtV5xV_oBCzlcGQVZe"];
    $serverKey = 'AAAAkT5ZT9A:APA91bEbd0juuIpm0S_5rYwhLpZhGoPgurzevvOIKB_u9v_X3jgQU6dDyq1oa6cBFNMKP_WepaWdhgvohghY32h87hfkpFN5oQP531-tsS3zR2q-nBgeYj4WKoRPhpH91HhS69NVVqP0';
    // $body = "Hello I am from Your php server";
    $link = env('APP_FRONT_END');
    $notification = array(
        'title' => 'you got new order', 'body' => 'please click on alert to get the order before reject after 3 minutes', "icon" => "http://localhost:3000/favicon.ico", 'sound' => 'default',    'order' => $tip
    );
    $arrayToSend = array('registration_ids' => $token, 'notification' => $notification, 'priority' => 'high');
    $json = json_encode($arrayToSend);
    $headers = array();
    $headers[] = 'Content-Type: application/json';
    $headers[] = 'Authorization: key=' . $serverKey;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    // curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //prevent return

    //Send the request
    curl_exec($ch);
    // //Close request
    // if ($response === FALSE) {
    //     die('FCM Send Error: ' . curl_error($ch));
    // }
    curl_close($ch);
}

function crypto($length)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
