<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SalesReportController extends Controller
{
    /**
     * Only owners can access sales reports.
     */
    private function authorizeReports(): void
    {
        abort_unless(
            auth()->user()?->role === 'owner',
            403,
            'You are not authorized to access sales reports.'
        );
    }

    /**
     * Display sales for a selected date.
     */
    public function daily(Request $request)
    {
        $this->authorizeReports();

        $validated = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $date = $validated['date'] ?? Carbon::today()->toDateString();

        $start = Carbon::createFromFormat('!Y-m-d', $date);
        $end = $start->copy()->addDay();

        $paymentsQuery = Payment::query()
            ->where('status', 'completed')
            ->where('paid_at', '>=', $start)
            ->where('paid_at', '<', $end);

        $totalSales = (float) (clone $paymentsQuery)->sum('amount');

        $paymentCount = (clone $paymentsQuery)->count();

        $methodTotals = (clone $paymentsQuery)
            ->select('method', DB::raw('SUM(amount) as total'))
            ->groupBy('method')
            ->pluck('total', 'method');

        $payments = (clone $paymentsQuery)
            ->with(['order', 'receivedBy'])
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('reports.daily-sales', [
            'date' => $date,
            'totalSales' => $totalSales,
            'paymentCount' => $paymentCount,
            'cashSales' => (float) ($methodTotals['cash'] ?? 0),
            'mobileMoneySales' => (float) (
                $methodTotals['mobile_money'] ?? 0
            ),
            'cardSales' => (float) ($methodTotals['card'] ?? 0),
            'payments' => $payments,
        ]);
    }

    /**
     * Display sales for a selected month and year.
     */
    public function monthly(Request $request)
    {
        $this->authorizeReports();

        $validated = $request->validate([
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'min:2020', 'max:2100'],
        ]);

        $month = (int) (
            $validated['month'] ?? Carbon::today()->month
        );

        $year = (int) (
            $validated['year'] ?? Carbon::today()->year
        );

        $start = Carbon::create($year, $month, 1)->startOfDay();
        $end = $start->copy()->addMonth();

        $paymentsQuery = Payment::query()
            ->where('status', 'completed')
            ->where('paid_at', '>=', $start)
            ->where('paid_at', '<', $end);

        $totalSales = (float) (clone $paymentsQuery)->sum('amount');

        $paymentCount = (clone $paymentsQuery)->count();

        $methodTotals = (clone $paymentsQuery)
            ->select('method', DB::raw('SUM(amount) as total'))
            ->groupBy('method')
            ->pluck('total', 'method');

        $dailyTotals = (clone $paymentsQuery)
            ->selectRaw(
                'DATE(paid_at) as sale_date,
                 COUNT(*) as payment_count,
                 SUM(amount) as total'
            )
            ->groupByRaw('DATE(paid_at)')
            ->orderBy('sale_date')
            ->get();

        $payments = (clone $paymentsQuery)
            ->with(['order', 'receivedBy'])
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        return view('reports.monthly-sales', [
            'month' => $month,
            'year' => $year,
            'totalSales' => $totalSales,
            'paymentCount' => $paymentCount,
            'cashSales' => (float) ($methodTotals['cash'] ?? 0),
            'mobileMoneySales' => (float) (
                $methodTotals['mobile_money'] ?? 0
            ),
            'cardSales' => (float) ($methodTotals['card'] ?? 0),
            'dailyTotals' => $dailyTotals,
            'payments' => $payments,
        ]);
    }
}
