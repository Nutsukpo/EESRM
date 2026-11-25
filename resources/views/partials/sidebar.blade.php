<aside class="app-sidebar" id="appSidebar">
    <a href="{{ route('dashboard') }}"
       class="sidebar-brand text-decoration-none text-white">
        <div class="brand-mark">
            <i class="fa-solid fa-utensils"></i>
        </div>
        <div>
            <p class="brand-name">EES Restaurant</p>
            <p class="brand-subtitle">Restaurant Manager</p>
        </div>
    </a>

    <div class="sidebar-section">Main Menu</div>

    <a href="{{ route('dashboard') }}"
       class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <i class="fa-solid fa-gauge-high"></i>
        <span>Dashboard</span>
    </a>

    @if (auth()->user()?->role === 'owner' || auth()->user()?->role === 'cashier')
        <div class="sidebar-section">Sales & Orders</div>

        <a href="/pos"
           class="sidebar-link">
            <i class="fa-solid fa-cart-plus"></i>
            <span>New Order</span>
        </a>

        <a href="/orders"
           class="sidebar-link">
            <i class="fa-solid fa-receipt"></i>
            <span>Orders</span>
        </a>

        <a href="/payments"
           class="sidebar-link">
            <i class="fa-solid fa-money-bill-wave"></i>
            <span>Payments</span>
        </a>
    @endif

    <!-- @if (auth()->user()?->role === 'owner' || auth()->user()?->role === 'server')
        <div class="sidebar-section">Food Service</div>

        <a href="#"
           class="sidebar-link">
            <i class="fa-solid fa-ticket"></i>
            <span>Verify Receipt</span>
        </a>

        <a href="#"
           class="sidebar-link">
            <i class="fa-solid fa-bell-concierge"></i>
            <span>Served Orders</span>
        </a>
    @endif -->

    @if (auth()->user()?->role === 'owner')
        <div class="sidebar-section">Management</div>

        <a href="/menu-items"
           class="sidebar-link">
            <i class="fa-solid fa-bowl-food"></i>
            <span>Menu Items</span>
        </a>

        <a href="/categories"
           class="sidebar-link">
            <i class="fa-solid fa-layer-group"></i>
            <span>Categories</span>
        </a>

        <a href="{{ route('users.index') }}"
           class="sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
            <i class="fa-solid fa-users"></i>
            <span>User Accounts</span>
        </a>

        
        <div class="sidebar-section">Reports</div>

        <a href="{{ route('reports.daily-sales') }}"
        class="sidebar-link {{ request()->routeIs('reports.daily-sales') ? 'active' : '' }}">
            <i class="fa-solid fa-calendar-day"></i>
            <span>Daily Sales</span>
        </a>

        <a href="{{ route('reports.monthly-sales') }}"
        class="sidebar-link {{ request()->routeIs('reports.monthly-sales') ? 'active' : '' }}">
            <i class="fa-solid fa-calendar-days"></i>
            <span>Monthly Sales</span>
        </a>


        <a href="{{ route('activity-logs.index') }}"
           class="sidebar-link">
            <i class="fa-solid fa-clipboard-list"></i>
            <span>Activity Logs</span>
        </a>
    @endif

    <div class="sidebar-footer">
        <div class="fw-semibold text-white">EES Technologies Ghana</div>
        <div class="mt-1">Restaurant Manager · v1.0</div>
    </div>
</aside>
