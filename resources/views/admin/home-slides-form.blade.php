@extends('admin.layout')

@section('content')
<section class="panel">
    <div class="panel-head panel-head--between">
        <div>
            <h2>{{ $editData ? 'Edit Slider Home' : 'Tambah Slider Home' }}</h2>
            <p>{{ $editData ? 'Perbarui data slider home.' : 'Tambah slider home baru.' }}</p>
        </div>
        <div class="panel-actions">
            <a class="btn btn-outline" href="{{ url('/admin/home-slides.php') }}">Kembali ke Daftar</a>
        </div>

    @if (session('success'))
        <div class="alert success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert error">{{ session('error') }}</div>
    @endif

    <form class="admin-form" method="post" action="{{ url('/admin/home-slides-form.php') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="{{ $editData ? $editData->id : 0 }}">
        <input type="hidden" name="current_image_path" value="{{ $editData ? $editData->image_path : '' }}">

        <div class="form-group">
            <label>Judul Slide <small class="text-muted">(opsional)</small></label>
            <input type="text" name="title" value="{{ $editData ? $editData->title : '' }}" placeholder="Contoh: Karunya Printing">
        </div>

        <div class="form-group">
            <label>Subjudul / Caption <small class="text-muted">(opsional)</small></label>
            <input type="text" name="caption" value="{{ $editData ? $editData->caption : '' }}" placeholder="Contoh: Jasa percetakan murah, cepat, dan berkualitas">
        </div>

        <div class="form-group">
            <label>Upload Gambar {{ $editData ? 'Baru' : '' }}</label>
            <input type="file" name="image_file" accept="image/*" {{ $editData ? '' : 'required' }}>
            @if ($editData && $editData->image_path)
                <p class="text-muted">Gambar saat ini: {{ $editData->image_path }}</p>
            @endif
        </div>

        <div class="form-group">
            <label>Urutan Tampil</label>
            <input type="number" name="sort_order" value="{{ $editData ? $editData->sort_order : 0 }}">
        </div>

        <div class="form-group check-row">
            <input type="checkbox" id="is_active" name="is_active" value="1" @checked(!$editData || $editData->is_active)>
            <label for="is_active">Aktif</label>
        </div>

        <div class="btn-row">
            <button class="btn btn-primary" type="submit">Simpan</button>
            <a class="btn btn-outline" href="{{ url('/admin/home-slides.php') }}">Batal</a>
        </div>
    </form>

    @if ($editData && $editData->image_path)
        <div class="preview-section" style="margin-top:24px;">
            <img src="{{ \App\Support\LegacySite::mediaUrl((string) $editData->image_path) }}" alt="Preview" class="preview-image">
        </div>
    @endif
</section>
@endsection
