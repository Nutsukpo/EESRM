<?php

namespace App\Http\Controllers;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class PosController extends Controller
{
    public function index(): View
    {
        $categories = MenuCategory::query()
            ->where('is_active', true)
            ->with([
                'menuItems' => function ($query) {
                    $query->where('is_available', true)
                        ->orderBy('name');
                },
            ])
            ->orderBy('name')
            ->get();

        return view('pos.index', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $decodedItems = json_decode(
            $request->input('items_json', ''),
            true
        );

        if (! is_array($decodedItems)) {
            throw ValidationException::withMessages([
                'items' => 'Your order is invalid. Please select your items again.',
            ]);
        }

        $input = [
            'order_type' => $request->input('order_type'),
            'items' => $decodedItems,
        ];

        $validated = Validator::make($input, [
            'order_type' => [
                'required',
                'in:dine_in,takeaway',
            ],
            'items' => [
                'required',
                'array',
                'min:1',
                'max:100',
            ],
            'items.*.id' => [
                'required',
                'integer',
                'distinct',
                'min:1',
            ],
            'items.*.quantity' => [
                'required',
                'integer',
                'min:1',
                'max:100',
            ],
        ])->validate();

        $order = DB::transaction(function () use ($validated) {
            $requestedItems = collect($validated['items'])
                ->keyBy('id');

            $menuItems = MenuItem::query()
                ->with('category')
                ->whereIn('id', $requestedItems->keys())
                ->where('is_available', true)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($menuItems->count() !== $requestedItems->count()) {
                throw ValidationException::withMessages([
                    'items' => 'One or more menu items are unavailable. Refresh and try again.',
                ]);
            }

            foreach ($menuItems as $menuItem) {
                if (! $menuItem->category ||
                    ! $menuItem->category->is_active) {
                    throw ValidationException::withMessages([
                        'items' => "The category for {$menuItem->name} is inactive.",
                    ]);
                }
            }

            $subtotal = 0;

            foreach ($requestedItems as $id => $requested) {
                $menuItem = $menuItems->get($id);

                $subtotal += (int) round(
                    (float) $menuItem->price * 100
                ) * (int) $requested['quantity'];
            }

            $order = Order::create([
                'order_number' => 'ORD-'
                    . now()->format('ymd')
                    . '-'
                    . Str::upper(Str::random(8)),
                'cashier_id' => auth()->id(),
                'order_type' => $validated['order_type'],
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'subtotal' => $subtotal / 100,
                'discount' => 0,
                'tax' => 0,
                'total' => $subtotal / 100,
            ]);

            foreach ($requestedItems as $id => $requested) {
                $menuItem = $menuItems->get($id);
                $quantity = (int) $requested['quantity'];
                $unitPrice = (int) round(
                    (float) $menuItem->price * 100
                );

                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $menuItem->id,
                    'item_name' => $menuItem->name,
                    'unit_price' => $unitPrice / 100,
                    'quantity' => $quantity,
                    'subtotal' => ($unitPrice * $quantity) / 100,
                ]);
            }

            return $order;
        }, 3);

        return redirect()
            ->route('pos.index')
            ->with(
                'status',
                "Order {$order->order_number} created successfully. Total: GH₵"
                    . number_format((float) $order->total, 2)
                    . '. Awaiting payment.'
            );
    }
}
