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

    <section class="panel">
        <div class="panel-head panel-head--between">
            <div>
                <h2>Profil Admin</h2>
                <p class="panel-subtitle">Konfigurasikan jalur pemulihan akun melalui Email dan WhatsApp.</p>
            </div>
        </div>

        <div class="form-grid">
            <form method="POST" action="/admin/settings-profile.php" class="admin-form form-left">
                @csrf
                <input type="hidden" name="action" value="save">

                <div class="form-group">
                    <label for="username">Username</label>
                    <input id="username" name="username" type="text" value="{{ old('username', $adminData->nama_pengguna) }}" required>
                    <small>🆔 Identitas unik login admin (3-30 karakter).</small>
                </div>

                <div class="form-group">
                    <label for="full_name">Nama Lengkap</label>
                    <input id="full_name" name="full_name" type="text" value="{{ old('full_name', $adminData->nama_lengkap) }}" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Pemulihan (OTP)</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $adminData->email) }}" required>
                    <small>📧 Email utama untuk pengiriman kode OTP saat lupa password.</small>
                </div>

                <div class="form-group">
                    <label for="phone">Nomor WhatsApp (Format 62)</label>
                    <input id="phone" name="phone" type="tel" value="{{ old('phone', $adminData->nomor_telepon) }}" placeholder="6281234567890">
                    <small>💬 Gunakan format internasional (contoh: 62812xxx) untuk OTP WhatsApp.</small>
                </div>

                <div class="form-group check-row">
                    <input type="checkbox" name="test_otp" id="test_otp" value="1">
                    <label for="test_otp" style="cursor: pointer;">Kirim WhatsApp uji coba setelah menyimpan</label>
                </div>

                <div class="btn-row">
                    <button type="submit" class="btn btn-primary">Perbarui Profil Admin</button>
                </div>
            </form>

            <div class="form-preview">
                <div class="preview-placeholder panel">
                    <p>Informasi profil ini digunakan untuk keamanan akun dan pengiriman kode verifikasi OTP jika Anda lupa kata sandi.</p>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
