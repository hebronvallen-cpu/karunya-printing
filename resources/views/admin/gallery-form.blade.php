@php
    use App\Support\LegacySite;
@endphp

@extends('admin.layout')

@section('content')
<section class="panel">
    <div class="panel-head panel-head--between">
        <div>
            <h2>{{ $editData ? 'Edit Galeri' : 'Tambah Galeri' }}</h2>
            <p>{{ $editData ? 'Perbarui data foto galeri layanan.' : 'Tambah foto galeri layanan baru.' }}</p>
        </div>
        <div class="panel-actions">
            <a class="btn btn-outline" href="{{ url('/admin/gallery.php') }}">Kembali ke Daftar</a>
        </div>

    @if (session('success'))
        <div class="alert success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert error">{{ session('error') }}</div>
    @endif

    <div class="form-grid">
        <form method="POST" class="admin-form form-left" enctype="multipart/form-data" action="{{ url('/admin/gallery-form.php') }}">
            @csrf
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="{{ $editData?->id ?? 0 }}">
            <input type="hidden" name="current_image_path" value="{{ $editData?->image_path ?? '' }}">

            <div class="form-group">
                <label for="service_name">Kategori Layanan *</label>
                <select id="service_name" name="service_name" required>
                    <option value="">Pilih...</option>
                    @foreach ($serviceOptions as $service)
                        <option value="{{ $service }}" @selected(($editData?->title ?? '') === $service)>{{ $service }}</option>
                    @endforeach
                </select>
                <small class="help-block">Kategori diisi otomatis ke `kode_kategori` dan `label_kategori` berdasarkan layanan yang dipilih.</small>
            </div>


            <div class="form-group">
                <label for="image_file">Unggah Foto {{ $editData ? 'Baru' : '' }}</label>
                <input id="image_file" name="image_file" type="file" accept="image/jpeg,image/png,image/webp" {{ $editData ? '' : 'required' }}>
                <small>Max 8MB (JPG, PNG, WEBP)</small>
            </div>

            <div class="form-group">
                <label for="sort_order">Urutan Tampil</label>
                <input id="sort_order" name="sort_order" type="number" min="0" value="{{ $editData?->sort_order ?? 0 }}">
            </div>

            <div class="form-group check-row">
                <input type="checkbox" id="is_active" name="is_active" @checked(($editData?->is_active ?? 1) == 1)>
                <label for="is_active">Tampilkan di website</label>
            </div>

            <div class="btn-row">
                <button class="btn btn-primary" type="submit">{{ $editData !== null ? 'Update' : 'Tambah' }}</button>
                <a class="btn btn-outline" href="{{ url('/admin/gallery.php') }}">Batal</a>
            </div>
        </form>

        <div class="form-preview">
            @if ($editData !== null && filled($editData->image_path))
                @php
                    $previewPath = \App\Support\LegacySite::mediaUrl((string) $editData->image_path);
                    $previewUrl = str_starts_with($previewPath, 'http') ? $previewPath : url($previewPath);
                @endphp
                <div class="preview-current panel">
                    <h4>Foto Saat Ini</h4>
                    <img src="{{ $previewUrl }}" alt="{{ $editData->title }}" class="preview-thumb">
                    <p>{{ basename((string) $editData->image_path) }}</p>
                </div>
            @else
                <div class="preview-placeholder panel">
                    <p>Preview foto akan muncul di sini setelah memilih file</p>
                </div>
            @endif
        </div>
</section>
@endsection
