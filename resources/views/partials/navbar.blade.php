
<nav class="app-navbar">
    {{-- Left: Mobile toggle and page title --}}
    <div class="d-flex align-items-center gap-3">
        <button type="button"
                class="btn navbar-menu-toggle d-lg-none"
                id="sidebarToggle"
                aria-label="Open navigation menu">
            <i class="fa-solid fa-bars"></i>
        </button>

        <div class="navbar-page-info">
            <h5 class="navbar-heading mb-0">
                @yield('page-title', 'Dashboard')
            </h5>

            <div class="navbar-subheading">
                <span class="navbar-breadcrumb-home">EES Restaurant</span>
                <i class="fa-solid fa-chevron-right mx-1"></i>
                @yield('page-title', 'Dashboard')
            </div>
        </div>
    </div>

    {{-- Right: System status, user profile and logout --}}
    <div class="d-flex align-items-center gap-3">
        <div class="navbar-system-status d-none d-xl-flex align-items-center gap-2">
            <span class="status-indicator"></span>
            <span>System Online</span>
        </div>

        <div class="navbar-divider d-none d-md-block"></div>

        @auth
            <div class="dropdown">
                <button class="navbar-profile dropdown-toggle"
                        type="button"
                        id="userProfileDropdown"
                        data-bs-toggle="dropdown"
                        aria-expanded="false">
                    <div class="user-avatar">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                    </div>

                    <div class="navbar-user-details text-start d-none d-sm-block">
                        <div class="navbar-user-name">
                            {{ auth()->user()->name }}
                        </div>
                        <div class="navbar-user-role">
                            <i class="fa-solid fa-shield-halved me-1"></i>
                            {{ ucfirst(auth()->user()->role ?? 'Staff') }}
                        </div>
                    </div>

                    <i class="fa-solid fa-chevron-down navbar-dropdown-icon"></i>
                </button>

                <ul class="dropdown-menu dropdown-menu-end navbar-profile-menu shadow"
                    aria-labelledby="userProfileDropdown">

                    <li class="px-3 py-2">
                        <div class="fw-semibold">{{ auth()->user()->name }}</div>
                        <small class="text-muted">{{ auth()->user()->email }}</small>
                    </li>

                    <li><hr class="dropdown-divider"></li>

                    <li>
                        <div class="px-3 py-2 small text-muted">
                            <i class="fa-solid fa-circle-check me-2"
                               style="color:#245641;"></i>
                            Signed in to EES Restaurant Manager
                        </div>
                    </li>

                    <li><hr class="dropdown-divider"></li>

                    <li>
                        <button type="button"
                                class="dropdown-item text-danger py-2"
                                data-bs-toggle="modal"
                                data-bs-target="#logoutConfirmationModal">
                            <i class="fa-solid fa-right-from-bracket me-2"></i>
                            Sign out
                        </button>
                    </li>
                </ul>
            </div>
        @else
            <div class="user-avatar">
                <i class="fa-solid fa-user"></i>
            </div>
        @endauth
    </div>
</nav>

{{-- Logout confirmation modal --}}
@auth
    <div class="modal fade"
         id="logoutConfirmationModal"
         tabindex="-1"
         aria-labelledby="logoutConfirmationTitle"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content logout-modal-content">

                <div class="modal-body text-center p-4">
                    <div class="logout-modal-icon mx-auto mb-3">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </div>

                    <h5 class="fw-bold mb-2" id="logoutConfirmationTitle">
                        Sign out?
                    </h5>

                    <p class="text-muted mb-4">
                        Are you sure you want to sign out of EES Restaurant Manager?
                        You will need to log in again to access your account.
                    </p>

                    <div class="d-grid gap-2">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="btn btn-danger w-100">
                                <i class="fa-solid fa-right-from-bracket me-2"></i>
                                Yes, sign me out
                            </button>
                        </form>

                        <button type="button"
                                class="btn btn-light border"
                                data-bs-dismiss="modal">
                            No, stay signed in
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endauth

<style>
    .app-navbar {
        height: 76px;
        position: sticky;
        top: 0;
        z-index: 1020;
        background: #fff;
        border-bottom: 1px solid #e8ece9;
        padding: 0 28px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }

    .navbar-heading {
        color: #173b2d;
        font-size: 18px;
        font-weight: 750;
        letter-spacing: -.3px;
    }

    .navbar-subheading {
        margin-top: 4px;
        color: #87928b;
        font-size: 11px;
    }

    .navbar-breadcrumb-home {
        color: #64756a;
    }

    .navbar-subheading .fa-chevron-right {
        font-size: 8px;
        color: #a6b0a9;
    }

    .navbar-menu-toggle {
        width: 40px;
        height: 40px;
        border: 1px solid #e4eae6;
        background: #f5f7f6;
        color: #173b2d;
        border-radius: 9px;
    }

    .navbar-menu-toggle:hover {
        background: #e4eee8;
        color: #173b2d;
    }

    .navbar-system-status {
        font-size: 11px;
        font-weight: 600;
        color: #64756a;
        white-space: nowrap;
    }

    .status-indicator {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #279765;
        box-shadow: 0 0 0 3px #e4f3eb;
    }

    .navbar-divider {
        height: 34px;
        width: 1px;
        background: #e8ece9;
    }

    .navbar-profile {
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 5px 7px;
        border: 1px solid transparent;
        border-radius: 10px;
        background: transparent;
        transition: background .2s, border-color .2s;
    }

    .navbar-profile:hover,
    .navbar-profile.show {
        background: #f5f7f6;
        border-color: #e4eae6;
    }

    .navbar-profile.dropdown-toggle::after {
        display: none;
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        flex-shrink: 0;
        display: grid;
        place-items: center;
        border-radius: 12px;
        background: #e4eee8;
        color: #173b2d;
        font-size: 15px;
        font-weight: 800;
        border: 1px solid #d7e5dc;
    }

    .navbar-user-name {
        max-width: 180px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        color: #26372e;
        font-size: 12px;
        font-weight: 700;
    }

    .navbar-user-role {
        margin-top: 3px;
        color: #78867d;
        font-size: 11px;
    }

    .navbar-dropdown-icon {
        margin: 0 5px;
        color: #8b978f;
        font-size: 10px;
    }

    .navbar-profile-menu {
        min-width: 260px;
        margin-top: 10px !important;
        padding: 8px;
        border: 1px solid #e6ece8;
        border-radius: 12px;
    }

    .navbar-profile-menu .dropdown-item {
        border-radius: 7px;
        font-size: 13px;
    }

    .navbar-profile-menu .dropdown-item:hover {
        background: #f5f7f6;
    }

    .logout-modal-content {
        border: 1px solid #e8ece9;
        border-radius: 16px;
        box-shadow: 0 15px 50px rgba(23, 59, 45, .14);
    }

    .logout-modal-icon {
        width: 58px;
        height: 58px;
        display: grid;
        place-items: center;
        border-radius: 50%;
        background: #fff0ef;
        color: #c0392b;
        font-size: 22px;
    }

    .logout-modal-content .btn {
        padding: 10px 14px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
    }

    @media (max-width: 575.98px) {
        .app-navbar {
            height: 68px;
            padding: 0 12px;
            gap: 8px;
        }

        .navbar-heading {
            font-size: 16px;
        }

        .navbar-subheading {
            font-size: 10px;
        }

        .navbar-profile {
            padding: 3px;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
        }

        .navbar-dropdown-icon {
            margin: 0 2px;
        }
    }
</style>
