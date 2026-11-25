<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class UserController extends Controller
{
    private function requireOwner(): void
    {
        abort_unless(
            auth()->check() && auth()->user()->role === 'owner',
            403,
            'Only the owner can manage user accounts.'
        );
    }

    public function index(): View
    {
        $this->requireOwner();

        $users = User::orderBy('name')->paginate(10);

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        $this->requireOwner();

        return view('users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->requireOwner();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'role' => [
                'required',
                Rule::in(['cashier', 'server']),
            ],
            'password' => [
                'required',
                'confirmed',
                Password::min(8),
            ],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()
            ->route('users.index')
            ->with('status', 'Staff account created successfully.');
    }
}
