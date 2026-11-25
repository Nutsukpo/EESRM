<?php

namespace App\Http\Controllers;

use App\Models\MenuCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class MenuCategoryController extends Controller
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
        $categories = MenuCategory::withCount('menuItems')
            ->orderBy('name')
            ->paginate(10);

        return view('categories.index', compact('categories'));
    }

    public function create(): View
    {
        $this->requireOwner();
        return view('categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->requireOwner();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:menu_categories,name'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $validated['is_active'] = true;

        MenuCategory::create($validated);

        return redirect()
            ->route('categories.index')
            ->with('status', 'Menu category created successfully.');
    }

    public function edit(MenuCategory $category): View
    {
        $this->requireOwner();
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, MenuCategory $category): RedirectResponse
    {
        $this->requireOwner();
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('menu_categories', 'name')->ignore($category->id),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $category->update($validated);

        return redirect()
            ->route('categories.index')
            ->with('status', 'Menu category updated successfully.');
    }

    public function toggleStatus(MenuCategory $category): RedirectResponse
    {
        $this->requireOwner();
        $category->update([
            'is_active' => ! $category->is_active,
        ]);

        return redirect()
            ->route('categories.index')
            ->with('status', 'Category status updated successfully.');
    }
}
