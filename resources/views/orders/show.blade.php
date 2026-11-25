
@extends('layouts.app')

@section('title', 'Order Details')

@section('content')
<div class="container-fluid py-4">

    {{-- Page Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Order Details</h2>
            <p class="text-muted mb-0">
                Order {{ $order->order_number }}
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i> Back to Orders
            </a>

            @if($order->payment_status === 'unpaid' && !in_array($order->status, ['served', 'cancelled'], true))
                <a href="{{ route('orders.edit', $order) }}" class="btn btn-outline-success">
                    <i class="fas fa-edit me-2"></i> Edit Order
                </a>
            @endif

            {{-- Record Payment Button --}}
            @if(
                !in_array($order->status, ['cancelled', 'served'], true)
                && $order->payment_status !== 'refunded'
                && (float) $order->total > (float) $order->payments
                    ->where('status', 'completed')
                    ->sum('amount')
            )
                <a href="{{ route('payments.create', $order) }}"
                   class="btn btn-success">
                    <i class="fas fa-money-bill-wave me-1"></i>
                    Record Payment
                </a>
            @endif
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Please correct the following:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $paymentColors = [
            'unpaid' => 'warning',
            'partially_paid' => 'info',
            'paid' => 'success',
            'refunded' => 'secondary',
        ];

        $statusColors = [
            'pending' => 'warning',
            'preparing' => 'info',
            'ready' => 'primary',
            'served' => 'success',
            'cancelled' => 'danger',
        ];

        $completedPayments = $order->payments
            ->where('status', 'completed');

        $paidAmount = (float) $completedPayments->sum('amount');

        $balance = max(
            0,
            round((float) $order->total - $paidAmount, 2)
        );
    @endphp

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">

        <div class="col-12 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-2">Order Total</div>
                    <div class="fs-3 fw-bold text-success">
                        GH₵{{ number_format((float) $order->total, 2) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-2">Total Paid</div>
                    <div class="fs-3 fw-bold text-success">
                        GH₵{{ number_format($paidAmount, 2) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-2">Outstanding Balance</div>
                    <div class="fs-3 fw-bold {{ $balance > 0 ? 'text-danger' : 'text-success' }}">
                        GH₵{{ number_format($balance, 2) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-2">Payment Status</div>
                    <span class="badge fs-6 text-bg-{{ $paymentColors[$order->payment_status] ?? 'secondary' }}">
                        {{ ucfirst(str_replace('_', ' ', $order->payment_status)) }}
                    </span>

                    <div class="mt-3">
                        <div class="text-muted small mb-2">Order Status</div>
                        <span class="badge text-bg-{{ $statusColors[$order->status] ?? 'secondary' }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="row g-4">

        {{-- Main Content --}}
        <div class="col-12 col-xl-8">

            {{-- Ordered Items --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0">
                        <i class="fas fa-utensils me-2 text-success"></i>
                        Ordered Items
                    </h5>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Item</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-end pe-3">Subtotal</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($order->items as $item)
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-semibold">
                                            {{ $item->item_name }}
                                        </div>

                                        @if($item->notes)
                                            <small class="text-muted">
                                                {{ $item->notes }}
                                            </small>
                                        @endif
                                    </td>

                                    <td class="text-end text-nowrap">
                                        GH₵{{ number_format((float) $item->unit_price, 2) }}
                                    </td>

                                    <td class="text-center">
                                        {{ $item->quantity }}
                                    </td>

                                    <td class="text-end pe-3 fw-semibold text-nowrap">
                                        GH₵{{ number_format((float) $item->subtotal, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">
                                        No items have been recorded for this order.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Order Totals --}}
                <div class="card-body">
                    <div class="row justify-content-end">
                        <div class="col-12 col-md-7 col-lg-6">

                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Subtotal</span>
                                <span>
                                    GH₵{{ number_format((float) $order->subtotal, 2) }}
                                </span>
                            </div>

                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Discount</span>
                                <span>
                                    GH₵{{ number_format((float) $order->discount, 2) }}
                                </span>
                            </div>

                            <div class="d-flex justify-content-between mb-3">
                                <span class="text-muted">Tax</span>
                                <span>
                                    GH₵{{ number_format((float) $order->tax, 2) }}
                                </span>
                            </div>

                            <hr>

                            <div class="d-flex justify-content-between fs-5 fw-bold">
                                <span>Total</span>
                                <span class="text-success">
                                    GH₵{{ number_format((float) $order->total, 2) }}
                                </span>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            {{-- Payment Records --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Payment Records</h5>

                    <span class="badge text-bg-light">
                        {{ $order->payments->count() }} record(s)
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Method</th>
                                <th>Reference</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th class="text-end pe-3">Amount</th>
                                <th>Receipt</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($order->payments as $payment)
                                <tr>
                                    <td class="ps-3">
                                        {{ ucwords(str_replace('_', ' ', $payment->method)) }}
                                    </td>

                                    <td>
                                        {{ $payment->payment_reference ?: 'PAY-'.$payment->id }}
                                    </td>

                                    <td class="text-nowrap">
                                        {{ $payment->paid_at?->format('d M Y, h:i A') ?? $payment->created_at?->format('d M Y, h:i A') ?? '—' }}
                                    </td>

                                    <td>
                                        @php
                                            $paymentStatusColor = match($payment->status) {
                                                'completed' => 'success',
                                                'pending' => 'warning',
                                                'failed' => 'danger',
                                                'refunded' => 'secondary',
                                                default => 'light',
                                            };
                                        @endphp

                                        <span class="badge text-bg-{{ $paymentStatusColor }}">
                                            {{ ucfirst($payment->status) }}
                                        </span>
                                    </td>

                                    <td class="text-end pe-3 text-nowrap">
                                        GH₵{{ number_format((float) $payment->amount, 2) }}
                                    </td>

                                    <td>
                                        @if($payment->status === 'completed')
                                            <a href="{{ route('payments.receipt', $payment) }}"
                                               class="btn btn-sm btn-outline-success">
                                                <i class="fas fa-receipt me-1"></i>
                                                Receipt
                                            </a>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="fas fa-money-bill-wave fa-2x mb-2 d-block"></i>
                                        No payment records yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($order->payments->contains('status', 'completed'))
                    <div class="card-footer bg-white">
                        <div class="d-flex justify-content-between flex-wrap gap-2">
                            <span class="text-muted">Total successfully paid</span>
                            <strong class="text-success">
                                GH₵{{ number_format($paidAmount, 2) }}
                            </strong>
                        </div>
                    </div>
                @endif
            </div>

        </div>

        {{-- Sidebar --}}
        <div class="col-12 col-xl-4">

            {{-- Order Information --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0">Order Information</h5>
                </div>

                <div class="card-body">

                    <div class="mb-3">
                        <div class="text-muted small">Order Number</div>
                        <div class="fw-semibold">{{ $order->order_number }}</div>
                    </div>

                    <div class="mb-3">
                        <div class="text-muted small">Order Type</div>
                        <div class="fw-semibold">
                            {{ $order->order_type === 'dine_in' ? 'Dine-in' : 'Takeaway' }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="text-muted small">Created</div>
                        <div>
                            {{ $order->created_at?->format('d M Y, h:i A') ?? '—' }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="text-muted small">Cashier</div>
                        <div>{{ $order->cashier?->name ?? 'System' }}</div>
                    </div>

                    <div class="mb-3">
                        <div class="text-muted small">Served By</div>
                        <div>{{ $order->server?->name ?? 'Not recorded' }}</div>
                    </div>

                    <div class="mb-3">
                        <div class="text-muted small">Served At</div>
                        <div>
                            {{ $order->served_at?->format('d M Y, h:i A') ?? 'Not served' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-muted small">Receipt Verified</div>
                        <div>
                            {{ $order->receipt_verified_at?->format('d M Y, h:i A') ?? 'Not verified' }}
                        </div>
                    </div>

                </div>
            </div>

            {{-- Order Notes --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0">
                        <i class="fas fa-sticky-note me-2 text-warning"></i>
                        Order Notes
                    </h5>
                </div>

                <div class="card-body">
                    @if($order->notes)
                        <p class="mb-0">{{ $order->notes }}</p>
                    @else
                        <p class="text-muted mb-0">
                            No notes were added to this order.
                        </p>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
