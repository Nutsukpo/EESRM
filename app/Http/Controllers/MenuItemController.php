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
    /**
     * Restrict menu management to the restaurant owner.
     */
    private function requireOwner(): void
    {
        abort_unless(
            auth()->check() && auth()->user()->role === 'owner',
            403,
            'Only the owner can manage menu items and categories.'
        );
    }


    public function index(): View
    {
        $this->requireOwner();
        $items = MenuItem::with('category')
            ->orderBy('name')
            ->paginate(10);

        return view('menu-items.index', compact('items'));
    }

    public function create(): View
    {
        $this->requireOwner();
        $categories = MenuCategory::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('menu-items.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->requireOwner();
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
        $this->requireOwner();
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
        $this->requireOwner();
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
        $this->requireOwner();
        $menu_item->update([
            'is_available' => ! $menu_item->is_available,
        ]);

        return redirect()
            ->route('menu-items.index')
            ->with('status', 'Menu item availability updated.');
    }
}
