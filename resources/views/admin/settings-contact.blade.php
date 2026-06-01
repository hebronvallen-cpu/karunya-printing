@extends('admin.layout')

@section('content')
<div class="admin-page-content">
    @if (session('success'))
        <div class="alert success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert error">{{ session('error') }}</div>
    @endif

    <div style="margin-bottom: 1.5rem;">
        <a href="{{ url('/admin/settings.php') }}" class="btn btn-secondary" style="background: #6b7280; color: #fff; text-decoration: none; display: inline-flex; align-items: center; padding: 0.5rem 1rem; border-radius: 8px;">
            <i class="fas fa-arrow-left" style="margin-right: 8px;"></i> Kembali ke Pengaturan
        </a>
    </div>

    <div class="settings-grid">
        <div class="card settings-card">
            <div class="card-header">
                <h2>Kontak & Media Sosial</h2>
                <p>Informasi yang tampil di halaman kontak dan footer</p>
            </div>

            <form method="POST" action="{{ url('/admin/settings-contact.php') }}">
                @csrf
                <input type="hidden" name="action" value="update_site_settings">

                <div class="form-group">
                    <label for="contact_phone">Nomor Telepon (Tampilan)</label>
                    <input id="contact_phone" name="contact_phone" type="text" value="{{ $settings['contact_phone'] ?? config('legacy.contact.phone') }}">
                </div>

                <div class="form-group">
                    <label for="contact_phone_international">Nomor WhatsApp (Format: 62812...)</label>
                    <input id="contact_phone_international" name="contact_phone_international" type="text" value="{{ $settings['contact_phone_international'] ?? config('legacy.contact.phone_international') }}">
                </div>

                <div class="form-group">
                    <label for="contact_email">Email</label>
                    <input id="contact_email" name="contact_email" type="email" value="{{ $settings['contact_email'] ?? config('legacy.contact.email') }}">
                </div>

                <div class="form-group">
                    <label for="contact_address">Alamat Lengkap</label>
                    <textarea id="contact_address" name="contact_address" style="width:100%; padding:0.625rem; border-radius:8px; border:1px solid var(--border-color);">{{ $settings['contact_address'] ?? config('legacy.contact.address') }}</textarea>
                </div>

                <div class="form-group">
                    <label for="contact_map_url">Google Maps URL</label>
                    <input id="contact_map_url" name="contact_map_url" type="text" value="{{ $settings['contact_map_url'] ?? config('legacy.contact.map_url') }}">
                </div>

                <div class="form-group">
                    <label for="whatsapp_default_message">Pesan WhatsApp Default</label>
                    <textarea id="whatsapp_default_message" name="whatsapp_default_message" style="width:100%; padding:0.625rem; border-radius:8px; border:1px solid var(--border-color);" placeholder="Halo, saya mau konsultasi percetakan">{{ $settings['whatsapp_default_message'] ?? config('legacy.whatsapp.default_message') }}</textarea>
                    <span class="form-help">Dipakai untuk tombol WhatsApp umum. Tombol layanan/harga/galeri tetap membuat pesan otomatis sesuai item.</span>
                </div>

                <hr style="margin: 1.5rem 0; border: none; border-top: 1px solid var(--border-color, #e5e7eb);">

                <h3>Link Media Sosial</h3>
                <p style="font-size: 0.8rem; color: var(--text-secondary, #6b7280); margin-bottom: 1rem;">
                    Ini adalah <strong>URL link</strong> yang dipakai ketika pelanggan mengklik ikon Instagram / Facebook / TikTok.
                </p>

                <div class="form-group">
                    <label for="social_instagram">Link Instagram</label>
                    <input id="social_instagram" name="social_instagram" type="text" value="{{ $settings['social_instagram'] ?? config('legacy.social_links.instagram') }}" placeholder="https://instagram.com/...">
                </div>

                <div class="form-group">
                    <label for="social_facebook">Link Facebook</label>
                    <input id="social_facebook" name="social_facebook" type="text" value="{{ $settings['social_facebook'] ?? config('legacy.social_links.facebook') }}" placeholder="https://facebook.com/...">
                </div>

                <div class="form-group">
                    <label for="social_tiktok">Link TikTok</label>
                    <input id="social_tiktok" name="social_tiktok" type="text" value="{{ $settings['social_tiktok'] ?? config('legacy.social_links.tiktok') }}" placeholder="https://tiktok.com/@...">
                </div>

                <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">Simpan Pengaturan Website</button>
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

.form-group input[type="text"],
.form-group input[type="email"],
.form-group textarea {
    width: 100%;
    padding: 0.625rem 0.875rem;
    border: 1px solid var(--border-color, #d1d5db);
    border-radius: 8px;
    font-size: 0.9375rem;
    background: var(--input-bg, #fff);
    color: var(--text-primary, #111827);
    box-sizing: border-box;
}

.form-help {
    display: block;
    font-size: 0.75rem;
    color: var(--text-secondary, #6b7280);
    margin-top: 0.25rem;
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
