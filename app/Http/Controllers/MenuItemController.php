<?php

namespace App\Http\Controllers;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MenuItemController extends Controller
{
    public function index(): View
    {
        $items = MenuItem::with('category')
            ->orderBy('name')
            ->paginate(10);

        return view('menu-items.index', compact('items'));
    }

    public function create(): View
    {
        $categories = MenuCategory::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('menu-items.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'menu_category_id' => [
                'required',
                'integer',
                Rule::exists('menu_categories', 'id')
                    ->where('is_active', true),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
        ]);

        $validated['is_available'] = true;

        MenuItem::create($validated);

        return redirect()
            ->route('menu-items.index')
            ->with('status', 'Menu item created successfully.');
    }

    public function edit(MenuItem $menu_item): View
    {
        $categories = MenuCategory::where('is_active', true)
            ->orWhere('id', $menu_item->menu_category_id)
            ->orderBy('name')
            ->get();

        return view('menu-items.edit', [
            'item' => $menu_item,
            'categories' => $categories,
        ]);
    }

    public function update(
        Request $request,
        MenuItem $menu_item
    ): RedirectResponse {
        $validated = $request->validate([
            'menu_category_id' => [
                'required',
                'integer',
                Rule::exists('menu_categories', 'id'),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
        ]);

        $menu_item->update($validated);

        return redirect()
            ->route('menu-items.index')
            ->with('status', 'Menu item updated successfully.');
    }

    public function toggleAvailability(
        MenuItem $menu_item
    ): RedirectResponse {
        $menu_item->update([
            'is_available' => ! $menu_item->is_available,
        ]);

        return redirect()
            ->route('menu-items.index')
            ->with('status', 'Menu item availability updated.');
    }
}
