@extends('admin.layout')

@section('content')
<div class="admin-page-content">
    @if (session('success'))
        <div class="alert success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert error">{{ session('error') }}</div>
    @endif

    <div class="verify-email-container">
        <div class="card">
            <div class="card-header">
                <h2>Verifikasi Ganti Email</h2>
                <p>Masukkan kode OTP yang dikirim ke email lama Anda</p>
            </div>
            
            <div class="email-info">
                <div class="info-row">
                    <span class="label">Email Lama:</span>
                    <span class="value">{{ $adminName ?? 'Admin' }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Email Baru:</span>
                    <span class="value">{{ $newEmail }}</span>
                </div>
            </div>
            
            <form method="POST" action="/admin/settings/verify-email.php">
                @csrf
                
                <div class="form-group">
                    <label for="otp">Kode OTP</label>
                    <input id="otp" name="otp" type="text" 
                           placeholder="Masukkan 6 digit kode OTP" 
                           maxlength="6" 
                           autocomplete="one-time-code"
                           required>
                    <small class="form-help">Kode OTP telah dikirim ke email lama Anda. berlaku 15 menit.</small>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Verifikasi & Ganti Email</button>
                    <a href="/admin/settings.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
            
            <div class="resend-info">
                <p>Tidak menerima kode OTP? <a href="/admin/settings.php">Kembali ke Pengaturan</a></p>
            </div>
        </div>
    </div>
</div>

<style>
.verify-email-container {
    max-width: 480px;
    margin: 2rem auto;
}

.card {
    background: var(--card-bg, #fff);
    border-radius: 12px;
    padding: 2rem;
    box-shadow: var(--shadow, 0 1px 3px rgba(0,0,0,0.1));
}

.card-header {
    margin-bottom: 1.5rem;
    text-align: center;
}

.card-header h2 {
    font-size: 1.5rem;
    font-weight: 600;
    margin: 0 0 0.5rem 0;
    color: var(--text-primary, #111827);
}

.card-header p {
    font-size: 0.9375rem;
    color: var(--text-secondary, #6b7280);
    margin: 0;
}

.email-info {
    background: var(--bg-secondary, #f9fafb);
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1.5rem;
}

.info-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
}

.info-row:not(:last-child) {
    border-bottom: 1px solid var(--border-color, #e5e7eb);
}

.info-row .label {
    font-size: 0.875rem;
    color: var(--text-secondary, #6b7280);
}

.info-row .value {
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--text-primary, #111827);
}

.form-group {
    margin-bottom: 1.25rem;
}

.form-group label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    margin-bottom: 0.5rem;
    color: var(--text-primary, #111827);
}

.form-group input[type="text"] {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid var(--border-color, #d1d5db);
    border-radius: 8px;
    font-size: 1.125rem;
    text-align: center;
    letter-spacing: 0.25em;
    background: var(--input-bg, #fff);
    color: var(--text-primary, #111827);
    transition: border-color 0.15s, box-shadow 0.15s;
}

.form-group input:focus {
    outline: none;
    border-color: var(--primary, #3b82f6);
    box-shadow: 0 0 0 3px var(--primary-light, rgba(59,130,246,0.1));
}

.form-help {
    display: block;
    font-size: 0.75rem;
    color: var(--text-secondary, #6b7280);
    margin-top: 0.5rem;
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 1.5rem;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-size: 0.9375rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.15s;
    text-decoration: none;
    border: none;
    flex: 1;
}

.btn-primary {
    background: var(--primary, #3b82f6);
    color: #fff;
}

.btn-primary:hover {
    background: var(--primary-dark, #2563eb);
}

.btn-secondary {
    background: var(--bg-secondary, #f3f4f6);
    color: var(--text-primary, #111827);
}

.btn-secondary:hover {
    background: var(--border-color, #e5e7eb);
}

.resend-info {
    margin-top: 1.5rem;
    text-align: center;
    font-size: 0.875rem;
    color: var(--text-secondary, #6b7280);
}

.resend-info a {
    color: var(--primary, #3b82f6);
    text-decoration: none;
}

.resend-info a:hover {
    text-decoration: underline;
}

.alert {
    padding: 0.875rem 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}

.alert.success {
    background: var(--success-bg, #d1fae5);
    color: var(--success-text, #065f46);
    border: 1px solid var(--success-border, #a7f3d0);
}

.alert.error {
    background: var(--error-bg, #fee2e2);
    color: var(--error-text, #991b1b);
    border: 1px solid var(--error-border, #fecaca);
}
</style>
@endsection
