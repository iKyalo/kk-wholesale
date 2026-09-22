{{--
    Active-state helper: routeIs() accepts a wildcard pattern, e.g. 'branches.*'
    matches branches.index, branches.create, branches.edit, etc.
--}}
@php
    $isActive = function (...$patterns) {
        return request()->routeIs($patterns);
    };
@endphp

<aside id="appSidebar" class="app-sidebar">
    <div class="sidebar-scroll">
        <ul class="nav flex-column sidebar-nav">

            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link {{ $isActive('dashboard') ? 'active' : '' }}"
                    data-bs-toggle="tooltip" data-bs-placement="right" title="Dashboard">
                    <i class="bi bi-speedometer2"></i>
                    <span class="nav-label">Dashboard</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('branches.index') }}" class="nav-link {{ $isActive('branches.*') ? 'active' : '' }}"
                    data-bs-toggle="tooltip" data-bs-placement="right" title="Branches">
                    <i class="bi bi-diagram-3"></i>
                    <span class="nav-label">Branches</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('stores.index') }}" class="nav-link {{ $isActive('stores.*') ? 'active' : '' }}"
                    data-bs-toggle="tooltip" data-bs-placement="right" title="Stores">
                    <i class="bi bi-shop"></i>
                    <span class="nav-label">Stores</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ route('products.index') }}" class="nav-link {{ $isActive('products.*') ? 'active' : '' }}"
                    data-bs-toggle="tooltip" data-bs-placement="right" title="Products">
                    <i class="bi bi-box-seam"></i>
                    <span class="nav-label">Products</span>
                </a>
            </li>

            {{-- Inventory (expandable submenu) --}}
            <li class="nav-item">
                <a href="#inventorySubmenu"
                    class="nav-link submenu-toggle {{ $isActive('inventory.*') ? 'active' : '' }}"
                    data-bs-toggle="collapse" role="button"
                    aria-expanded="{{ $isActive('inventory.*') ? 'true' : 'false' }}" aria-controls="inventorySubmenu"
                    data-bs-placement="right" title="Inventory">
                    <i class="bi bi-boxes"></i>
                    <span class="nav-label">Inventory</span>
                    <i class="bi bi-chevron-down submenu-caret ms-auto"></i>
                </a>
                <div class="collapse submenu {{ $isActive('inventory.*') ? 'show' : '' }}" id="inventorySubmenu">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a href="{{ route('inventory.index') }}"
                                class="nav-link {{ $isActive('inventory.index') ? 'active' : '' }}">
                                All Inventory
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('inventory.by-store') }}"
                                class="nav-link {{ $isActive('inventory.by-store') ? 'active' : '' }}">
                                By Store
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('inventory.edit-stock') }}"
                                class="nav-link {{ $isActive('inventory.edit-stock') ? 'active' : '' }}">
                                Update Stock
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            {{-- Sales (expandable submenu) --}}
            <li class="nav-item">
                <a href="#salesSubmenu" class="nav-link submenu-toggle {{ $isActive('sales.*') ? 'active' : '' }}"
                    data-bs-toggle="collapse" role="button"
                    aria-expanded="{{ $isActive('sales.*') ? 'true' : 'false' }}" aria-controls="salesSubmenu"
                    data-bs-placement="right" title="Sales">
                    <i class="bi bi-receipt"></i>
                    <span class="nav-label">Sales</span>
                    <i class="bi bi-chevron-down submenu-caret ms-auto"></i>
                </a>
                <div class="collapse submenu {{ $isActive('sales.*') ? 'show' : '' }}" id="salesSubmenu">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a href="{{ route('sales.index') }}"
                                class="nav-link {{ $isActive('sales.index') ? 'active' : '' }}">
                                All Sales
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('sales.create') }}"
                                class="nav-link {{ $isActive('sales.create') ? 'active' : '' }}">
                                Create Sale
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('sales.by-store') }}"
                                class="nav-link {{ $isActive('sales.by-store') ? 'active' : '' }}">
                                By Store
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            {{-- Stock Transfers (expandable submenu) --}}
            <li class="nav-item">
                <a href="#transfersSubmenu"
                    class="nav-link submenu-toggle {{ $isActive('transfers.*') ? 'active' : '' }}"
                    data-bs-toggle="collapse" role="button"
                    aria-expanded="{{ $isActive('transfers.*') ? 'true' : 'false' }}" aria-controls="transfersSubmenu"
                    data-bs-placement="right" title="Stock Transfers">
                    <i class="bi bi-arrow-left-right"></i>
                    <span class="nav-label">Stock Transfers</span>
                    <i class="bi bi-chevron-down submenu-caret ms-auto"></i>
                </a>
                <div class="collapse submenu {{ $isActive('transfers.*') ? 'show' : '' }}" id="transfersSubmenu">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a href="{{ route('transfers.index') }}"
                                class="nav-link {{ $isActive('transfers.index') ? 'active' : '' }}">
                                All Transfers
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('transfers.create') }}"
                                class="nav-link {{ $isActive('transfers.create') ? 'active' : '' }}">
                                Create Transfer
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <a href="{{ route('reports.index') }}" class="nav-link {{ $isActive('reports.*') ? 'active' : '' }}"
                    data-bs-toggle="tooltip" data-bs-placement="right" title="Reports">
                    <i class="bi bi-bar-chart"></i>
                    <span class="nav-label">Reports</span>
                </a>
            </li>

            {{-- Users (expandable submenu) --}}
            <li class="nav-item">
                <a href="#usersSubmenu" class="nav-link submenu-toggle {{ $isActive('users.*') ? 'active' : '' }}"
                    data-bs-toggle="collapse" role="button"
                    aria-expanded="{{ $isActive('users.*') ? 'true' : 'false' }}" aria-controls="usersSubmenu"
                    data-bs-placement="right" title="Users">
                    <i class="bi bi-people"></i>
                    <span class="nav-label">Users</span>
                    <i class="bi bi-chevron-down submenu-caret ms-auto"></i>
                </a>
                <div class="collapse submenu {{ $isActive('users.*') ? 'show' : '' }}" id="usersSubmenu">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a href="{{ route('users.index') }}"
                                class="nav-link {{ $isActive('users.index') ? 'active' : '' }}">
                                All Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('users.create') }}"
                                class="nav-link {{ $isActive('users.create') ? 'active' : '' }}">
                                Create User
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="nav-item mt-auto">
                <a href="{{ Route::has('settings.index') ? route('settings.index') : '#' }}"
                    class="nav-link {{ $isActive('settings.*') ? 'active' : '' }}" data-bs-toggle="tooltip"
                    data-bs-placement="right" title="Settings">
                    <i class="bi bi-gear"></i>
                    <span class="nav-label">Settings</span>
                </a>
            </li>

        </ul>
    </div>
</aside>

<div id="sidebarBackdrop" class="sidebar-backdrop"></div>
