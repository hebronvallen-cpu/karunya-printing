@php
    use App\Support\LegacySite;
@endphp

@extends('admin.layout')

@section('content')
<section class="panel">
    <div class="panel-head panel-head--between">
        <div>
            <h2>Daftar Slider Home</h2>
            <p>Atur gambar slider home dan urutan tampil.</p>
        </div>
        <div class="panel-actions">
            <a class="btn btn-primary" href="/admin/home-slides-form.php">+ Tambah Baru</a>
        </div>

    <form class="admin-filters" method="get" action="/admin/home-slides.php">
        <div class="form-group">
            <label>Cari</label>
            <input type="text" name="q" value="{{ $searchQuery }}" placeholder="Judul / caption / path...">
        </div>
        <div class="form-group">
            <label>Status</label>
            <select name="status">
                <option value="all" @selected($statusFilter === 'all')>Semua</option>
                <option value="active" @selected($statusFilter === 'active')>Aktif</option>
                <option value="inactive" @selected($statusFilter === 'inactive')>Nonaktif</option>
            </select>
        </div>
        <button class="btn btn-primary btn-sm" type="submit">Filter</button>
        <a class="btn btn-outline btn-sm" href="/admin/home-slides.php">Reset</a>
    </form>

    <div class="table-scroll">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Judul</th>
                    <th>Caption</th>
                    <th>Gambar</th>
                    <th>Status</th>
                    <th>Urutan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($slides as $slide)
                    <tr>
                        <td>{{ $slide->id }}</td>
                        <td>{{ $slide->title }}</td>
                        <td>{{ $slide->caption }}</td>
                        <td>
                            @if ($slide->image_path)
                                <img src="{{ \App\Support\LegacySite::mediaUrl((string) $slide->image_path) }}" alt="{{ $slide->title }}" style="max-height:60px;border-radius:6px;">
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $slide->is_active ? 'badge-success' : 'badge-muted' }}">
                                {{ $slide->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td>{{ $slide->sort_order }}</td>
                        <td>
                            <a class="btn btn-sm btn-outline" href="/admin/home-slides-form.php?edit={{ $slide->id }}">Edit</a>
                            <form class="inline" method="post" action="/admin/home-slides.php" onsubmit="return confirm('Yakin hapus slide ini?');">
                                @csrf
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="{{ $slide->id }}">
                                <button class="btn btn-sm btn-danger" type="submit">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">Belum ada data slider home.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="panel-foot text-muted">
        Total: {{ $slidesTotalCount }} | Aktif: {{ $slidesActiveCount }} | Nonaktif: {{ $slidesInactiveCount }}
    </div>
</section>
@endsection
