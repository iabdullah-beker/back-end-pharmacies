<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Order;
use App\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    public function getStatisticsForVendor(){
        $pharmacy = auth()->user()->pharmacy;
        $order = $pharmacy->order;
        //******************* All ************************* */

        $accepted = count($order->where('status',1));
        $rejected = count($order->where('status',2));
        $pending = count($order->where('status',4));
        $balance = 0;
        foreach($order as $or)
            $balance += $or->price;
        //******************* Monthly ************************* */
        $monthAccepted = DB::table("orders")
        ->whereMonth('created_at', Carbon::now()->month)
        ->where('pharmacy_id',$pharmacy->id)
        ->where('status',1)
        ->count();

        $monthRejected = DB::table("orders")
        ->whereMonth('created_at', Carbon::now()->month)
        ->where('pharmacy_id',$pharmacy->id)
        ->where('status',2)
        ->count();

        $monthPending = DB::table("orders")
        ->whereMonth('created_at', Carbon::now()->month)
        ->where('pharmacy_id',$pharmacy->id)
        ->where('status',4)
        ->count();

        //******************* Last Month ************************* */
        $lastMonthAccepted = DB::table("orders")
        ->whereMonth('created_at', Carbon::now()->month-1)
        ->where('pharmacy_id',$pharmacy->id)
        ->where('status',1)
        ->count();

        $lastMonthRejected = DB::table("orders")
        ->whereMonth('created_at', Carbon::now()->month-1)
        ->where('pharmacy_id',$pharmacy->id)
        ->where('status',2)
        ->count();

        $lastMonthPending = DB::table("orders")
        ->whereMonth('created_at', Carbon::now()->month-1)
        ->where('pharmacy_id',$pharmacy->id)
        ->where('status',4)
        ->count();

        $today = today();
        $ordersCountThisMonth = array(); // this month
        for($i=1; $i < $today->daysInMonth + 1; ++$i) {
            $count = DB::table("orders")
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereDay('created_at', $i)
            ->where('pharmacy_id',$pharmacy->id)
            ->count();
            $ordersCountThisMonth [] = [$count];
        }

        $ordersCountLastMonth = array(); // Last month
        for($i=1; $i < $today->daysInMonth + 2; ++$i) {
            $count = DB::table("orders")
            ->whereMonth('created_at', Carbon::now()->month-1)
            ->whereDay('created_at', $i)
            ->where('pharmacy_id',$pharmacy->id)
            ->count();
            $ordersCountLastMonth [] = [$count];
        }

        $lastOrders = Order::where('pharmacy_id',$pharmacy->id)
        ->with('cosmetics')
        ->with('packages')
        ->with('user')
        ->orderBy('id', 'desc')
        ->take(10)
        ->get();

        $head = array(
            "accepted" => $accepted,
            "rejected" => $rejected,
            "pending" => $pending,
            "balance" => $balance,
        );

        $thismonth = array(
            "monthAccepted" => $monthAccepted,
            "monthRejected" => $monthRejected,
            "monthPending" => $monthPending,
        );
        $lastMonth = array(
            "lastMonthAccepted" => $lastMonthAccepted,
            "lastMonthRejected" => $lastMonthRejected,
            "lastMonthPending" => $lastMonthPending,
        );

        $chart = array(
            "chartThisMonth" => $ordersCountThisMonth,
            "chartLastMonth" => $ordersCountLastMonth,
        );
        return response()->json([
            "head" => $head,
            "thisMonth" =>$thismonth,
            "lastMonth" =>$lastMonth,
            "chart" => $chart,
            "lastOrders" =>$lastOrders
        ]);
    }

    public function getStatisticsForAdmin(){

        $order = Order::all();
        //******************* All ************************* */

        $accepted = count($order->where('status',1));
        $rejected = count($order->where('status',2));
        $sellers =  count(User::where('role','vendor')->get());
        $users =  count(User::where('role','user')->get());

        //******************* Monthly ************************* */
        $monthAccepted = DB::table("orders")
        ->whereMonth('created_at', Carbon::now()->month)
        ->where('status',1)
        ->count();

        $monthRejected = DB::table("orders")
        ->whereMonth('created_at', Carbon::now()->month)
        ->where('status',2)
        ->count();

        $monthPending = DB::table("orders")
        ->whereMonth('created_at', Carbon::now()->month)
        // ->where('status',4)
        ->count();

        //******************* Last Month ************************* */
        $lastMonthAccepted = DB::table("orders")
        ->whereMonth('created_at', Carbon::now()->month-1)
        ->where('status',1)
        ->count();

        $lastMonthRejected = DB::table("orders")
        ->whereMonth('created_at', Carbon::now()->month-1)
        ->where('status',2)
        ->count();

        $lastMonthPending = DB::table("orders")
        ->whereMonth('created_at', Carbon::now()->month-1)
        ->where('status',4)
        ->count();

        $today = today();
        $ordersCountThisMonth = array(); // this month
        for($i=1; $i < $today->daysInMonth + 1; ++$i) {
            $count = DB::table("orders")
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereDay('created_at', $i)
            ->count();
            $ordersCountThisMonth [] = [$count];
        }

        $ordersCountLastMonth = array(); // Last month
        for($i=1; $i < $today->daysInMonth + 2; ++$i) {
            $count = DB::table("orders")
            ->whereMonth('created_at', Carbon::now()->month-1)
            ->whereDay('created_at', $i)
            ->count();
            $ordersCountLastMonth [] = [$count];
        }

        $lastSellers = User::where('role','vendor')->orderBy('id', 'desc')->take(10)->get();
        $lastUsers = User::where('role','user')->orderBy('id', 'desc')->take(10)->get();

        $head = array(
            "accepted" => $accepted,
            "rejected" => $rejected,
            "sellers" => $sellers,
            "users" => $users,
        );

        $thismonth = array(
            "monthAccepted" => $monthAccepted,
            "monthRejected" => $monthRejected,
            "monthPending" => $monthPending,
        );
        $lastMonth = array(
            "lastMonthAccepted" => $lastMonthAccepted,
            "lastMonthRejected" => $lastMonthRejected,
            "lastMonthPending" => $lastMonthPending,
        );

        $chart = array(
            "chartThisMonth" => $ordersCountThisMonth,
            "chartLastMonth" => $ordersCountLastMonth,
        );
        return response()->json([
            "head" => $head,
            "thisMonth" =>$thismonth,
            "lastMonth" =>$lastMonth,
            "chart" => $chart,
            "lastSellers" => $lastSellers,
            "lastUsers" => $lastUsers
        ]);
    }
}
