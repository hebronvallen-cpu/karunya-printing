@extends('admin.layout')

@section('content')
<div class="admin-page-content">
    @if (session('success'))
        <div class="alert success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert error">{{ session('error') }}</div>
    @endif

    @if($showBackButton)
    <div style="margin-bottom: 1.5rem;">
        <a href="/admin/settings.php" class="btn btn-secondary" style="background: #6b7280; color: #fff; text-decoration: none; display: inline-flex; align-items: center; padding: 0.5rem 1rem; border-radius: 8px;">
            <i class="fas fa-arrow-left" style="margin-right: 8px;"></i> Kembali ke Pengaturan
        </a>
    </div>
    @endif

    <div class="settings-grid">
        <div class="card settings-card">
            <div class="card-header">
                <h2>Profil Admin</h2>
                <p>Kelola informasi profil akun Anda</p>
            </div>
            <form method="POST" action="/admin/settings-profile.php">
                @csrf
                <input type="hidden" name="action" value="update_profile">

                <div class="form-group">
                    <label for="username">Username</label>
                    <input id="username" name="username" type="text" value="{{ old('username', $adminData->nama_pengguna) }}" required>
                    <small class="form-help">Ubah username akun (unik). 3–30 karakter; huruf, angka, titik, underscore, atau minus.</small>
                </div>

                <div class="form-group">
                    <label for="full_name">Nama Lengkap</label>
                    <input id="full_name" name="full_name" type="text" value="{{ old('full_name', $adminData->nama_lengkap) }}" required>
                </div>

                <div class="form-group">
                    <label>Status Akun</label>
                    <div class="form-static">
                        {{ $adminData->aktif ? 'Aktif' : 'Nonaktif' }}
                    </div>
                </div>

                <div class="form-group">
                    <label>Dibuat</label>
                    <div class="form-static">
                        {{ $adminData->waktu_dibuat ? \Carbon\Carbon::parse($adminData->waktu_dibuat)->format('d M Y, H:i') : '-' }}
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </form>
        </div>
    </div>
</div>

<style>
.settings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}

.settings-card {
    background: var(--card-bg, #fff);
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: var(--shadow, 0 1px 3px rgba(0,0,0,0.1));
}

.settings-card .card-header {
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--border-color, #e5e7eb);
}

.settings-actions {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.btn-long {
    width: 100%;
    padding: 0.85rem 1rem;
    justify-content: flex-start;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    margin-bottom: 0.375rem;
    color: var(--text-primary, #111827);
}

.form-group input[type="text"] {
    width: 100%;
    padding: 0.625rem 0.875rem;
    border: 1px solid var(--border-color, #d1d5db);
    border-radius: 8px;
    font-size: 0.9375rem;
    background: var(--input-bg, #fff);
    color: var(--text-primary, #111827);
}

.form-help {
    display: block;
    font-size: 0.75rem;
    color: var(--text-secondary, #6b7280);
    margin-top: 0.25rem;
}

.form-static {
    padding: 0.625rem 0.875rem;
    font-size: 0.9375rem;
    color: var(--text-secondary, #6b7280);
    border: 1px solid var(--border-color, #d1d5db);
    border-radius: 8px;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.625rem 1rem;
    border-radius: 8px;
    font-size: 0.9375rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.15s;
    text-decoration: none;
    border: none;
}

.btn-primary {
    background: var(--primary, #3b82f6);
    color: #fff;
}

.btn-primary:hover {
    background: var(--primary-dark, #2563eb);
}

.btn-warning {
    background: var(--warning, #f59e0b);
    color: #fff;
}
</style>
@endsection
