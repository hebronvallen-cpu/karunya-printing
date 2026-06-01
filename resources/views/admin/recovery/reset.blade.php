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
    <title>Reset Password | Karunya Printing Admin</title>
    <link rel="stylesheet" href="{{ url('/admin.css') }}?v={{ $adminStyleVersion }}">
    <script>const savedTheme = localStorage.getItem('admin-theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'); document.documentElement.setAttribute('data-theme', savedTheme);</script>
</head>
<body class="page-admin-login reveal" style="--admin-hero-bg: url('{{ url('/gambar/tempat.jpeg') }}');">
    <main class="admin-hero">
        <div class="container admin-hero-shell">
            <section class="admin-login-intro">
                <span class="admin-login-kicker">Reset Password</span>
                <h1 class="admin-login-display">Buat password baru</h1>
                <p class="admin-login-copy">Masukkan password baru yang kuat untuk akun admin Anda.</p>
                <div class="admin-login-points">
                    <span>Minimal 6 Karakter</span>
                    <span>Kombinasi</span>
                    <span>Simpan di Tempat Aman</span>
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
                <h1 class="admin-login-title">Password Baru</h1>
                <p class="admin-login-subtitle">Akun: {{ $username }}</p>

                @if (session('error'))
                    <div class="alert error">{{ session('error') }}</div>
                @endif

                <form method="POST" class="admin-login-form" action="{{ url('/admin/recovery/reset.php') }}">
                    @csrf
                    <input type="hidden" name="admin_id" value="{{ $adminId }}">
                    <input type="hidden" name="token" value="{{ $token }}">
                    <div class="form-group">
                        <label for="password">Password Baru</label>
                        <input id="password" name="password" type="password" placeholder="Minimal 6 karakter" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label for="password_confirmation">Konfirmasi Password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Masukkan lagi password" required minlength="6">
                    </div>
                    <button class="btn btn-primary full-width" type="submit">Simpan Password Baru</button>
                </form>

                <div class="login-footer">
                    <p class="admin-login-note">Ingat password lama?</p>
                    <div class="admin-login-actions">
                        <a class="btn btn-outline btn-sm" href="{{ url('/admin/login.php?key=' . urlencode($accessKey)) }}">Kembali ke Login</a>
                    </div>
                </div>
            </section>
        </div>
    </main>
</body>
</html>
