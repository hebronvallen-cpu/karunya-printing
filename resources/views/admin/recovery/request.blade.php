@php
    $adminStyleVersion = is_file(public_path('admin.css'))
        ? filemtime(public_path('admin.css'))
        : time();
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password | Karunya Printing Admin</title>
    <link rel="stylesheet" href="{{ url('/admin.css') }}?v={{ $adminStyleVersion }}">
    <script>const savedTheme = localStorage.getItem('admin-theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'); document.documentElement.setAttribute('data-theme', savedTheme);</script>
</head>
<body class="page-admin-login reveal" style="--admin-hero-bg: url('{{ url('/gambar/tempat.jpeg') }}');">
    <main class="admin-hero">
        <div class="container admin-hero-shell">
            <section class="admin-login-intro">
                <span class="admin-login-kicker">Pemulihan Akun</span>
                <h1 class="admin-login-display">Lupa password atau username?</h1>
                <p class="admin-login-copy">Masukkan informasi yang Anda ingat. Kami akan membantu Anda mendapatkan kembali akses ke akun admin.</p>
                <div class="admin-login-points">
                    <span>Verifikasi Aman</span>
                    <span>OTP Gratis</span>
                    <span>Mudah & Cepat</span>
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
                <h1 class="admin-login-title">Pulihkan Akun</h1>
                <p class="admin-login-subtitle">Masukkan username atau email untuk memulai proses pemulihan</p>

                @if (session('error'))
                    <div class="alert error">{{ session('error') }}</div>
                @endif

                @if (session('success'))
                    <div class="alert success">{{ session('success') }}</div>
                @endif

                <form method="POST" class="admin-login-form" action="{{ url('/admin/recovery/request.php') }}">
                    @csrf
                    <div class="form-group">
                        <label for="username">Username atau Email</label>
                        <input id="username" name="identifier" type="text" value="{{ old('identifier') }}" placeholder="Masukkan username atau email" required autofocus>
                    </div>
                    <button class="btn btn-primary full-width" type="submit">Kirim Kode Verifikasi</button>
                </form>

                <div class="login-footer">
                    <p class="admin-login-note">Tidak perluPanic? Ingat sesuatu?</p>
                    <div class="admin-login-actions">
                        <a class="btn btn-outline btn-sm" href="{{ url('/admin/login.php?key=' . urlencode($accessKey)) }}">Kembali ke Login</a>
                        <a class="btn btn-outline btn-sm" href="{{ url('/index.php') }}">Lihat Website</a>
                    </div>
                </div>
            </section>
        </div>
    </main>
</body>
</html>
