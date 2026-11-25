<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $isOwner = $user->role === 'owner';

        $orders = Order::query()
            ->with('cashier')
            ->when(
                !$isOwner,
                fn ($query) => $query->where('cashier_id', $user->id)
            )
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->search);

                $query->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                        ->orWhereHas('cashier', function ($cashier) use ($search) {
                            $cashier->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('payment_status'), fn ($q) =>
                $q->where('payment_status', $request->payment_status)
            )
            ->when($request->filled('status'), fn ($q) =>
                $q->where('status', $request->status)
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $this->authorizeOrderAccess($order);

        $order->load(['cashier', 'server', 'items.menuItem', 'payments']);

        return view('orders.show', compact('order'));
    }

    public function edit(Order $order): View|RedirectResponse
    {
        $this->authorizeOrderAccess($order);

        if (!$this->isEditable($order)) {
            return redirect()
                ->route('orders.show', $order)
                ->with('error', 'This order can no longer be edited.');
        }

        $order->load('items');

        $categories = MenuCategory::query()
            ->where('is_active', true)
            ->with(['menuItems' => fn ($q) =>
                $q->where('is_available', true)->orderBy('name')
            ])
            ->orderBy('name')
            ->get();

        return view('orders.edit', compact('order', 'categories'));
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        // Check ownership before processing the submitted changes.
        $this->authorizeOrderAccess($order);

        $input = [
            'order_type' => $request->input('order_type'),
            'items' => json_decode($request->input('items_json', ''), true),
        ];

        $validated = Validator::make($input, [
            'order_type' => ['required', 'in:dine_in,takeaway'],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.id' => ['required', 'integer', 'distinct', 'min:1'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100'],
        ])->validate();

        DB::transaction(function () use ($order, $validated) {
            $lockedOrder = Order::query()
                ->lockForUpdate()
                ->findOrFail($order->id);

            // Recheck ownership against the locked database record.
            $this->authorizeOrderAccess($lockedOrder);

            if (!$this->isEditable($lockedOrder)) {
                throw ValidationException::withMessages([
                    'order' => 'This order is no longer editable.',
                ]);
            }

            $requestedItems = collect($validated['items'])->keyBy('id');

            $menuItems = MenuItem::query()
                ->whereIn('id', $requestedItems->keys())
                ->where('is_available', true)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($menuItems->count() !== $requestedItems->count()) {
                throw ValidationException::withMessages([
                    'items' => 'One or more selected menu items are unavailable.',
                ]);
            }

            foreach ($menuItems as $menuItem) {
                if (!$menuItem->category()->where('is_active', true)->exists()) {
                    throw ValidationException::withMessages([
                        'items' => "The category for {$menuItem->name} is inactive.",
                    ]);
                }
            }

            $oldValues = [
                'order_type' => $lockedOrder->order_type,
                'total' => $lockedOrder->total,
                'items' => $lockedOrder->items()->get()->map(fn ($item) => [
                    'name' => $item->item_name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                ])->all(),
            ];

            $totalPesewas = 0;

            foreach ($requestedItems as $id => $requested) {
                $menuItem = $menuItems->get($id);
                $unitPrice = (int) round((float) $menuItem->price * 100);
                $totalPesewas += $unitPrice * (int) $requested['quantity'];
            }

            $lockedOrder->items()->delete();

            foreach ($requestedItems as $id => $requested) {
                $menuItem = $menuItems->get($id);
                $quantity = (int) $requested['quantity'];
                $unitPrice = (int) round((float) $menuItem->price * 100);

                OrderItem::create([
                    'order_id' => $lockedOrder->id,
                    'menu_item_id' => $menuItem->id,
                    'item_name' => $menuItem->name,
                    'unit_price' => $unitPrice / 100,
                    'quantity' => $quantity,
                    'subtotal' => ($unitPrice * $quantity) / 100,
                ]);
            }

            $lockedOrder->update([
                'order_type' => $validated['order_type'],
                'subtotal' => $totalPesewas / 100,
                'discount' => 0,
                'tax' => 0,
                'total' => $totalPesewas / 100,
            ]);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'order_updated',
                'auditable_type' => Order::class,
                'auditable_id' => $lockedOrder->id,
                'description' => "Updated order {$lockedOrder->order_number}",
                'old_values' => $oldValues,
                'new_values' => [
                    'order_type' => $lockedOrder->order_type,
                    'total' => $lockedOrder->total,
                    'items' => $lockedOrder->items()->get()->map(fn ($item) => [
                        'name' => $item->item_name,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                    ])->all(),
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }, 3);

        return redirect()
            ->route('orders.show', $order)
            ->with('status', 'Order updated successfully.');
    }

    /**
     * Owners can access every order.
     * Other users can access only orders assigned to their cashier account.
     */
    private function authorizeOrderAccess(Order $order): void
    {
        $user = auth()->user();

        abort_unless(
            $user && (
                $user->role === 'owner'
                || (int) $order->cashier_id === (int) $user->id
            ),
            403,
            'You are not authorized to access this order.'
        );
    }

    private function isEditable(Order $order): bool
    {
        return $order->payment_status === 'unpaid'
            && !in_array($order->status, ['served', 'cancelled'], true);
    }
}
