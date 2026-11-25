
@extends('layouts.app')

@section('title', 'Payment Management')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Payment Management</h2>
            <p class="text-muted mb-0">View and track recorded payments.</p>
        </div>
        <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-receipt me-1"></i> View Orders
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('payments.index') }}"
                  class="row g-3 align-items-end">
                <div class="col-md-7">
                    <label for="search" class="form-label">Search payments</label>
                    <input id="search" type="text" name="search"
                           class="form-control"
                           value="{{ request('search') }}"
                           placeholder="Payment reference or order number">
                </div>

                <div class="col-md-3">
                    <label for="status" class="form-label">Payment status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">All statuses</option>
                        @foreach(['completed', 'pending', 'failed', 'refunded'] as $status)
                            <option value="{{ $status }}"
                                @selected(request('status') === $status)>
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-success flex-grow-1" type="submit">
                        <i class="fas fa-search me-1"></i> Search
                    </button>
                    <a href="{{ route('payments.index') }}"
                       class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Payment Records</h5>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Reference</th>
                        <th>Order</th>
                        <th>Date</th>
                        <th>Method</th>
                        <th>Received By</th>
                        <th class="text-end">Amount</th>
                        <th>Status</th>
                        <th>Receipt</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td>
                                <span class="fw-semibold">
                                    {{ $payment->payment_reference ?: 'PAY-'.$payment->id }}
                                </span>
                            </td>
                            <td>
                                @if($payment->order)
                                    <a href="{{ route('orders.show', $payment->order) }}">
                                        {{ $payment->order->order_number }}
                                    </a>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                {{ $payment->paid_at?->format('d M Y, h:i A') ?? $payment->created_at?->format('d M Y, h:i A') ?? '—' }}
                            </td>
                            <td>{{ ucwords(str_replace('_', ' ', $payment->method)) }}</td>
                            <td>{{ $payment->receivedBy?->name ?? '—' }}</td>
                            <td class="text-end fw-semibold">
                                GH₵{{ number_format((float) $payment->amount, 2) }}
                            </td>
                            <td>
                                @php
                                    $statusClass = match($payment->status) {
                                        'completed' => 'success',
                                        'pending' => 'warning',
                                        'failed' => 'danger',
                                        'refunded' => 'secondary',
                                        default => 'light',
                                    };
                                @endphp
                                <span class="badge text-bg-{{ $statusClass }}">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>
                            <td>
                                @if($payment->status === 'completed')
                                    <a href="{{ route('payments.receipt', $payment) }}"
                                       class="btn btn-sm btn-outline-success">
                                        <i class="fas fa-print me-1"></i> Receipt
                                    </a>
                                @else
                                    <span class="text-muted">Unavailable</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-money-bill-wave fa-2x mb-3 d-block"></i>
                                No payment records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
            <div class="card-footer bg-white">
                {{ $payments->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
