<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the restaurant dashboard.
     *
     * Business rules:
     * - No credit sales.
     * - A completed payment represents one completed sale.
     * - Only completed payments count towards sales.
     * - Outstanding balances are not calculated or displayed.
     */
    public function index(Request $request)
    {
        $today = Carbon::today();
        $now = Carbon::now();

        /*
        |--------------------------------------------------------------------------
        | Today's Sales
        |--------------------------------------------------------------------------
        */

        $todayPayments = Payment::query()
            ->where('status', 'completed')
            ->whereBetween('paid_at', [$today, $now]);

        $todaySales = (float) (clone $todayPayments)
            ->sum('amount');

        $paymentsToday = (clone $todayPayments)
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Today's Sales By Payment Method
        |--------------------------------------------------------------------------
        */

        $collectionsByMethod = (clone $todayPayments)
            ->select('method', DB::raw('SUM(amount) as total'))
            ->groupBy('method')
            ->pluck('total', 'method');

        $cashSales = (float) ($collectionsByMethod['cash'] ?? 0);

        $mobileMoneySales = (float) (
            $collectionsByMethod['mobile_money'] ?? 0
        );

        $cardSales = (float) ($collectionsByMethod['card'] ?? 0);

        /*
        |--------------------------------------------------------------------------
        | Orders Recorded Today
        |--------------------------------------------------------------------------
        */

        $ordersToday = Order::query()
            ->whereBetween('created_at', [$today, $now])
            ->count();

        $cancelledOrdersToday = Order::query()
            ->whereBetween('created_at', [$today, $now])
            ->where('status', 'cancelled')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Paid Orders Awaiting Service
        |--------------------------------------------------------------------------
        */

        $awaitingService = Order::query()
            ->where('status', 'paid')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Orders Served Today
        |--------------------------------------------------------------------------
        */

        $servedToday = Order::query()
            ->where('status', 'served')
            ->whereBetween('served_at', [$today, $now])
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Recent Orders
        |--------------------------------------------------------------------------
        */

        $recentOrders = Order::query()
            ->with(['payments'])
            ->latest('created_at')
            ->take(8)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Recent Completed Sales
        |--------------------------------------------------------------------------
        */

        $recentPayments = Payment::query()
            ->with(['order', 'receivedBy'])
            ->where('status', 'completed')
            ->latest('paid_at')
            ->take(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Dashboard View
        |--------------------------------------------------------------------------
        */

        return view('dashboard', compact(
            'todaySales',
            'paymentsToday',
            'ordersToday',
            'cancelledOrdersToday',
            'awaitingService',
            'servedToday',
            'cashSales',
            'mobileMoneySales',
            'cardSales',
            'recentOrders',
            'recentPayments'
        ));
    }
}
