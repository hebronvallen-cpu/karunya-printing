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
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <title>Masukkan Kode OTP | Karunya Printing Admin</title>
    <link rel="stylesheet" href="/admin.css?v={{ $adminStyleVersion }}">
    <script>const savedTheme = localStorage.getItem('admin-theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'); document.documentElement.setAttribute('data-theme', savedTheme);</script>
</head>
<body class="page-admin-login reveal" style="--admin-hero-bg: url('/gambar/tempat.jpeg');">
    <main class="admin-hero">
        <div class="container admin-hero-shell">
            <section class="admin-login-intro">
                <span class="admin-login-kicker">Verifikasi</span>
                <h1 class="admin-login-display">Masukkan kode verifikasi</h1>
<p class="admin-login-copy">Kami telah mengirim kode OTP ke email Anda. Masukkan kode 6 digit untuk melanjutkan.</p>
                <div class="admin-login-points">
                    <span>6 Digit</span>
                    <span>15 Menit</span>
                    <span>Sekali Pakai</span>
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
                <h1 class="admin-login-title">Verifikasi OTP</h1>
                <p class="admin-login-subtitle">Kode dikirim ke {{ $email }}</p>

                @if (session('error'))
                    <div class="alert error">{{ session('error') }}</div>
                @endif

                <form method="POST" class="admin-login-form" action="/admin/recovery/verify.php">
                    @csrf
                    <input type="hidden" name="admin_id" value="{{ $adminId }}">
                    <div class="form-group">
                        <label for="otp">Kode OTP (6 digit)</label>
                        <input id="otp" name="otp" type="text" maxlength="6" pattern="[0-9]*" inputmode="numeric" placeholder="XXXXXX" required autofocus>
                    </div>
                    <button class="btn btn-primary full-width" type="submit">Verifikasi</button>
                </form>

                <div class="login-footer">
                    <p class="admin-login-note">Tidak menerima kode?</p>
                    <div class="admin-login-actions">
                        <form method="POST" action="/admin/recovery/request.php" style="display:inline;">
                            @csrf
                            <input type="hidden" name="identifier" value="{{ $identifier }}">
                            <button type="submit" class="btn btn-outline btn-sm">Kirim Ulang OTP</button>
                        </form>
                    </div>
                </div>
            </section>
        </div>
    </main>
</body>
</html>
