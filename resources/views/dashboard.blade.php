
@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

@php
    $user = auth()->user();

    $role = $user->role ?? '';

    $isOwner = $role === 'owner';
    $isCashier = $role === 'cashier';
    $isServer = $role === 'server';

    $money = fn ($amount) => 'GH₵ ' . number_format((float) $amount, 2);

    $statusClasses = [
        'pending' => 'bg-warning-subtle text-warning-emphasis',
        'pending_payment' => 'bg-warning-subtle text-warning-emphasis',
        'preparing' => 'bg-info-subtle text-info-emphasis',
        'ready' => 'bg-primary-subtle text-primary-emphasis',
        'paid' => 'bg-success-subtle text-success-emphasis',
        'served' => 'bg-primary-subtle text-primary-emphasis',
        'cancelled' => 'bg-danger-subtle text-danger-emphasis',
    ];

    $statusLabels = [
        'pending' => 'Pending',
        'pending_payment' => 'Pending',
        'preparing' => 'Preparing',
        'ready' => 'Ready',
        'paid' => 'Paid',
        'served' => 'Served',
        'cancelled' => 'Cancelled',
    ];
@endphp

{{-- Welcome --}}
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="page-heading mb-1">
            Welcome back, {{ $user->name }}
        </h1>

        <p class="page-description mb-0">
            Here is your restaurant's operational overview.
        </p>
    </div>

    <div class="text-muted small">
        <i class="fa-regular fa-calendar me-1"></i>
        {{ now()->format('l, d M Y') }}
    </div>
</div>

{{-- Main Statistics --}}
<div class="row g-3 mb-4">

    {{-- Today's Sales --}}
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="content-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small mb-2">Today's Sales</div>
                    <div class="fs-4 fw-bold">{{ $money($todaySales) }}</div>
                </div>

                <div class="dashboard-icon bg-success-subtle text-success">
                    <i class="fa-solid fa-money-bill-wave"></i>
                </div>
            </div>

            <div class="small text-muted mt-3">
                <i class="fa-solid fa-check-circle me-1"></i>
                Completed payments received today
            </div>
        </div>
    </div>

    {{-- Orders Today --}}
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="content-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small mb-2">Orders Today</div>
                    <div class="fs-4 fw-bold">
                        {{ number_format($ordersToday) }}
                    </div>
                </div>

                <div class="dashboard-icon bg-primary-subtle text-primary">
                    <i class="fa-solid fa-receipt"></i>
                </div>
            </div>

            <div class="small text-muted mt-3">
                {{ number_format($cancelledOrdersToday) }} cancelled today
            </div>
        </div>
    </div>

    {{-- Awaiting Service --}}
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="content-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small mb-2">Awaiting Service</div>
                    <div class="fs-4 fw-bold">
                        {{ number_format($awaitingService) }}
                    </div>
                </div>

                <div class="dashboard-icon bg-warning-subtle text-warning-emphasis">
                    <i class="fa-solid fa-bell-concierge"></i>
                </div>
            </div>

            <div class="small text-muted mt-3">
                Paid orders not yet served
            </div>
        </div>
    </div>

    {{-- Served Today --}}
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="content-card p-4 h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="text-muted small mb-2">Served Today</div>
                    <div class="fs-4 fw-bold">
                        {{ number_format($servedToday) }}
                    </div>
                </div>

                <div class="dashboard-icon bg-info-subtle text-info">
                    <i class="fa-solid fa-check-double"></i>
                </div>
            </div>

            <div class="small text-muted mt-3">
                Orders completed by the service team
            </div>
        </div>
    </div>

</div>

{{-- Payment Collections --}}
<div class="content-card p-4 mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h5 class="fw-bold mb-1">Today's Payment Collections</h5>
            <p class="text-muted small mb-0">
                Completed payments grouped by payment method.
            </p>
        </div>

        <span class="badge text-bg-light">
            {{ number_format($paymentsToday) }} payments
        </span>
    </div>

    <div class="row g-3">
        <div class="col-12 col-md-4">
            <div class="collection-item">
                <div class="collection-icon text-success">
                    <i class="fa-solid fa-money-bill-1-wave"></i>
                </div>

                <div>
                    <div class="small text-muted">Cash</div>
                    <div class="fw-bold fs-5">{{ $money($cashSales) }}</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="collection-item">
                <div class="collection-icon text-primary">
                    <i class="fa-solid fa-mobile-screen-button"></i>
                </div>

                <div>
                    <div class="small text-muted">Mobile Money</div>
                    <div class="fw-bold fs-5">{{ $money($mobileMoneySales) }}</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="collection-item">
                <div class="collection-icon text-info">
                    <i class="fa-regular fa-credit-card"></i>
                </div>

                <div>
                    <div class="small text-muted">Card</div>
                    <div class="fw-bold fs-5">{{ $money($cardSales) }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Operations and Quick Actions --}}
<div class="row g-3 mb-4">

    <div class="col-12 col-xl-7">
        <div class="content-card p-4 h-100">
            <h5 class="fw-bold mb-1">Operations Overview</h5>

            <p class="text-muted small mb-4">
                Monitor the progress of restaurant operations.
            </p>

            <div class="operation-row">
                <div class="operation-label">
                    <span class="operation-dot bg-success"></span>
                    Paid orders awaiting service
                </div>
                <strong>{{ number_format($awaitingService) }}</strong>
            </div>

            <div class="operation-row">
                <div class="operation-label">
                    <span class="operation-dot bg-primary"></span>
                    Orders served today
                </div>
                <strong>{{ number_format($servedToday) }}</strong>
            </div>

            <div class="operation-row">
                <div class="operation-label">
                    <span class="operation-dot bg-danger"></span>
                    Orders cancelled today
                </div>
                <strong>{{ number_format($cancelledOrdersToday) }}</strong>
            </div>

            <a href="{{ route('orders.index') }}"
               class="btn btn-outline-success mt-3">
                View All Orders
                <i class="fa-solid fa-arrow-right ms-1"></i>
            </a>
        </div>
    </div>

    <div class="col-12 col-xl-5">
        <div class="content-card p-4 h-100">
            <h5 class="fw-bold mb-1">Quick Actions</h5>

            <p class="text-muted small mb-4">
                Shortcuts for your daily responsibilities.
            </p>

            <div class="d-grid gap-2">

                @if($isOwner || $isCashier)
                    <a href="{{ url('/pos') }}"
                       class="btn btn-success text-start">
                        <i class="fa-solid fa-plus me-2"></i>
                        Create New Order
                    </a>

                    <a href="{{ route('orders.index') }}"
                       class="btn btn-outline-success text-start">
                        <i class="fa-solid fa-receipt me-2"></i>
                        Manage Orders
                    </a>

                    <a href="{{ route('payments.index') }}"
                       class="btn btn-outline-secondary text-start">
                        <i class="fa-solid fa-money-bill-transfer me-2"></i>
                        Payment History
                    </a>
                @endif

                @if($isOwner)
                    <a href="{{ route('users.index') }}"
                       class="btn btn-outline-secondary text-start">
                        <i class="fa-solid fa-users me-2"></i>
                        Manage Users
                    </a>
                @endif

                @if($isOwner || $isServer)
                    <a href="{{ route('orders.index') }}"
                       class="btn btn-outline-primary text-start">
                        <i class="fa-solid fa-ticket me-2"></i>
                        Verify Receipt / Find Order
                    </a>
                @endif

            </div>
        </div>
    </div>

</div>

{{-- Recent Orders --}}
<div class="content-card p-4 mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h5 class="fw-bold mb-1">Recent Orders</h5>
            <p class="text-muted small mb-0">
                The eight most recently recorded orders.
            </p>
        </div>

        <a href="{{ route('orders.index') }}"
           class="btn btn-sm btn-outline-success">
            View All
        </a>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Order Number</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Payment Status</th>
                    <!-- <th>Order Status</th> -->
                    <th class="text-end">Action</th>
                </tr>
            </thead>

            <tbody>
                @forelse($recentOrders as $order)
                    @php
                        $paymentStatus = $order->payment_status ?? 'unpaid';

                        $paymentLabel = match ($paymentStatus) {
                            'paid' => 'Paid',
                            'refunded' => 'Refunded',
                            default => 'Unpaid',
                        };

                        $paymentClass = match ($paymentStatus) {
                            'paid' => 'bg-success-subtle text-success-emphasis',
                            'refunded' => 'bg-danger-subtle text-danger-emphasis',
                            default => 'bg-secondary-subtle text-secondary-emphasis',
                        };

                        $orderStatus = $order->status ?? 'pending';
                    @endphp

                    <tr>
                        <td class="fw-semibold">
                            {{ $order->order_number }}
                        </td>

                        <td class="text-muted small">
                            {{ $order->created_at?->format('d M, h:i A') ?? '—' }}
                        </td>

                        <td class="fw-semibold">
                            {{ $money($order->total) }}
                        </td>

                        <td>
                            <span class="badge {{ $paymentClass }}">
                                {{ $paymentLabel }}
                            </span>
                        </td>

                        <!-- <td>
                            <span class="badge {{ $statusClasses[$orderStatus] ?? 'bg-secondary-subtle text-secondary-emphasis' }}">
                                {{ $statusLabels[$orderStatus] ?? ucfirst(str_replace('_', ' ', $orderStatus)) }}
                            </span>
                        </td> -->

                        <td class="text-end">
                            <a href="{{ route('orders.show', $order) }}"
                               class="btn btn-sm btn-outline-success"
                               aria-label="View order {{ $order->order_number }}">
                                <i class="fa-solid fa-eye"></i>
                                <span class="d-none d-md-inline">View</span>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="fa-solid fa-receipt fs-3 d-block mb-2"></i>
                            No orders have been recorded yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Recent Completed Payments --}}
<div class="content-card p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h5 class="fw-bold mb-1">Recent Completed Payments</h5>
            <p class="text-muted small mb-0">
                Latest successfully recorded payments.
            </p>
        </div>

        <a href="{{ route('payments.index') }}"
           class="btn btn-sm btn-outline-success">
            Payment History
        </a>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Order</th>
                    <th>Method</th>
                    <th>Cashier</th>
                    <th>Date</th>
                    <th class="text-end">Amount</th>
                </tr>
            </thead>

            <tbody>
                @forelse($recentPayments as $payment)
                    <tr>
                        <td class="fw-semibold">
                            {{ $payment->payment_reference ?: 'PAY-' . $payment->id }}
                        </td>

                        <td>
                            {{ $payment->order?->order_number ?? '—' }}
                        </td>

                        <td>
                            {{ ucwords(str_replace('_', ' ', $payment->method)) }}
                        </td>

                        <td>
                            {{ $payment->receivedBy?->name ?? '—' }}
                        </td>

                        <td class="text-muted small">
                            {{ $payment->paid_at?->format('d M, h:i A') ?? '—' }}
                        </td>

                        <td class="text-end fw-semibold">
                            {{ $money($payment->amount) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            No completed payments have been recorded yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('styles')
<style>
    .dashboard-icon {
        width: 46px;
        height: 46px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        font-size: 19px;
        flex-shrink: 0;
    }

    .collection-item {
        display: flex;
        align-items: center;
        gap: 14px;
        height: 100%;
        padding: 16px;
        border: 1px solid var(--bs-border-color, #dee2e6);
        border-radius: 12px;
    }

    .collection-icon {
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: var(--bs-tertiary-bg, #f8f9fa);
        font-size: 18px;
        flex-shrink: 0;
    }

    .operation-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 13px 0;
        border-bottom: 1px solid var(--bs-border-color, #dee2e6);
    }

    .operation-row:last-of-type {
        border-bottom: 0;
    }

    .operation-label {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: .92rem;
    }

    .operation-dot {
        width: 9px;
        height: 9px;
        display: inline-block;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .content-card .table th {
        color: var(--bs-secondary-color, #6c757d);
        font-size: .8rem;
        font-weight: 600;
        white-space: nowrap;
        border-bottom-width: 1px;
    }

    .content-card .table td {
        font-size: .9rem;
    }

    @media (max-width: 575.98px) {
        .content-card {
            padding: 1rem !important;
        }

        .collection-item {
            padding: 12px;
        }
    }
</style>
@endpush
