
@extends('layouts.app')

@section('title', 'Create Staff Account')
@section('page-title', 'Create Staff Account')

@section('content')
    <div class="mb-4">
        <h1 class="page-heading">Create Staff Account</h1>
        <p class="page-description">
            Add a cashier or server to your restaurant team.
        </p>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-xl-9">
            <div class="content-card overflow-hidden">

                <div class="p-4 p-md-5"
                     style="background:linear-gradient(135deg, #173b2d, #245641);">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center"
                             style="width:54px;height:54px;background:#e9a23b;color:#173b2d;">
                            <i class="fa-solid fa-user-plus fa-xl"></i>
                        </div>
                        <div class="text-white">
                            <h4 class="fw-bold mb-1">New Team Member</h4>
                            <p class="mb-0 small" style="color:#d5e4db;">
                                Set up staff access to EES Restaurant Manager.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="p-4 p-md-5">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <div class="fw-semibold mb-2">
                                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                                Please correct the following errors:
                            </div>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('users.store') }}">
                        @csrf

                        <h6 class="fw-bold mb-3" style="color:#173b2d;">
                            <i class="fa-solid fa-id-card me-2"></i>
                            Personal Information
                        </h6>

                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label for="name" class="form-label fw-semibold">
                                    Full Name <span class="text-danger">*</span>
                                </label>
                                <input
                                    id="name"
                                    name="name"
                                    type="text"
                                    class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name') }}"
                                    placeholder="Enter staff member's full name"
                                    autocomplete="name"
                                    required
                                >
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="email" class="form-label fw-semibold">
                                    Email Address <span class="text-danger">*</span>
                                </label>
                                <input
                                    id="email"
                                    name="email"
                                    type="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email') }}"
                                    placeholder="staff@example.com"
                                    autocomplete="email"
                                    required
                                >
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    This email will be used to sign in.
                                </div>
                            </div>
                        </div>

                        <hr class="my-4" style="border-color:#e5ebe7;">

                        <h6 class="fw-bold mb-3" style="color:#173b2d;">
                            <i class="fa-solid fa-shield-halved me-2"></i>
                            Account Access
                        </h6>

                        <div class="mb-4">
                            <label for="role" class="form-label fw-semibold">
                                Staff Role <span class="text-danger">*</span>
                            </label>
                            <select
                                id="role"
                                name="role"
                                class="form-select @error('role') is-invalid @enderror"
                                required
                            >
                                <option value="">Select a role</option>
                                <option value="cashier" @selected(old('role') === 'cashier')>
                                    Cashier — orders and payments
                                </option>
                                <option value="server" @selected(old('role') === 'server')>
                                    Server — receipt verification and food service
                                </option>
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="password" class="form-label fw-semibold">
                                    Password <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input
                                        id="password"
                                        name="password"
                                        type="password"
                                        class="form-control @error('password') is-invalid @enderror"
                                        placeholder="Minimum 8 characters"
                                        minlength="8"
                                        autocomplete="new-password"
                                        required
                                    >
                                    <button class="btn btn-outline-secondary"
                                            type="button"
                                            onclick="togglePassword('password', this)"
                                            aria-label="Show or hide password">
                                        <i class="fa-regular fa-eye"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="password_confirmation"
                                       class="form-label fw-semibold">
                                    Confirm Password <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input
                                        id="password_confirmation"
                                        name="password_confirmation"
                                        type="password"
                                        class="form-control"
                                        placeholder="Re-enter password"
                                        minlength="8"
                                        autocomplete="new-password"
                                        required
                                    >
                                    <button class="btn btn-outline-secondary"
                                            type="button"
                                            onclick="togglePassword('password_confirmation', this)"
                                            aria-label="Show or hide password confirmation">
                                        <i class="fa-regular fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-3 p-3 mt-4 mb-4"
                             style="background:#f5f7f6;border:1px solid #e5ebe7;">
                            <div class="d-flex gap-3">
                                <i class="fa-solid fa-circle-info mt-1"
                                   style="color:#245641;"></i>
                                <div class="small text-muted">
                                    <strong class="d-block mb-1" style="color:#173b2d;">
                                        Account security
                                    </strong>
                                    Passwords are stored securely as hashes.
                                    Only create accounts for authorized restaurant staff.
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap justify-content-end gap-2 pt-3 border-top">
                            <a href="{{ route('users.index') }}"
                               class="btn btn-outline-secondary px-4">
                                <i class="fa-solid fa-arrow-left me-2"></i>
                                Cancel
                            </a>

                            <button type="submit" class="btn btn-success px-4">
                                <i class="fa-solid fa-user-check me-2"></i>
                                Create Account
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <p class="text-muted small mt-3">
                <i class="fa-solid fa-lock me-1"></i>
                User account creation is restricted to the restaurant owner.
            </p>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function togglePassword(inputId, button) {
        const input = document.getElementById(inputId);
        const icon = button.querySelector('i');

        if (!input || !icon) return;

        const showPassword = input.type === 'password';
        input.type = showPassword ? 'text' : 'password';

        icon.classList.toggle('fa-eye', !showPassword);
        icon.classList.toggle('fa-eye-slash', showPassword);
    }
</script>
@endpush
