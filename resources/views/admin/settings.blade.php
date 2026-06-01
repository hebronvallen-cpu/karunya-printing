@extends('admin.layout')

@section('content')
<div class="admin-page-content">
    @if (session('success'))
        <div class="alert success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert error">{{ session('error') }}</div>
    @endif

    <div class="settings-grid">
        <div class="card settings-card hub-card" style="max-width: 600px;">
            <div class="card-header">
                <h2>Pengaturan</h2>
                <p>Pilih kategori pengaturan untuk dikelola</p>
            </div>
            <div class="settings-actions">
                <a class="btn btn-long btn-primary" href="/admin/settings-profile.php">
                    <i class="fas fa-user-circle" style="margin-right: 10px;"></i> Profil Admin
                </a>
                <a class="btn btn-long btn-warning" href="/admin/settings-password.php">
                    <i class="fas fa-key" style="margin-right: 10px;"></i> Ubah Password
                </a>
                <a class="btn btn-long btn-info" href="/admin/settings-contact.php">
                    <i class="fas fa-address-book" style="margin-right: 10px;"></i> Kontak & Media Sosial
                </a>
            </div>
        </div>
    </div>
</div>

<style>
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

.settings-card + .card {
    margin-top: 1.25rem;
}

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

.settings-card h2 {
    font-size: 1.25rem;
    font-weight: 600;
    margin: 0 0 0.25rem 0;
    color: var(--text-primary, #111827);
}

.settings-card .card-header p {
    font-size: 0.875rem;
    color: var(--text-secondary, #6b7280);
    margin: 0;
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

.form-group input[type="text"],
.form-group input[type="email"],
.form-group input[type="password"] {
    width: 100%;
    padding: 0.625rem 0.875rem;
    border: 1px solid var(--border-color, #d1d5db);
    border-radius: 8px;
    font-size: 0.9375rem;
    background: var(--input-bg, #fff);
    color: var(--text-primary, #111827);
    transition: border-color 0.15s, box-shadow 0.15s;
}

.form-group input:focus {
    outline: none;
    border-color: var(--primary, #3b82f6);
    box-shadow: 0 0 0 3px var(--primary-light, rgba(59,130,246,0.1));
}

.form-group input:disabled {
    background: var(--bg-disabled, #f3f4f6);
    color: var(--text-secondary, #6b7280);
    cursor: not-allowed;
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

.btn-warning:hover {
    background: var(--warning-dark, #d97706);
}

.btn-info {
    background: var(--info, #06b6d4);
    color: #fff;
}

.btn-info:hover {
    background: var(--info-dark, #0891b2);
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

.info-content p {
    margin: 0 0 0.5rem 0;
    font-size: 0.9375rem;
}

.info-content hr {
    margin: 1rem 0;
    border: none;
    border-top: 1px solid var(--border-color, #e5e7eb);
}

.info-content ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.info-content li {
    margin-bottom: 0.5rem;
}

.info-content a {
    color: var(--primary, #3b82f6);
    text-decoration: none;
}

.info-content a:hover {
    text-decoration: underline;
}

.text-muted {
    color: var(--text-secondary, #6b7280);
}
</style>
@endsection
