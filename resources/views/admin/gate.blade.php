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
    <title>Akses Ditolak | Karunya Printing</title>
    <link rel="stylesheet" href="/admin.css?v={{ filemtime(public_path('admin.css')) }}">
</head>
<body class="page-admin-gate" style="--admin-hero-bg: url('/gambar/tempat.jpeg');">
    <main class="admin-hero">
        <div class="container admin-hero-shell admin-gate-shell">
            <section class="admin-gate-card panel">
                <div class="brand">
                    <span class="brand-mark" aria-hidden="true">KP</span>
                    <span class="brand-title wordmark">
                        <span class="wordmark-script">Karunya</span>
                        <span class="wordmark-sub">Admin Panel</span>
                    </span>
                </div>
                <span class="admin-login-kicker">Akses Internal</span>
                <h1 class="admin-login-title">Akses Ditolak</h1>
                <p class="admin-login-subtitle">Halaman login admin hanya bisa dibuka lewat tautan admin resmi yang sudah disiapkan.</p>
                <div class="admin-login-actions">
                    <a class="btn btn-primary full-width" href="/index.php">Kembali ke Website</a>
                </div>
            </section>
        </div>
    </main>
</body>
</html>
