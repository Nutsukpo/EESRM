
@extends('layouts.app')

@section('content')
<div class="container py-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Complete Payment</h1>
            <p class="text-muted mb-0">
                Order {{ $order->order_number }}
            </p>
        </div>

        <a href="{{ route('orders.show', $order) }}"
           class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Back to Order
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Please correct the following:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4">

        {{-- Order summary --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h2 class="h5 mb-0">Order Summary</h2>
                </div>

                <div class="card-body">

                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Order Number</span>
                        <strong>{{ $order->order_number }}</strong>
                    </div>

                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Order Type</span>
                        <strong>
                            {{ ucwords(str_replace('_', ' ', $order->order_type ?? '')) }}
                        </strong>
                    </div>

                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Items</span>
                        <strong>{{ $order->items->count() }}</strong>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Subtotal</span>
                        <strong>
                            GH₵{{ number_format((float) $order->subtotal, 2) }}
                        </strong>
                    </div>

                    @if ((float) $order->discount > 0)
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Discount</span>
                            <strong>
                                -GH₵{{ number_format((float) $order->discount, 2) }}
                            </strong>
                        </div>
                    @endif

                    @if ((float) $order->tax > 0)
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Tax</span>
                            <strong>
                                GH₵{{ number_format((float) $order->tax, 2) }}
                            </strong>
                        </div>
                    @endif

                    <hr>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-bold">Total to Pay</span>
                        <span class="fs-3 fw-bold text-success">
                            GH₵{{ number_format((float) $order->total, 2) }}
                        </span>
                    </div>

                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        Full payment is required to complete this sale.
                        Credit and partial payments are not accepted.
                    </div>

                </div>
            </div>
        </div>

        {{-- Payment form --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h2 class="h5 mb-0">Payment Details</h2>
                </div>

                <div class="card-body">

                    <form method="POST"
                          action="{{ route('payments.store', $order) }}">
                        @csrf

                        <div class="mb-4">
                            <label for="method" class="form-label fw-semibold">
                                Payment Method
                            </label>

                            <select name="method"
                                    id="method"
                                    class="form-select @error('method') is-invalid @enderror"
                                    required>
                                <option value="">Select payment method</option>

                                <option value="cash"
                                    {{ old('method') === 'cash' ? 'selected' : '' }}>
                                    Cash
                                </option>

                                <option value="mobile_money"
                                    {{ old('method') === 'mobile_money' ? 'selected' : '' }}>
                                    Mobile Money
                                </option>

                                <option value="card"
                                    {{ old('method') === 'card' ? 'selected' : '' }}>
                                    Card
                                </option>
                            </select>

                            @error('method')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="amount" class="form-label fw-semibold">
                                Full Payment Amount (GH₵)
                            </label>

                            <div class="input-group input-group-lg">
                                <span class="input-group-text">GH₵</span>

                                <input type="number"
                                       name="amount"
                                       id="amount"
                                       class="form-control @error('amount') is-invalid @enderror"
                                       value="{{ old('amount', number_format((float) $order->total, 2, '.', '')) }}"
                                       min="{{ number_format((float) $order->total, 2, '.', '') }}"
                                       max="{{ number_format((float) $order->total, 2, '.', '') }}"
                                       step="0.01"
                                       inputmode="decimal"
                                       required>
                            </div>

                            @error('amount')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror

                            <div class="form-text">
                                Enter the exact order total. Partial payments
                                and overpayments are not permitted.
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="payment_reference"
                                   class="form-label fw-semibold">
                                Payment Reference
                                <span class="text-muted fw-normal">(Optional)</span>
                            </label>

                            <input type="text"
                                   name="payment_reference"
                                   id="payment_reference"
                                   class="form-control @error('payment_reference') is-invalid @enderror"
                                   value="{{ old('payment_reference') }}"
                                   maxlength="255"
                                   placeholder="Mobile Money or card transaction reference">

                            @error('payment_reference')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <button type="submit"
                                    class="btn btn-success btn-lg flex-grow-1">
                                <i class="fas fa-check-circle me-1"></i>
                                Confirm Full Payment
                            </button>

                            <a href="{{ route('orders.show', $order) }}"
                               class="btn btn-outline-secondary btn-lg">
                                Cancel
                            </a>
                        </div>

                    </form>

                </div>
            </div>
        </div>

    </div>
</div>
@endsection
