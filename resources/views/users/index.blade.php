
@extends('layouts.app')

@section('title', 'User Accounts')
@section('page-title', 'User Accounts')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="page-heading">User Accounts</h1>
            <p class="page-description mb-0">
                Manage restaurant staff and control system access.
            </p>
        </div>

        <a href="{{ route('users.create') }}"
           class="btn btn-success px-4 py-2">
            <i class="fa-solid fa-user-plus me-2"></i>
            Create Staff Account
        </a>
    </div>

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>
            {{ session('status') }}
            <button type="button" class="btn-close"
                    data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>
            Please correct the errors in your submission.
        </div>
    @endif

    <div class="content-card overflow-hidden">
        <div class="p-3 p-md-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="fw-bold mb-1">Staff Directory</h5>
                <p class="text-muted mb-0">
                    Registered accounts and assigned roles
                </p>
            </div>

            <span class="badge rounded-pill px-3 py-2"
                  style="background:#e4eee8;color:#173b2d;">
                {{ $users->total() }} {{ Str::plural('account', $users->total()) }}
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background:#f5f7f6;">
                    <tr>
                        <th class="px-4 py-3 text-secondary small text-uppercase">Staff Member</th>
                        <th class="py-3 text-secondary small text-uppercase">Email Address</th>
                        <th class="py-3 text-secondary small text-uppercase">Role</th>
                        <th class="py-3 text-secondary small text-uppercase">Date Created</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="user-avatar">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold">
                                            {{ $user->name }}
                                        </div>
                                        <div class="text-muted small">
                                            Staff ID: {{ $user->id }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td>{{ $user->email }}</td>

                            <td>
                                @php
                                    $roleStyles = [
                                        'owner' => 'background:#e9a23b;color:#173b2d;',
                                        'cashier' => 'background:#dcebe2;color:#173b2d;',
                                        'server' => 'background:#edf0f4;color:#46515d;',
                                    ];
                                @endphp

                                <span class="badge rounded-pill px-3 py-2"
                                      style="{{ $roleStyles[$user->role] ?? 'background:#edf0f4;color:#46515d;' }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>

                            <td>
                                <div>{{ $user->created_at?->format('d M Y') ?? '—' }}</div>
                                <small class="text-muted">
                                    {{ $user->created_at?->format('h:i A') ?? '' }}
                                </small>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <div class="mb-3">
                                    <i class="fa-solid fa-users fa-2x"
                                       style="color:#245641;"></i>
                                </div>
                                <h6 class="fw-bold">No user accounts found</h6>
                                <p class="text-muted mb-3">
                                    Create a staff account to get started.
                                </p>
                                <a href="{{ route('users.create') }}"
                                   class="btn btn-success">
                                    <i class="fa-solid fa-user-plus me-2"></i>
                                    Create First Staff Account
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="p-3 border-top">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <div class="mt-3">
        <small class="text-muted">
            <i class="fa-solid fa-shield-halved me-1"></i>
            User account management is restricted to the restaurant owner.
        </small>
    </div>
@endsection
