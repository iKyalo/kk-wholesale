<nav class="app-navbar navbar navbar-expand navbar-light bg-white">
    <div class="d-flex align-items-center w-100 px-3">

        {{-- Sidebar toggle --}}
        <button type="button" id="sidebarToggle" class="btn btn-icon me-2" aria-label="Toggle sidebar">
            <i class="bi bi-list fs-4"></i>
        </button>

        {{-- Logo / App Name --}}
        <a href="{{ route('dashboard') }}" class="navbar-brand d-flex align-items-center me-3">
            <span class="bg-primary text-white rounded d-flex align-items-center justify-content-center me-2"
                style="width: 32px; height: 32px;">
                <i class="bi bi-box-seam"></i>
            </span>
            <span class="fw-bold d-none d-sm-inline">{{ config('app.name', 'Laravel') }}</span>
        </a>

        {{-- Page title / breadcrumb --}}
        <div class="d-none d-md-flex flex-grow-1 align-items-center">
            @hasSection('page-title')
                <span class="text-muted">|</span>
                <h1 class="h6 fw-semibold mb-0 ms-3">@yield('page-title')</h1>
            @endif

            @hasSection('breadcrumb')
                <nav aria-label="breadcrumb" class="ms-3">
                    <ol class="breadcrumb mb-0 small">
                        @yield('breadcrumb')
                    </ol>
                </nav>
            @endif
        </div>

        <div class="flex-grow-1 d-md-none"></div>

        {{-- Right-hand actions --}}
        <div class="d-flex align-items-center gap-2 ms-auto">

            {{-- User profile dropdown --}}
            <div class="dropdown">
                <button type="button" class="btn btn-icon d-flex align-items-center gap-2 px-2"
                    data-bs-toggle="dropdown" aria-expanded="false" style="margin-right: 1rem;">
                    <span
                        class="bg-secondary bg-opacity-10 text-secondary rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 32px; height: 32px;">
                        <i class="bi bi-person-fill"></i>
                    </span>
                    <span class="d-none d-lg-inline small fw-semibold">{{ auth()->user()->name ?? 'Account' }}</span>
                    <i class="bi bi-chevron-down small d-none d-lg-inline"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    <li>
                        <span class="dropdown-item-text small text-muted">
                            Signed in as<br>
                            <strong class="text-body">{{ auth()->user()->name ?? '' }}</strong>
                        </span>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ Route::has('profile.edit') ? route('profile.edit') : '#' }}">
                            <i class="bi bi-person me-2"></i> My Profile
                        </a>
                    </li>
                    {{-- <li>
                        <a class="dropdown-item"
                            href="{{ Route::has('settings.index') ? route('settings.index') : '#' }}">
                            <i class="bi bi-gear me-2"></i> Settings
                        </a>
                    </li> --}}
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i> Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>

        </div>
    </div>
</nav>
