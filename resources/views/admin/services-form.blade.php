@extends('admin.layout')

@section('content')
<section class="panel">
    <div class="panel-head panel-head--between">
        <div>
            <h2>{{ $editData !== null ? 'Edit Layanan' : 'Tambah Layanan' }}</h2>
            <p>{{ $editData !== null ? 'Perbarui data layanan.' : 'Tambah layanan baru.' }}</p>
        </div>
        <div class="panel-actions">
            <a class="btn btn-outline" href="{{ url('/admin/services.php') }}">Kembali ke Daftar</a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert error">{{ session('error') }}</div>
    @endif

    <div class="form-grid">
        <form method="POST" class="admin-form form-left" action="{{ url('/admin/services-form.php') }}">
            @csrf
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="{{ $editData?->id ?? 0 }}">

            <div class="form-group">
                <label for="title">Nama Layanan *</label>
                <input id="title" name="title" type="text" value="{{ $editData?->title ?? '' }}" required maxlength="100">
            </div>

            <div class="form-group">
                <label for="description">Deskripsi *</label>
                <textarea id="description" name="description" rows="4" required>{{ $editData?->description ?? '' }}</textarea>
                <small>Deskripsi singkat yang tampil di kartu layanan publik.</small>
            </div>

            <div class="form-group">
                <label for="sort_order">Urutan</label>
                <input id="sort_order" name="sort_order" type="number" min="0" value="{{ $editData?->sort_order ?? 0 }}">
                <small>Semakin kecil angka, semakin awal tampil.</small>
            </div>

            <div class="form-group check-row">
                <input type="checkbox" id="is_active" name="is_active" @checked(($editData?->is_active ?? 1) == 1)>
                <label for="is_active">Tampilkan di website</label>
            </div>

            <div class="btn-row">
                <button class="btn btn-primary" type="submit">{{ $editData !== null ? 'Simpan Perubahan' : 'Tambah Layanan' }}</button>
                <a class="btn btn-outline" href="{{ url('/admin/services.php') }}">Batal</a>
            </div>
        </form>

        <div class="form-preview">
            <div class="preview-card panel">
                <h4>Preview Kartu Layanan</h4>
                <div class="service-preview-card">
                    <h5>{{ $editData?->title ?? 'Nama Layanan' }}</h5>
                    <p>{{ \Illuminate\Support\Str::limit($editData?->description ?? 'Deskripsi layanan akan tampil di sini.', 100) }}</p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

