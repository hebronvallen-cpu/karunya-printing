@extends('admin.layout')

@section('content')
<div class="admin-page-content">
    @if (session('success'))
        <div class="alert success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert error">{{ session('error') }}</div>
    @endif

    <div class="panel-actions" style="margin-bottom: 1.5rem;">
        <a href="/admin/settings.php" class="btn btn-outline btn-sm">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="margin-right:8px">
                <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
            </svg>
            Kembali ke Pengaturan
        </a>
    </div>

    <div class="settings-card-premium">
        <div class="panel-head">
            <div>
                <span class="admin-page-kicker">Security Center</span>
                <h2>Profil Admin</h2>
                <p class="panel-subtitle">Konfigurasikan jalur pemulihan akun melalui Email dan WhatsApp.</p>
            </div>
        </div>

        <form method="POST" action="/admin/settings-profile.php" class="admin-form">
            @csrf
            <input type="hidden" name="action" value="save">

            <div class="settings-form-group">
                <label class="form-label">Username</label>
                <div class="settings-input-wrapper">
                    <input name="username" type="text" class="form-input" value="{{ old('username', $adminData->nama_pengguna) }}" required>
                    <p class="input-hint">🆔 Identitas unik login admin (3-30 karakter).</p>
                </div>
            </div>

            <div class="settings-form-group">
                <label class="form-label">Nama Lengkap</label>
                <div class="settings-input-wrapper">
                    <input name="full_name" type="text" class="form-input" value="{{ old('full_name', $adminData->nama_lengkap) }}" required>
                </div>
            </div>

            <div class="settings-form-group">
                <label class="form-label">Email Pemulihan (OTP)</label>
                <div class="settings-input-wrapper">
                    <input name="email" type="email" class="form-input" value="{{ old('email', $adminData->email) }}" required>
                    <p class="input-hint">📧 Email utama untuk pengiriman kode OTP saat lupa password.</p>
                </div>
            </div>

            <div class="settings-form-group">
                <label class="form-label">Nomor WhatsApp (Format 62)</label>
                <div class="settings-input-wrapper">
                    <input name="phone" type="tel" class="form-input" value="{{ old('phone', $adminData->nomor_telepon) }}" placeholder="6281234567890">
                    <p class="input-hint">💬 Gunakan format internasional (contoh: 62812xxx) untuk OTP WhatsApp.</p>
                </div>
            </div>

            <div class="test-otp-checkbox check-row">
                <input type="checkbox" name="test_otp" id="test_otp" value="1">
                <label for="test_otp" style="font-weight: 600; cursor: pointer;">Kirim WhatsApp uji coba setelah menyimpan</label>
            </div>

            <div class="btn-row" style="margin-top: 2rem;">
                <button type="submit" class="btn btn-primary full-width">Perbarui Profil Admin</button>
            </div>
        </form>
    </div>
</div>
@endsection
