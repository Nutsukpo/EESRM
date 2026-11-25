<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $isOwner = $user->role === 'owner';

        $today = Carbon::today();
        $now = Carbon::now();

        /*
         * Cashiers see only their own completed payments.
         * Owners see all completed payments.
         */
        $todayPayments = Payment::query()
            ->where('status', 'completed')
            ->whereBetween('paid_at', [$today, $now])
            ->when(
                !$isOwner,
                fn ($query) => $query->where('received_by', $user->id)
            );

        $todaySales = (float) (clone $todayPayments)->sum('amount');

        $paymentsToday = (clone $todayPayments)->count();

        $collectionsByMethod = (clone $todayPayments)
            ->select('method', DB::raw('SUM(amount) as total'))
            ->groupBy('method')
            ->pluck('total', 'method');

        $cashSales = (float) ($collectionsByMethod['cash'] ?? 0);
        $mobileMoneySales = (float) ($collectionsByMethod['mobile_money'] ?? 0);
        $cardSales = (float) ($collectionsByMethod['card'] ?? 0);

        /*
         * Orders are restricted by cashier_id for non-owners.
         */
        $todayOrders = Order::query()
            ->when(
                !$isOwner,
                fn ($query) => $query->where('cashier_id', $user->id)
            );

        $ordersToday = (clone $todayOrders)
            ->whereBetween('created_at', [$today, $now])
            ->count();

        $cancelledOrdersToday = (clone $todayOrders)
            ->whereBetween('created_at', [$today, $now])
            ->where('status', 'cancelled')
            ->count();

        $awaitingService = (clone $todayOrders)
            ->where('payment_status', 'paid')
            ->whereNotIn('status', ['served', 'cancelled'])
            ->count();

        $servedToday = (clone $todayOrders)
            ->where('status', 'served')
            ->whereBetween('served_at', [$today, $now])
            ->count();

        $recentOrders = Order::query()
            ->with('payments')
            ->when(
                !$isOwner,
                fn ($query) => $query->where('cashier_id', $user->id)
            )
            ->latest('created_at')
            ->take(8)
            ->get();

        $recentPayments = Payment::query()
            ->with(['order', 'receivedBy'])
            ->where('status', 'completed')
            ->when(
                !$isOwner,
                fn ($query) => $query->where('received_by', $user->id)
            )
            ->latest('paid_at')
            ->take(5)
            ->get();

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
