@extends('admin.layout')

@section('content')
<section class="panel">
    <div class="panel-head panel-head--between">
        <div>
            <h2>{{ $editData !== null ? 'Edit Harga' : 'Tambah Harga' }}</h2>
            <p>{{ $editData !== null ? 'Perbarui data harga layanan.' : 'Tambah harga layanan baru.' }}</p>
        </div>
        <div class="panel-actions">
            <a class="btn btn-outline" href="{{ url('/admin/prices.php') }}">Kembali ke Daftar</a>
        </div>

    @if (session('success'))
        <div class="alert success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert error">{{ session('error') }}</div>
    @endif

    <div class="form-grid">
        <form method="POST" class="admin-form form-left" action="{{ url('/admin/prices-form.php') }}">
            @csrf
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="{{ $editData?->id ?? 0 }}">

            <div class="form-group">
                <label for="service_name">Layanan *</label>
                <input id="service_name" name="service_name" type="text" value="{{ $editData?->service_name ?? '' }}" required maxlength="100">
            </div>

            <div class="form-group">
                <label for="size_info">Ukuran/Satuan *</label>
                <input id="size_info" name="size_info" type="text" value="{{ $editData?->size_info ?? '' }}" required maxlength="50">
            </div>

            <div class="form-group">
                <label for="price_text">Harga Mulai *</label>
                <input id="price_text" name="price_text" type="text" value="{{ $editData?->price_text ?? '' }}" required maxlength="50">
                <small>Contoh: "Rp 35.000" atau "Mulai Rp 250.000"</small>
            </div>

            <div class="form-group">
                <label for="sort_order">Urutan</label>
                <input id="sort_order" name="sort_order" type="number" min="0" value="{{ $editData?->sort_order ?? 0 }}">
            </div>

            <div class="form-group check-row">
                <input type="checkbox" id="is_active" name="is_active" @checked(($editData?->is_active ?? 1) == 1)>
                <label for="is_active">Tampilkan di website</label>
            </div>

            <div class="btn-row">
                <button class="btn btn-primary" type="submit">{{ $editData !== null ? 'Simpan Perubahan' : 'Tambah Harga' }}</button>
                <a class="btn btn-outline" href="{{ url('/admin/prices.php') }}">Batal</a>
            </div>
        </form>

        <div class="form-preview price-preview">
            <div class="preview-card panel">
                <h4>Preview Tabel Harga</h4>
                <table class="preview-table">
                    <tr>
                        <td>{{ $editData?->service_name ?? 'Layanan' }}</td>
                        <td>{{ $editData?->size_info ?? 'ukuran' }}</td>
                        <td>{{ $editData?->price_text ?? 'Rp 0' }}</td>
                    </tr>
                </table>
            </div>
    </div>
</section>
@endsection
