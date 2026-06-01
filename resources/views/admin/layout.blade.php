@php
    $adminStyleVersion = is_file(public_path('admin.css'))
        ? filemtime(public_path('admin.css'))
        : time();
@endphp
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <title>{{ $pageTitle }} | Admin Karunya Printing</title>
    <link rel="stylesheet" href="/admin.css?v={{ filemtime(public_path('admin.css')) }}">
    <link rel="stylesheet" href="/css/admin/base.css?v={{ $adminStyleVersion }}">
    @yield('page-styles-admin')
    <script>const savedAdminTheme = localStorage.getItem('admin-theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'); document.documentElement.setAttribute('data-theme', savedAdminTheme);</script>
</head>
<body class="admin-body">
    <header class="header admin-topbar glass" id="admin-header">
        <div class="admin-container nav-wrap topbar-inner">
            <button class="sidebar-toggle" id="sidebar-toggle" aria-label="Toggle Sidebar" aria-expanded="true">
                <span></span><span></span><span></span>
            </button>
            <a href="/admin/dashboard.php" class="brand">
                <span class="brand-mark" aria-hidden="true">KP</span>
                <span class="brand-title wordmark header-wordmark">
                    <span class="wordmark-script">Karunya</span>
                    <span class="wordmark-sub">Admin Panel</span>
            </a>
<div class="nav-actions">
                <span class="admin-user">{{ $adminName }}</span>
                <a class="btn btn-outline btn-sm" href="/admin/settings.php">Pengaturan</a>
                <a class="btn btn-primary btn-sm" href="/" target="_blank" rel="noopener">Lihat Website</a>
                <a class="btn btn-outline btn-sm" href="/admin/logout.php">Logout</a>
                <button class="theme-toggle" aria-label="Toggle Dark Mode"></button>
            </div>
    </header>

    <aside class="admin-sidebar" id="admin-sidebar">
        <nav class="sidebar-nav">
            <a @class(['nav-item', 'active' => $currentPage === 'dashboard.php']) href="/admin/dashboard.php">
                Dashboard
            </a>
            <a @class(['nav-item', 'active' => $currentPage === 'home-slides.php']) href="/admin/home-slides.php">
                Slider Home
            </a>
            <a @class(['nav-item', 'active' => $currentPage === 'gallery.php']) href="/admin/gallery.php">
                Galeri
            </a>
            <a @class(['nav-item', 'active' => $currentPage === 'prices.php']) href="/admin/prices.php">
                Harga
            </a>
            <a @class(['nav-item', 'active' => $currentPage === 'services.php']) href="/admin/services.php">
                Layanan
            </a>
            <hr>
<a class="nav-item" href="/" target="_blank" rel="noopener">
                Lihat Website
            </a>
            <hr>
            <a @class(['nav-item', 'active' => $currentPage === 'settings.php']) href="/admin/settings.php">
                Pengaturan
            </a>
        </nav>
    </aside>
    <button class="admin-sidebar-overlay" id="admin-sidebar-overlay" type="button" aria-label="Tutup navigasi admin"></button>

    <div class="admin-container admin-layout">
        <main class="admin-main">
            <section class="admin-page-banner">
                <div>
                    <span class="admin-page-kicker">Panel Admin</span>
                    <h1>{{ $pageTitle }}</h1>
                    <p>{{ $pageSubtitle }}</p>
                </div>
            </section>

            @yield('content')
        </main>
    </div>

    <script>
    (function() {
        const html = document.documentElement;
        const toggleBtn = document.querySelector('[data-dark-toggle], .theme-toggle');
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const sidebar = document.getElementById('admin-sidebar');
        const sidebarOverlay = document.getElementById('admin-sidebar-overlay');
        const body = document.body;

        toggleBtn?.addEventListener('click', () => {
            const current = html.getAttribute('data-theme');
            const next = current === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', next);
            localStorage.setItem('admin-theme', next);
        });

        const setSidebarState = (open) => {
            if (!sidebar) {
                return;
            }
            sidebar.classList.toggle('is-open', open);
            sidebarOverlay?.classList.toggle('is-open', open);
            body.classList.toggle('admin-menu-open', open);
            sidebarToggle?.setAttribute('aria-expanded', open ? 'true' : 'false');
        };

        sidebarToggle?.addEventListener('click', () => {
            const nextOpen = !sidebar?.classList.contains('is-open');
            setSidebarState(nextOpen);
        });

        sidebarOverlay?.addEventListener('click', () => {
            setSidebarState(false);
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth > 1024) {
                setSidebarState(false);
            }
        });
    })();
    </script>
</body>
</html>
