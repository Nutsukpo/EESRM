
@extends('layouts.app')

@section('title', 'Orders Management')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold mb-1">Orders Management</h2>
            <p class="text-muted mb-0">View and manage restaurant orders.</p>
        </div>

        <a href="{{ route('pos.index') }}" class="btn btn-success">
            <i class="fas fa-cash-register me-2"></i> Open POS
        </a>
    </div>

    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('status') }}
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
            <strong>Please check the following:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $totalOrders = $orders->total();
        $unpaidOrders = $orders->getCollection()->where('payment_status', 'unpaid')->count();
        $paidOrders = $orders->getCollection()->where('payment_status', 'paid')->count();
        $servedOrders = $orders->getCollection()->where('status', 'served')->count();
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Matching Orders</div>
                    <div class="fs-3 fw-bold">{{ number_format($totalOrders) }}</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Unpaid on This Page</div>
                    <div class="fs-3 fw-bold text-warning">{{ $unpaidOrders }}</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Paid on This Page</div>
                    <div class="fs-3 fw-bold text-success">{{ $paidOrders }}</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Served on This Page</div>
                    <div class="fs-3 fw-bold text-primary">{{ $servedOrders }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('orders.index') }}" method="GET">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-lg-4">
                        <label for="search" class="form-label">Search orders</label>
                        <input
                            type="text"
                            name="search"
                            id="search"
                            class="form-control"
                            value="{{ request('search') }}"
                            placeholder="Order number or cashier name"
                        >
                    </div>

                    <div class="col-12 col-md-5 col-lg-3">
                        <label for="payment_status" class="form-label">Payment status</label>
                        <select name="payment_status" id="payment_status" class="form-select">
                            <option value="">All payment statuses</option>
                            @foreach(['unpaid' => 'Unpaid', 'partially_paid' => 'Partially Paid', 'paid' => 'Paid', 'refunded' => 'Refunded'] as $value => $label)
                                <option value="{{ $value }}" @selected(request('payment_status') === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-5 col-lg-3">
                        <label for="status" class="form-label">Order status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">All order statuses</option>
                            @foreach(['pending' => 'Pending', 'preparing' => 'Preparing', 'ready' => 'Ready', 'served' => 'Served', 'cancelled' => 'Cancelled'] as $value => $label)
                                <option value="{{ $value }}" @selected(request('status') === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-lg-2 d-flex gap-2">
                        <button type="submit" class="btn btn-success flex-grow-1">
                            <i class="fas fa-search me-1"></i> Filter
                        </button>
                        <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-undo"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold">Order Records</h5>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Order</th>
                        <th>Date</th>
                        <th>Cashier</th>
                        <th>Type</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td class="ps-3">
                                <div class="fw-semibold">{{ $order->order_number }}</div>
                                <small class="text-muted">#{{ $order->id }}</small>
                            </td>

                            <td>
                                {{ $order->created_at?->format('d M Y') ?? '—' }}
                                <div class="small text-muted">
                                    {{ $order->created_at?->format('h:i A') ?? '' }}
                                </div>
                            </td>

                            <td>{{ $order->cashier?->name ?? 'System' }}</td>

                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ $order->order_type === 'dine_in' ? 'Dine-in' : 'Takeaway' }}
                                </span>
                            </td>

                            <td class="fw-semibold text-nowrap">
                                GH₵{{ number_format((float) $order->total, 2) }}
                            </td>

                            <td>
                                @php
                                    $paymentColors = [
                                        'unpaid' => 'warning',
                                        'partially_paid' => 'info',
                                        'paid' => 'success',
                                        'refunded' => 'secondary',
                                    ];
                                @endphp
                                <span class="badge text-bg-{{ $paymentColors[$order->payment_status] ?? 'secondary' }}">
                                    {{ ucfirst(str_replace('_', ' ', $order->payment_status)) }}
                                </span>
                            </td>

                            <td>
                                @php
                                    $statusColors = [
                                        'pending' => 'warning',
                                        'preparing' => 'info',
                                        'ready' => 'primary',
                                        'served' => 'success',
                                        'cancelled' => 'danger',
                                    ];
                                @endphp
                                <span class="badge text-bg-{{ $statusColors[$order->status] ?? 'secondary' }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>

                            <td class="text-end pe-3 text-nowrap">
                                <a
                                    href="{{ route('orders.show', $order) }}"
                                    class="btn btn-sm btn-outline-success"
                                    title="View order"
                                >
                                    <i class="fas fa-eye"></i>
                                </a>

                                @if($order->payment_status === 'unpaid' && !in_array($order->status, ['served', 'cancelled'], true))
                                    <a
                                        href="{{ route('orders.edit', $order) }}"
                                        class="btn btn-sm btn-outline-primary"
                                        title="Edit order"
                                    >
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @else
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-secondary"
                                        disabled
                                        title="Only unpaid orders can be edited"
                                    >
                                        <i class="fas fa-lock"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="fas fa-receipt fa-2x text-muted mb-3"></i>
                                <h6>No orders found</h6>
                                <p class="text-muted mb-0">
                                    Orders created through the POS will appear here.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="card-footer bg-white">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
