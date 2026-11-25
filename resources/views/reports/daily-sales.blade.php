
@extends('layouts.app')

@section('title', 'Daily Sales Report')
@section('page-title', 'Daily Sales Report')

@section('content')
@php
    $money = fn ($amount) => 'GH₵' . number_format((float) $amount, 2);
@endphp

<div class="container-fluid py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Daily Sales Report</h2>
            <p class="text-muted mb-0">
                Completed sales for {{ \Carbon\Carbon::parse($date)->format('d M Y') }}
            </p>
        </div>

        <button type="button" class="btn btn-outline-dark" onclick="window.print()">
            <i class="fa-solid fa-print me-1"></i> Print Report
        </button>
    </div>

    <form method="GET" action="{{ route('reports.daily-sales') }}"
          class="card border-0 shadow-sm mb-4 report-filter">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-5 col-lg-4">
                    <label for="date" class="form-label fw-semibold">Select date</label>
                    <input type="date" name="date" id="date"
                           class="form-control"
                           value="{{ $date }}" required>
                </div>
                <div class="col-auto">
                    <button class="btn btn-success" type="submit">
                        <i class="fa-solid fa-filter me-1"></i> Filter
                    </button>
                </div>
                <div class="col-auto">
                    <a href="{{ route('reports.daily-sales') }}"
                       class="btn btn-outline-secondary">Today</a>
                </div>
            </div>
        </div>
    </form>

    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Total Sales</div>
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

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold">Sales Transactions</h5>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Time</th>
                        <th>Payment Ref.</th>
                        <th>Order Number</th>
                        <th>Cashier</th>
                        <th>Method</th>
                        <th class="text-end">Amount</th>
                        <th class="text-center">Receipt</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $payment)
                        <tr>
                            <td>{{ $payment->paid_at
                                ? \Carbon\Carbon::parse($payment->paid_at)->format('h:i A')
                                : '—' }}</td>
                            <td>{{ $payment->payment_reference ?: 'PAY-'.$payment->id }}</td>
                            <td>{{ $payment->order?->order_number ?? '—' }}</td>
                            <td>{{ $payment->receivedBy?->name ?? '—' }}</td>
                            <td>{{ ucwords(str_replace('_', ' ', $payment->method)) }}</td>
                            <td class="text-end fw-semibold">
                                {{ $money($payment->amount) }}
                            </td>
                            <td class="text-center">
                                <a href="{{ route('payments.receipt', $payment) }}"
                                   class="btn btn-sm btn-outline-success">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                No completed sales were found for this date.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($payments->count())
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="5" class="text-end">Sales on this page</th>
                            <th class="text-end">
                                {{ $money($payments->sum('amount')) }}
                            </th>
                            <th></th>
                        </tr>
                    </tfoot>
                @endif
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
        font-size: 11px;
    }
}
</style>
@endsection
