@php
    $adminStyleVersion = is_file(public_path('css/admin/base.css'))
        ? filemtime(public_path('css/admin/base.css'))
        : time();
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <title>Login Admin | Karunya Printing</title>
    <link rel="stylesheet" href="/admin.css?v={{ filemtime(public_path('admin.css')) }}">
    <link rel="stylesheet" href="/css/admin/base.css?v={{ $adminStyleVersion }}">
    <link rel="stylesheet" href="/css/admin/styles/login.css?v={{ filemtime(public_path('css/admin/styles/login.css')) }}">
    <script>const savedTheme = localStorage.getItem('admin-theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'); document.documentElement.setAttribute('data-theme', savedTheme);</script>
</head>
<body class="page-admin-login reveal" style="--admin-hero-bg: url('/gambar/tempat.jpeg');">
    <button class="theme-toggle admin-login-theme-toggle" type="button" id="admin-login-theme-toggle" aria-label="Ubah tema"></button>
    <main class="admin-hero">
        <div class="container admin-hero-shell">
            <section class="admin-login-intro">
                <span class="admin-login-kicker">Panel Internal</span>
                <h1 class="admin-login-display">Kelola website Karunya Printing dari satu tempat.</h1>
                <p class="admin-login-copy">Masuk untuk mengatur galeri, harga, dan konten utama website dengan tampilan admin yang lebih rapi dan nyaman dipakai.</p>
                <div class="admin-login-points">
                    <span>Galeri</span>
                    <span>Harga</span>
                    <span>Slider Home</span>
                    <span>Aktivitas</span>
                </div>
            </section>
            <section class="admin-login-card panel reveal">
                <div class="brand">
                    <span class="brand-mark" aria-hidden="true">KP</span>
                    <span class="brand-title wordmark">
                        <span class="wordmark-script">Karunya</span>
                        <span class="wordmark-sub">Admin Panel</span>
                    </span>
                </div>
                <h1 class="admin-login-title">Masuk ke Admin</h1>
                <p class="admin-login-subtitle">Kelola galeri, harga, dan slider home website Karunya Printing</p>

                @if (session('error'))
                    <div class="alert error">{{ session('error') }}</div>
                @endif

                <form method="POST" class="admin-login-form" action="/admin/login.php?key={{ urlencode($accessKey) }}">
                    @csrf
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input id="username" name="username" type="text" value="{{ old('username') }}" required autofocus>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input id="password" name="password" type="password" required>
                    </div>
                    <button class="btn btn-primary full-width" type="submit">Masuk ke Dashboard</button>
                </form>

<div class="login-footer">
                    <p class="admin-login-note">Halaman ini khusus untuk admin internal Karunya Printing.</p>
                    <div class="admin-login-actions">
                        <a class="btn btn-outline btn-sm" href="/admin/recovery/request.php">Lupa Password?</a>
                        <a class="btn btn-outline btn-sm" href="/index.php">Kembali ke Website</a>
                    </div>
                </div>
            </section>
        </div>
    </main>
    <script>
    (function() {
        const html = document.documentElement;
        const toggleBtn = document.getElementById('admin-login-theme-toggle');

        toggleBtn?.addEventListener('click', () => {
            const current = html.getAttribute('data-theme');
            const next = current === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', next);
            localStorage.setItem('admin-theme', next);
        });
    })();
    </script>
</body>
</html>
