@extends('admin.layout')

@section('page-styles-admin')
    <link rel="stylesheet" href="/css/admin/styles/prices.css?v={{ filemtime(public_path('css/admin/styles/prices.css')) }}">
@endsection

@section('content')
<section class="panel">
    <div class="panel-head panel-head--between">
        <div>
            <h2>Daftar Harga</h2>
            <p class="panel-subtitle">Menampilkan {{ $priceItems->count() }} dari {{ $priceTotalCount }} data harga.</p>
        </div>
        <div class="panel-actions">
            <a class="btn btn-primary" href="/admin/prices-form.php">+ Tambah Harga</a>
        </div>

    <form method="GET" class="admin-filters admin-filters-grid" action="/admin/prices.php">
        <div class="field-group">
            <input id="price-search" name="q" type="text" value="{{ $searchQuery }}" placeholder="Pencarian" aria-label="Pencarian harga">
        </div>

        <div class="field-group">
            <label for="price-status">Filter status</label>
            <select id="price-status" name="status">
                <option value="all" @selected($statusFilter === 'all')>Semua status</option>
                <option value="active" @selected($statusFilter === 'active')>Aktif</option>
                <option value="inactive" @selected($statusFilter === 'inactive')>Nonaktif</option>
            </select>
        </div>

        <div class="field-group">
            <label for="price-service">Filter layanan</label>
            <select id="price-service" name="service">
                <option value="0" @selected($serviceFilter === 0)>Semua layanan</option>
                @foreach ($services as $service)
                    <option value="{{ $service->id }}" @selected($serviceFilter === (int) $service->id)>{{ $service->title }}</option>
                @endforeach
            </select>
        </div>

        <div class="field-actions">
            <button class="btn btn-primary" type="submit">Terapkan</button>
            <a class="btn btn-outline" href="/admin/prices.php">Reset</a>
        </div>
    </form>

    @if ($priceItems->isEmpty())
        <p>Belum ada data harga.</p>
    @else
        <div class="table-scroll">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Layanan</th>
                        <th>Ukuran / Satuan</th>
                        <th>Harga</th>
                        <th>Urutan</th>
                        <th>Status</th>
                        <th class="actions-col" aria-label="Aksi"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($priceItems as $item)
                        <tr>
                            <td>{{ $item->service?->title ?? '-' }}</td>
                            <td>{{ $item->size_info }}</td>
                            <td>{{ $item->price_text }}</td>
                            <td>{{ $item->sort_order }}</td>
                            <td>{{ $item->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                            <td class="actions">
                                <a class="btn btn-outline" href="/admin/prices-form.php?edit={{ $item->id }}">Edit</a>
                                <form method="POST" action="/admin/prices.php" onsubmit="return confirm('Hapus item ini?');">
                                    @csrf
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="{{ $item->id }}">
                                    <button class="btn btn-danger" type="submit">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
@endsection
