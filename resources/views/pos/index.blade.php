@extends('layouts.app')

@section('title', 'Cashier POS')

@section('content')
<div class="container-fluid py-4">

    {{-- Page Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="fas fa-cash-register me-2 text-success"></i>
                Cashier POS
            </h2>
            <p class="text-muted mb-0">
                Create and manage dine-in and takeaway orders.
            </p>
        </div>

        <span class="badge rounded-pill px-3 py-2"
              style="background:#e8f5ed;color:#176b43;">
            <i class="fas fa-circle-check me-1"></i>
            Point of Sale
        </span>
    </div>

    {{-- Flash Messages --}}
    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"
                    aria-label="Close"></button>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"
                    aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <div class="fw-bold mb-1">
                <i class="fas fa-exclamation-circle me-2"></i>
                Please correct the following:
            </div>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4">

        {{-- Menu Section --}}
        <div class="col-xl-8 col-lg-7">

            {{-- Search --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <label for="menuSearch" class="form-label fw-semibold">
                        <i class="fas fa-search me-2 text-success"></i>
                        Search menu
                    </label>

                    <input
                        type="search"
                        id="menuSearch"
                        class="form-control"
                        placeholder="Search by food name or description..."
                        autocomplete="off"
                    >
                </div>
            </div>

            @php
                $hasMenuItems = $categories->contains(
                    fn ($category) => $category->menuItems->isNotEmpty()
                );
            @endphp

            @if ($hasMenuItems)
                @foreach ($categories as $category)
                    @if ($category->menuItems->isNotEmpty())
                        <section class="menu-category mb-4"
                                 data-category="{{ strtolower($category->name) }}">

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="fw-bold mb-0">
                                    {{ $category->name }}
                                </h5>

                                <span class="badge bg-light text-dark border">
                                    {{ $category->menuItems->count() }}
                                    {{ Str::plural('item', $category->menuItems->count()) }}
                                </span>
                            </div>

                            <div class="row g-3">
                                @foreach ($category->menuItems as $item)
                                    <div
                                        class="col-sm-6 col-xl-4 menu-product"
                                        data-search="{{ strtolower($item->name . ' ' . ($item->description ?? '') . ' ' . $category->name) }}"
                                    >
                                        <div class="card h-100 border-0 shadow-sm menu-product-card">
                                            <div class="card-body d-flex flex-column">

                                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                                    <span class="small text-muted">
                                                        {{ $category->name }}
                                                    </span>

                                                    <i class="fas fa-utensils text-success"></i>
                                                </div>

                                                <h6 class="fw-bold mb-2">
                                                    {{ $item->name }}
                                                </h6>

                                                @if ($item->description)
                                                    <p class="small text-muted mb-3">
                                                        {{ $item->description }}
                                                    </p>
                                                @else
                                                    <div class="flex-grow-1"></div>
                                                @endif

                                                <div class="d-flex justify-content-between align-items-center gap-2 mt-auto">
                                                    <strong class="text-success">
                                                        GH₵{{ number_format((float) $item->price, 2) }}
                                                    </strong>

                                                    <button
                                                        type="button"
                                                        class="btn btn-success btn-sm add-item"
                                                        data-id="{{ $item->id }}"
                                                        data-name="{{ $item->name }}"
                                                        data-price="{{ $item->price }}"
                                                    >
                                                        <i class="fas fa-plus me-1"></i>
                                                        Add
                                                    </button>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif
                @endforeach

                <div id="noSearchResults" class="alert alert-info d-none">
                    No menu items match your search.
                </div>
            @else
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-utensils fa-3x text-muted mb-3"></i>
                        <h5 class="fw-bold">No menu items available</h5>
                        <p class="text-muted mb-0">
                            Activate a menu category and add available menu items
                            before creating an order.
                        </p>
                    </div>
                </div>
            @endif
        </div>

        {{-- Order Cart --}}
        <div class="col-xl-4 col-lg-5">
            <div class="card border-0 shadow-sm pos-cart">

                <div class="card-header bg-white border-0 pt-4 px-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">
                            <i class="fas fa-receipt me-2 text-success"></i>
                            Current Order
                        </h5>

                        <span id="cartCount" class="badge rounded-pill bg-success">
                            0 items
                        </span>
                    </div>
                </div>

                <div class="card-body px-4">

                    {{-- Order Submission Form --}}
                    <form
                        id="posOrderForm"
                        method="POST"
                        action="{{ route('pos.store') }}"
                    >
                        @csrf

                        <input
                            type="hidden"
                            name="items_json"
                            id="itemsJson"
                        >

                        <div class="mb-4">
                            <label for="orderType" class="form-label fw-semibold">
                                Order type
                            </label>

                            <select
                                id="orderType"
                                name="order_type"
                                class="form-select"
                                required
                            >
                                <option value="dine_in"
                                    {{ old('order_type') === 'dine_in' ? 'selected' : '' }}>
                                    Dine-in
                                </option>

                                <option value="takeaway"
                                    {{ old('order_type', 'takeaway') === 'takeaway' ? 'selected' : '' }}>
                                    Takeaway
                                </option>
                            </select>
                        </div>

                        {{-- Empty Cart --}}
                        <div id="emptyCart" class="text-center py-4">
                            <div class="mb-3">
                                <i class="fas fa-shopping-basket fa-3x text-muted"></i>
                            </div>

                            <h6 class="fw-bold">Your cart is empty</h6>

                            <p class="small text-muted mb-0">
                                Select menu items to begin an order.
                            </p>
                        </div>

                        {{-- JavaScript renders selected items here --}}
                        <div id="cartItems"></div>

                        <div class="alert alert-warning py-2 small d-none"
                             id="cartLimitWarning">
                            Maximum quantity per item is 100.
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Subtotal</span>
                            <span id="cartSubtotal">GH₵0.00</span>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fw-bold fs-5">Total</span>

                            <strong
                                id="cartTotal"
                                class="fs-4 text-success"
                            >
                                GH₵0.00
                            </strong>
                        </div>

                        <div class="d-grid gap-2">
                            <button
                                type="button"
                                id="clearCart"
                                class="btn btn-outline-secondary"
                            >
                                <i class="fas fa-trash-alt me-2"></i>
                                Clear order
                            </button>

                            <button
                                type="submit"
                                id="submitOrder"
                                class="btn btn-success py-2"
                                disabled
                            >
                                <i class="fas fa-check-circle me-2"></i>
                                Create order
                            </button>
                        </div>

                        <div class="small text-muted mt-3">
                            <i class="fas fa-shield-alt me-1"></i>
                            Prices and totals will be validated by the server.
                            Payment is recorded separately.
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('styles')
<style>
    .menu-product-card {
        border-radius: 12px;
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .menu-product-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 22px rgba(0, 0, 0, .08) !important;
    }

    .pos-cart {
        border-radius: 14px;
    }

    .cart-line {
        padding: 12px 0;
        border-bottom: 1px solid #edf0ed;
    }

    .cart-line:last-child {
        border-bottom: 0;
    }

    .quantity-control {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .quantity-control button {
        width: 30px;
        height: 30px;
        padding: 0;
        display: inline-flex;
        justify-content: center;
        align-items: center;
    }

    @media (min-width: 992px) {
        .pos-cart {
            position: sticky;
            top: 90px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const cart = new Map();

    const cartItems = document.getElementById('cartItems');
    const emptyCart = document.getElementById('emptyCart');
    const cartTotal = document.getElementById('cartTotal');
    const cartSubtotal = document.getElementById('cartSubtotal');
    const cartCount = document.getElementById('cartCount');
    const submitOrder = document.getElementById('submitOrder');
    const orderForm = document.getElementById('posOrderForm');
    const itemsJson = document.getElementById('itemsJson');
    const clearCart = document.getElementById('clearCart');
    const searchInput = document.getElementById('menuSearch');
    const noSearchResults = document.getElementById('noSearchResults');
    const cartLimitWarning = document.getElementById('cartLimitWarning');

    let isSubmitting = false;

    function money(amountInPesewas) {
        return 'GH₵' + (amountInPesewas / 100).toFixed(2);
    }

    function renderCart() {
        cartItems.replaceChildren();

        let totalInPesewas = 0;
        let totalQuantity = 0;

        cart.forEach(function (item) {
            const lineTotal = item.priceInPesewas * item.quantity;

            totalInPesewas += lineTotal;
            totalQuantity += item.quantity;

            const row = document.createElement('div');
            row.className = 'cart-line';

            const top = document.createElement('div');
            top.className = 'd-flex justify-content-between gap-2 mb-2';

            const details = document.createElement('div');
            details.className = 'flex-grow-1';

            const name = document.createElement('div');
            name.className = 'fw-semibold';
            name.textContent = item.name;

            const price = document.createElement('small');
            price.className = 'text-muted';
            price.textContent = money(item.priceInPesewas) + ' each';

            details.append(name, price);

            const lineTotalLabel = document.createElement('strong');
            lineTotalLabel.className = 'text-success text-nowrap';
            lineTotalLabel.textContent = money(lineTotal);

            top.append(details, lineTotalLabel);

            const bottom = document.createElement('div');
            bottom.className = 'd-flex justify-content-between align-items-center';

            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'btn btn-sm btn-link text-danger p-0';
            remove.textContent = 'Remove';
            remove.addEventListener('click', function () {
                cart.delete(item.id);
                renderCart();
            });

            const controls = document.createElement('div');
            controls.className = 'quantity-control';

            const minus = document.createElement('button');
            minus.type = 'button';
            minus.className = 'btn btn-sm btn-outline-secondary';
            minus.textContent = '−';
            minus.setAttribute('aria-label', 'Decrease ' + item.name);
            minus.addEventListener('click', function () {
                if (item.quantity > 1) {
                    item.quantity--;
                } else {
                    cart.delete(item.id);
                }

                renderCart();
            });

            const quantity = document.createElement('span');
            quantity.className = 'fw-semibold';
            quantity.textContent = item.quantity;

            const plus = document.createElement('button');
            plus.type = 'button';
            plus.className = 'btn btn-sm btn-outline-secondary';
            plus.textContent = '+';
            plus.setAttribute('aria-label', 'Increase ' + item.name);
            plus.addEventListener('click', function () {
                if (item.quantity < 100) {
                    item.quantity++;
                    cartLimitWarning.classList.add('d-none');
                } else {
                    cartLimitWarning.classList.remove('d-none');
                }

                renderCart();
            });

            controls.append(minus, quantity, plus);
            bottom.append(remove, controls);

            row.append(top, bottom);
            cartItems.append(row);
        });

        emptyCart.classList.toggle('d-none', cart.size > 0);

        cartSubtotal.textContent = money(totalInPesewas);
        cartTotal.textContent = money(totalInPesewas);
        cartCount.textContent = totalQuantity + (
            totalQuantity === 1 ? ' item' : ' items'
        );

        submitOrder.disabled = cart.size === 0 || isSubmitting;
        clearCart.disabled = isSubmitting;

        document.querySelectorAll('.add-item').forEach(function (button) {
            button.disabled = isSubmitting;
        });
    }

    document.querySelectorAll('.add-item').forEach(function (button) {
        button.addEventListener('click', function () {
            const id = Number(button.dataset.id);
            const name = button.dataset.name;
            const price = Number(button.dataset.price);

            if (!Number.isSafeInteger(id) ||
                !Number.isFinite(price) ||
                price <= 0) {
                return;
            }

            const priceInPesewas = Math.round(price * 100);
            const existing = cart.get(id);

            if (existing) {
                if (existing.quantity >= 100) {
                    cartLimitWarning.classList.remove('d-none');
                    return;
                }

                existing.quantity++;
            } else {
                cart.set(id, {
                    id: id,
                    name: name,
                    priceInPesewas: priceInPesewas,
                    quantity: 1
                });
            }

            cartLimitWarning.classList.add('d-none');
            renderCart();
        });
    });

    clearCart.addEventListener('click', function () {
        if (isSubmitting) {
            return;
        }

        cart.clear();
        cartLimitWarning.classList.add('d-none');
        renderCart();
    });

    searchInput.addEventListener('input', function () {
        const term = searchInput.value.trim().toLowerCase();
        let visibleItems = 0;

        document.querySelectorAll('.menu-product').forEach(function (card) {
            const matches = card.dataset.search.includes(term);

            card.classList.toggle('d-none', !matches);

            if (matches) {
                visibleItems++;
            }
        });

        document.querySelectorAll('.menu-category').forEach(function (section) {
            const hasVisibleItem = section.querySelector(
                '.menu-product:not(.d-none)'
            );

            section.classList.toggle('d-none', !hasVisibleItem);
        });

        noSearchResults.classList.toggle('d-none', visibleItems > 0);
    });

    orderForm.addEventListener('submit', function (event) {
        if (isSubmitting) {
            event.preventDefault();
            return;
        }

        if (cart.size === 0) {
            event.preventDefault();
            return;
        }

        const items = Array.from(cart.values()).map(function (item) {
            return {
                id: item.id,
                quantity: item.quantity
            };
        });

        itemsJson.value = JSON.stringify(items);
        isSubmitting = true;

        submitOrder.disabled = true;
        submitOrder.innerHTML =
            '<span class="spinner-border spinner-border-sm me-2" ' +
            'aria-hidden="true"></span>Creating order...';

        clearCart.disabled = true;
    });

    renderCart();
});
</script>
@endpush
