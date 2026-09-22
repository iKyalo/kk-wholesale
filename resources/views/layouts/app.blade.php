<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} @hasSection('title') - @yield('title') @endif</title>

    <!-- Bootstrap 5 CSS -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous"
    >

    <!-- Bootstrap Icons -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet"
    >

    <style>
        :root {
            --sidebar-width: 260px;
            --sidebar-collapsed-width: 72px;
            --navbar-height: 60px;
        }

        body {
            padding-top: var(--navbar-height);
            background-color: #f4f6f9;
        }

        /* ---------- Top Navbar ---------- */
        .app-navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: var(--navbar-height);
            z-index: 1030;
            border-bottom: 1px solid #e5e7eb;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .04);
        }

        .btn-icon {
            background: transparent;
            border: none;
            color: #495057;
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: .375rem;
        }

        .btn-icon:hover {
            background-color: #f1f3f5;
        }

        /* ---------- Sidebar ---------- */
        .app-sidebar {
            position: fixed;
            top: var(--navbar-height);
            left: 0;
            bottom: 0;
            width: var(--sidebar-width);
            background: #fff;
            border-right: 1px solid #e5e7eb;
            z-index: 1020;
            transition: width .2s ease, transform .2s ease;
            overflow: hidden;
        }

        .sidebar-scroll {
            height: 100%;
            overflow-y: auto;
            overflow-x: hidden;
            padding: .75rem .5rem;
            display: flex;
            flex-direction: column;
        }

        .sidebar-nav {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .sidebar-nav .nav-link {
            display: flex;
            align-items: center;
            gap: .75rem;
            padding: .55rem .75rem;
            border-radius: .375rem;
            color: #495057;
            font-size: .9rem;
            white-space: nowrap;
        }

        .sidebar-nav .nav-link i {
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
            flex-shrink: 0;
        }

        .sidebar-nav .nav-link:hover {
            background-color: #f1f3f5;
            color: #212529;
        }

        .sidebar-nav .nav-link.active {
            background-color: rgba(13, 110, 253, .1);
            color: #0d6efd;
            font-weight: 600;
        }

        .submenu {
            padding-left: 2.1rem;
        }

        .submenu .nav-link {
            padding: .4rem .5rem;
            font-size: .85rem;
        }

        .submenu-caret {
            transition: transform .2s ease;
            font-size: .75rem;
        }

        .submenu-toggle[aria-expanded="true"] .submenu-caret {
            transform: rotate(180deg);
        }

        /* Collapsed state (desktop) */
        body.sidebar-collapsed .app-sidebar {
            width: var(--sidebar-collapsed-width);
        }

        body.sidebar-collapsed .app-sidebar .nav-label,
        body.sidebar-collapsed .app-sidebar .submenu-caret,
        body.sidebar-collapsed .app-sidebar .submenu {
            display: none;
        }

        body.sidebar-collapsed .app-sidebar .sidebar-nav .nav-link {
            justify-content: center;
            padding: .55rem;
        }

        body.sidebar-collapsed .main-content {
            margin-left: var(--sidebar-collapsed-width);
        }

        /* ---------- Main content ---------- */
        .main-content {
            margin-left: var(--sidebar-width);
            transition: margin-left .2s ease;
            min-height: calc(100vh - var(--navbar-height));
        }

        /* ---------- Mobile offcanvas behavior ---------- */
        .sidebar-backdrop {
            display: none;
            position: fixed;
            top: var(--navbar-height);
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, .4);
            z-index: 1015;
        }

        @media (max-width: 991.98px) {
            .app-sidebar {
                width: var(--sidebar-width) !important;
                transform: translateX(-100%);
            }

            body.sidebar-mobile-open .app-sidebar {
                transform: translateX(0);
            }

            body.sidebar-mobile-open .sidebar-backdrop {
                display: block;
            }

            /* Labels always visible while the mobile sidebar is open */
            body.sidebar-collapsed .app-sidebar .nav-label,
            body.sidebar-collapsed .app-sidebar .submenu-caret {
                display: inline;
            }

            body.sidebar-collapsed .app-sidebar .submenu.show {
                display: block;
            }

            .main-content,
            body.sidebar-collapsed .main-content {
                margin-left: 0 !important;
            }
        }
    </style>

    @stack('styles')
</head>
<body>

    @include('layouts.partials.navbar')

    <div class="app-wrapper d-flex">
        @include('layouts.partials.sidebar')

        <main id="mainContent" class="main-content flex-grow-1">
            <div class="container-fluid py-4 px-3 px-md-4">
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Bootstrap 5 JS Bundle (includes Popper) -->
    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"
    ></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const body = document.body;
            const sidebar = document.getElementById('appSidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            const toggleButton = document.getElementById('sidebarToggle');
            const STORAGE_KEY = 'sidebarCollapsed';

            function isDesktop() {
                return window.innerWidth >= 992;
            }

            function refreshTooltips() {
                const triggers = sidebar.querySelectorAll('[data-bs-toggle="tooltip"]');

                triggers.forEach(function (el) {
                    const existing = bootstrap.Tooltip.getInstance(el);
                    if (existing) {
                        existing.dispose();
                    }
                });

                // Tooltips are only useful once labels are hidden (collapsed desktop sidebar).
                if (isDesktop() && body.classList.contains('sidebar-collapsed')) {
                    triggers.forEach(function (el) {
                        new bootstrap.Tooltip(el);
                    });
                }
            }

            function applyStoredPreference() {
                const collapsed = localStorage.getItem(STORAGE_KEY) === 'true';
                if (isDesktop() && collapsed) {
                    body.classList.add('sidebar-collapsed');
                }
            }

            function toggleSidebar() {
                if (isDesktop()) {
                    body.classList.toggle('sidebar-collapsed');
                    localStorage.setItem(STORAGE_KEY, body.classList.contains('sidebar-collapsed'));
                } else {
                    body.classList.toggle('sidebar-mobile-open');
                }
                refreshTooltips();
            }

            function closeMobileSidebar() {
                body.classList.remove('sidebar-mobile-open');
            }

            toggleButton.addEventListener('click', toggleSidebar);
            backdrop.addEventListener('click', closeMobileSidebar);

            // Close the mobile sidebar after navigating to a real page link.
            sidebar.querySelectorAll('.nav-link:not(.submenu-toggle)').forEach(function (link) {
                link.addEventListener('click', function () {
                    if (!isDesktop()) {
                        closeMobileSidebar();
                    }
                });
            });

            window.addEventListener('resize', function () {
                if (isDesktop()) {
                    closeMobileSidebar();
                }
            });

            applyStoredPreference();
            refreshTooltips();
        });
    </script>

    @stack('scripts')
</body>
</html>
