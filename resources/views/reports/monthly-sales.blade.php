
@extends('layouts.app')

@section('title', 'Monthly Sales Report')
@section('page-title', 'Monthly Sales Report')

@section('content')
@php
    $money = fn ($amount) => 'GH₵' . number_format((float) $amount, 2);

    $monthName = \Carbon\Carbon::create($year, $month, 1)->format('F Y');
@endphp

<div class="container-fluid py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Monthly Sales Report</h2>
            <p class="text-muted mb-0">{{ $monthName }}</p>
        </div>

        <button type="button" class="btn btn-outline-dark" onclick="window.print()">
            <i class="fa-solid fa-print me-1"></i> Print Report
        </button>
    </div>

    <form method="GET" action="{{ route('reports.monthly-sales') }}"
          class="card border-0 shadow-sm mb-4 report-filter">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-sm-5 col-md-3">
                    <label for="month" class="form-label fw-semibold">Month</label>
                    <select class="form-select" id="month" name="month" required>
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" @selected($month === $m)>
                                {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="col-sm-4 col-md-2">
                    <label for="year" class="form-label fw-semibold">Year</label>
                    <select class="form-select" id="year" name="year" required>
                        @for ($y = max(2020, now()->year - 5); $y <= now()->year + 2; $y++)
                            <option value="{{ $y }}" @selected($year === $y)>
                                {{ $y }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="col-auto">
                    <button class="btn btn-success" type="submit">
                        <i class="fa-solid fa-filter me-1"></i> Filter
                    </button>
                </div>

                <div class="col-auto">
                    <a href="{{ route('reports.monthly-sales') }}"
                       class="btn btn-outline-secondary">This Month</a>
                </div>
            </div>
        </div>
    </form>

    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Monthly Sales</div>
                    <h3 class="text-success fw-bold mt-2 mb-0">
                        {{ $money($totalSales) }}
                    </h3>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Successful Payments</div>
                    <h3 class="fw-bold mt-2 mb-0">{{ $paymentCount }}</h3>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Cash Sales</div>
                    <h4 class="fw-bold mt-2 mb-0">{{ $money($cashSales) }}</h4>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Mobile Money + Card</div>
                    <h4 class="fw-bold mt-2 mb-0">
                        {{ $money($mobileMoneySales + $cardSales) }}
                    </h4>
                    <small class="text-muted">
                        MoMo: {{ $money($mobileMoneySales) }} ·
                        Card: {{ $money($cardSales) }}
                    </small>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-0">Daily Breakdown</h5>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th class="text-center">Payments</th>
                        <th class="text-end">Sales</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($dailyTotals as $day)
                        <tr>
                            <td>
                                <a href="{{ route('reports.daily-sales', ['date' => $day->sale_date]) }}">
                                    {{ \Carbon\Carbon::parse($day->sale_date)->format('D, d M Y') }}
                                </a>
                            </td>
                            <td class="text-center">{{ $day->payment_count }}</td>
                            <td class="text-end fw-semibold">{{ $money($day->total) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">
                                No completed sales were found for this month.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($dailyTotals->isNotEmpty())
                    <tfoot class="table-light">
                        <tr>
                            <th>Monthly Total</th>
                            <th class="text-center">{{ $paymentCount }}</th>
                            <th class="text-end text-success">{{ $money($totalSales) }}</th>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-0">Monthly Transactions</h5>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date & Time</th>
                        <th>Payment Ref.</th>
                        <th>Order Number</th>
                        <th>Cashier</th>
                        <th>Method</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $payment)
                        <tr>
                            <td>{{ $payment->paid_at
                                ? \Carbon\Carbon::parse($payment->paid_at)->format('d M Y, h:i A')
                                : '—' }}</td>
                            <td>{{ $payment->payment_reference ?: 'PAY-'.$payment->id }}</td>
                            <td>{{ $payment->order?->order_number ?? '—' }}</td>
                            <td>{{ $payment->receivedBy?->name ?? '—' }}</td>
                            <td>{{ ucwords(str_replace('_', ' ', $payment->method)) }}</td>
                            <td class="text-end fw-semibold">{{ $money($payment->amount) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                No transactions found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($payments->hasPages())
            <div class="card-footer bg-white">
                {{ $payments->links() }}
            </div>
        @endif
    </div>
</div>

<style>
@media print {
    .app-sidebar, .app-header, .navbar, .report-filter,
    button, .btn, .pagination, .card-footer {
        display: none !important;
    }

    body, .content-wrapper, main {
        background: #fff !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .card {
        box-shadow: none !important;
        border: 1px solid #ddd !important;
        break-inside: avoid;
    }

    table {
        font-size: 10px;
    }
}
</style>
@endsection
