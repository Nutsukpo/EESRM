
@extends('layouts.app')

@section('title', 'Edit Order')

@section('content')
<div class="container-fluid py-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Edit Order</h2>
            <p class="text-muted mb-0">
                Order: {{ $order->order_number }}
            </p>
        </div>

        <a href="{{ route('orders.show', $order) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i> Back to Order
        </a>
    </div>

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

    @if($order->payment_status !== 'unpaid' || in_array($order->status, ['served', 'cancelled'], true))
        <div class="alert alert-warning">
            This order is no longer editable.
            <a href="{{ route('orders.show', $order) }}">Return to order details</a>.
        </div>
    @else

        @php
            $existingItems = $order->items->map(fn ($item) => [
                'id' => (int) $item->menu_item_id,
                'name' => $item->item_name,
                'price' => (int) round((float) $item->unit_price * 100),
                'quantity' => (int) $item->quantity,
            ])->values();

            $availableItems = $categories->flatMap(function ($category) {
                return $category->menuItems->map(fn ($item) => [
                    'id' => (int) $item->id,
                    'name' => $item->name,
                    'category' => $category->name,
                    'price' => (int) round((float) $item->price * 100),
                ]);
            })->values();
        @endphp

        <div class="row g-4">

            <div class="col-12 col-xl-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="fw-bold mb-0">Available Menu Items</h5>
                    </div>

                    <div class="card-body">
                        <input
                            type="search"
                            id="menuSearch"
                            class="form-control mb-3"
                            placeholder="Search menu items..."
                        >

                        @forelse($categories as $category)
                            @if($category->menuItems->isNotEmpty())
                                <div class="mb-4 menu-category">
                                    <h6 class="fw-bold border-bottom pb-2">
                                        {{ $category->name }}
                                    </h6>

                                    <div class="row g-3">
                                        @foreach($category->menuItems as $item)
                                            <div class="col-12 col-md-6 menu-product"
                                                 data-search="{{ strtolower($item->name . ' ' . $category->name) }}">
                                                <div class="border rounded-3 p-3 h-100 d-flex justify-content-between align-items-center gap-3">
                                                    <div>
                                                        <div class="fw-semibold">{{ $item->name }}</div>
                                                        <div class="text-success fw-bold">
                                                            GH₵{{ number_format((float) $item->price, 2) }}
                                                        </div>
                                                    </div>

                                                    <button
                                                        type="button"
                                                        class="btn btn-outline-success btn-sm add-item"
                                                        data-id="{{ $item->id }}"
                                                    >
                                                        <i class="fas fa-plus"></i> Add
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @empty
                            <div class="alert alert-info mb-0">
                                No available menu items were found.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-5">
                <form
                    action="{{ route('orders.update', $order) }}"
                    method="POST"
                    id="editOrderForm"
                >
                    @csrf
                    @method('PUT')

                    <input
                        type="hidden"
                        name="items_json"
                        id="itemsJson"
                    >

                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold mb-0">Update Order</h5>

                            <button type="button" id="clearCart" class="btn btn-sm btn-outline-danger">
                                Clear items
                            </button>
                        </div>

                        <div class="card-body">
                            <label for="orderType" class="form-label fw-semibold">
                                Order Type
                            </label>

                            <select name="order_type" id="orderType" class="form-select mb-4">
                                <option value="dine_in" @selected(old('order_type', $order->order_type) === 'dine_in')>
                                    Dine-in
                                </option>
                                <option value="takeaway" @selected(old('order_type', $order->order_type) === 'takeaway')>
                                    Takeaway
                                </option>
                            </select>

                            <div id="cartItems">
                                <div class="text-center text-muted py-4">
                                    Your order items will appear here.
                                </div>
                            </div>

                            <div class="alert alert-light border mt-3 mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal</span>
                                    <span id="subtotal">GH₵0.00</span>
                                </div>

                                <div class="d-flex justify-content-between mb-2">
                                    <span>Discount</span>
                                    <span>GH₵0.00</span>
                                </div>

                                <div class="d-flex justify-content-between mb-3">
                                    <span>Tax</span>
                                    <span>GH₵0.00</span>
                                </div>

                                <hr>

                                <div class="d-flex justify-content-between fs-5 fw-bold">
                                    <span>New Total</span>
                                    <span id="grandTotal" class="text-success">GH₵0.00</span>
                                </div>
                            </div>

                            <div class="alert alert-warning small">
                                <i class="fas fa-info-circle me-1"></i>
                                Saving changes replaces the order's item list and recalculates its total.
                                This screen is only available for unpaid orders.
                            </div>

                            <button type="submit" id="saveOrder" class="btn btn-success w-100">
                                <i class="fas fa-save me-2"></i> Save Order Changes
                            </button>

                            <a href="{{ route('orders.show', $order) }}"
                               class="btn btn-outline-secondary w-100 mt-2">
                                Cancel
                            </a>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    @endif
</div>

@if($order->payment_status === 'unpaid' && !in_array($order->status, ['served', 'cancelled'], true))
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const menuItems = @json($availableItems);
            const initialItems = @json($existingItems);

            const menuMap = new Map(
                menuItems.map(item => [Number(item.id), item])
            );

            const cart = new Map();

            initialItems.forEach(item => {
                const id = Number(item.id);

                // Preserve an existing order item even if it is no longer
                // offered on the current menu.
                cart.set(id, {
                    id: id,
                    name: item.name,
                    price: Number(item.price),
                    quantity: Number(item.quantity)
                });
            });

            const cartItems = document.getElementById('cartItems');
            const subtotalElement = document.getElementById('subtotal');
            const grandTotalElement = document.getElementById('grandTotal');
            const itemsJson = document.getElementById('itemsJson');
            const form = document.getElementById('editOrderForm');
            const saveButton = document.getElementById('saveOrder');

            function money(pesewas) {
                return 'GH₵' + (pesewas / 100).toLocaleString('en-GH', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            function renderCart() {
                cartItems.replaceChildren();

                if (cart.size === 0) {
                    const empty = document.createElement('p');
                    empty.className = 'text-muted text-center py-4';
                    empty.textContent = 'No items selected. Add at least one menu item.';
                    cartItems.appendChild(empty);
                }

                let totalPesewas = 0;

                cart.forEach(item => {
                    const lineTotal = item.price * item.quantity;
                    totalPesewas += lineTotal;

                    const row = document.createElement('div');
                    row.className = 'border-bottom py-3';

                    const top = document.createElement('div');
                    top.className = 'd-flex justify-content-between gap-2 mb-2';

                    const name = document.createElement('div');
                    name.className = 'fw-semibold';
                    name.textContent = item.name;

                    const line = document.createElement('div');
                    line.className = 'fw-semibold text-nowrap';
                    line.textContent = money(lineTotal);

                    top.append(name, line);

                    const bottom = document.createElement('div');
                    bottom.className = 'd-flex justify-content-between align-items-center gap-2';

                    const unitPrice = document.createElement('small');
                    unitPrice.className = 'text-muted';
                    unitPrice.textContent = money(item.price) + ' each';

                    const controls = document.createElement('div');
                    controls.className = 'd-flex align-items-center gap-2';

                    const minus = document.createElement('button');
                    minus.type = 'button';
                    minus.className = 'btn btn-sm btn-outline-secondary';
                    minus.textContent = '−';
                    minus.setAttribute('aria-label', 'Decrease quantity');
                    minus.addEventListener('click', () => changeQuantity(item.id, -1));

                    const quantity = document.createElement('span');
                    quantity.className = 'fw-semibold';
                    quantity.textContent = item.quantity;

                    const plus = document.createElement('button');
                    plus.type = 'button';
                    plus.className = 'btn btn-sm btn-outline-secondary';
                    plus.textContent = '+';
                    plus.setAttribute('aria-label', 'Increase quantity');
                    plus.addEventListener('click', () => changeQuantity(item.id, 1));

                    const remove = document.createElement('button');
                    remove.type = 'button';
                    remove.className = 'btn btn-sm btn-outline-danger';
                    remove.innerHTML = '<i class="fas fa-trash"></i>';
                    remove.setAttribute('aria-label', 'Remove item');
                    remove.addEventListener('click', () => {
                        cart.delete(item.id);
                        renderCart();
                    });

                    controls.append(minus, quantity, plus, remove);
                    bottom.append(unitPrice, controls);
                    row.append(top, bottom);
                    cartItems.appendChild(row);
                });

                subtotalElement.textContent = money(totalPesewas);
                grandTotalElement.textContent = money(totalPesewas);

                itemsJson.value = JSON.stringify(
                    Array.from(cart.values()).map(item => ({
                        id: item.id,
                        quantity: item.quantity
                    }))
                );

                saveButton.disabled = cart.size === 0;
            }

            function changeQuantity(id, change) {
                const item = cart.get(id);

                if (!item) return;

                const quantity = item.quantity + change;

                if (quantity < 1) {
                    cart.delete(id);
                } else if (quantity <= 100) {
                    item.quantity = quantity;
                }

                renderCart();
            }

            document.querySelectorAll('.add-item').forEach(button => {
                button.addEventListener('click', function () {
                    const id = Number(this.dataset.id);
                    const menuItem = menuMap.get(id);

                    if (!menuItem) return;

                    const existing = cart.get(id);

                    if (existing) {
                        if (existing.quantity >= 100) return;
                        existing.quantity++;
                    } else {
                        cart.set(id, {
                            id: id,
                            name: menuItem.name,
                            price: Number(menuItem.price),
                            quantity: 1
                        });
                    }

                    renderCart();
                });
            });

            document.getElementById('menuSearch').addEventListener('input', function () {
                const search = this.value.trim().toLowerCase();

                document.querySelectorAll('.menu-product').forEach(product => {
                    product.classList.toggle(
                        'd-none',
                        !product.dataset.search.includes(search)
                    );
                });

                document.querySelectorAll('.menu-category').forEach(category => {
                    const visibleProducts = category.querySelectorAll(
                        '.menu-product:not(.d-none)'
                    );

                    category.classList.toggle('d-none', visibleProducts.length === 0);
                });
            });

            document.getElementById('clearCart').addEventListener('click', function () {
                cart.clear();
                renderCart();
            });

            form.addEventListener('submit', function (event) {
                if (cart.size === 0) {
                    event.preventDefault();
                    alert('Please add at least one item to the order.');
                    return;
                }

                itemsJson.value = JSON.stringify(
                    Array.from(cart.values()).map(item => ({
                        id: item.id,
                        quantity: item.quantity
                    }))
                );

                saveButton.disabled = true;
                saveButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving changes...';
            });

            renderCart();
        });
    </script>
    @endpush
@endif
@endsection
