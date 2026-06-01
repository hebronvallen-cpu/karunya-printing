@php
    use App\Support\LegacySite;
@endphp

@extends('admin.layout')

@section('page-styles-admin')
    <link rel="stylesheet" href="/css/admin/styles/gallery.css?v={{ filemtime(public_path('css/admin/styles/gallery.css')) }}">
@endsection

@section('content')
<section class="panel">
    <div class="panel-head panel-head--between">
        <div>
            <h2>Daftar Galeri</h2>
            <p class="panel-subtitle">Menampilkan {{ $galleryItems->count() }} dari {{ $galleryTotalCount }} data galeri.</p>
        </div>
        <div class="panel-actions">
            <a class="btn btn-primary" href="/admin/gallery-form.php">+ Tambah Galeri</a>
        </div>
    </div>

    <form method="GET" class="admin-filters admin-filters-grid" action="/admin/gallery.php">
        <div class="field-group">
            <label for="gallery-service">Filter layanan</label>
            <select id="gallery-service" name="service">
                <option value="all">Semua layanan</option>
                @foreach ($serviceOptions as $service)
                    <option value="{{ $service }}" @selected($serviceFilter === $service)>{{ $service }}</option>
                @endforeach
            </select>
        </div>

        <div class="field-group">
            <label for="gallery-status">Filter status</label>
            <select id="gallery-status" name="status">
                <option value="all" @selected($statusFilter === 'all')>Semua status</option>
                <option value="active" @selected($statusFilter === 'active')>Aktif</option>
                <option value="inactive" @selected($statusFilter === 'inactive')>Nonaktif</option>
            </select>
        </div>

        <div class="field-actions">
            <button class="btn btn-primary" type="submit">Terapkan</button>
            <a class="btn btn-outline" href="/admin/gallery.php">Reset</a>
        </div>
    </form>

    @if ($galleryItems->isEmpty())
        <p>Belum ada data galeri.</p>
    @else
        <div class="admin-list-shell" data-admin-pager data-page-size="10">
            <div class="table-scroll">
                <table class="admin-table admin-table--fixed">


                    <thead>
                        <tr>
                            <th>Preview</th>
                            <th>Jenis Layanan</th>
                            <th>File</th>
                            <th>Urutan</th>
                            <th>Status</th>
                            <th class="actions-col" aria-label="Aksi"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($galleryItems as $item)
                            @php
                                $itemPath = LegacySite::mediaUrl((string) $item->image_path);
                                // Jika tersimpan sebagai data URI (base64), jangan dibungkus url() karena akan rusak.
                                $itemUrl = str_starts_with($itemPath, 'data:')
                                    ? $itemPath
                                    : (str_starts_with($itemPath, 'http') ? $itemPath : $itemPath);
                            @endphp

                            <tr>
                                <td>
                                    <img class="thumb" src="{{ $itemUrl }}" alt="{{ $item->title }}"
                                         title="image_path: {{ (string) $item->image_path }}\nitemUrl: {{ $itemUrl }}"
                                         onerror="this.style.display='none';">
                                </td>


                                <td class="truncate">{{ $item->title }}</td>
                                <td class="truncate">{{ $item->image_path }}</td>

                                <td>{{ $item->sort_order }}</td>
                                <td>{{ $item->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                                <td class="actions">
                                    <a class="btn btn-outline" href="/admin/gallery-form.php?edit={{ $item->id }}">Edit</a>
                                    <form method="POST" action="/admin/gallery.php" onsubmit="return confirm('Hapus item ini?');">
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

            <div class="admin-list-pager" data-admin-pager-controls hidden>
                <button class="admin-list-pager__btn" type="button" data-admin-pager-prev aria-label="Lihat 10 data sebelumnya">
                    <span class="pager-triangle pager-triangle--prev" aria-hidden="true"></span>
                </button>
                <span class="admin-list-pager__status" data-admin-pager-status></span>
                <button class="admin-list-pager__btn" type="button" data-admin-pager-next aria-label="Lihat 10 data selanjutnya">
                    <span class="pager-triangle pager-triangle--next" aria-hidden="true"></span>
                </button>
            </div>
        </div>
    @endif
</section>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-admin-pager]').forEach(pager => {
            const rows = Array.from(pager.querySelectorAll('tbody tr'));
            const pageSize = Number(pager.dataset.pageSize || '10');
            const controls = pager.querySelector('[data-admin-pager-controls]');
            const prevBtn = pager.querySelector('[data-admin-pager-prev]');
            const nextBtn = pager.querySelector('[data-admin-pager-next]');
            const status = pager.querySelector('[data-admin-pager-status]');
            let pageIndex = 0;

            if (!rows.length || !controls || !prevBtn || !nextBtn || !status) {
                return;
            }

            const renderPage = () => {
                const totalPages = Math.ceil(rows.length / pageSize);
                const start = pageIndex * pageSize;
                const end = Math.min(start + pageSize, rows.length);

                rows.forEach((row, index) => {
                    row.hidden = index < start || index >= end;
                });

                controls.hidden = rows.length <= pageSize;
                prevBtn.disabled = pageIndex === 0;
                nextBtn.disabled = pageIndex >= totalPages - 1;
                status.textContent = `${start + 1}-${end} dari ${rows.length}`;
            };

            prevBtn.addEventListener('click', () => {
                pageIndex = Math.max(0, pageIndex - 1);
                renderPage();
            });

            nextBtn.addEventListener('click', () => {
                pageIndex = Math.min(Math.ceil(rows.length / pageSize) - 1, pageIndex + 1);
                renderPage();
            });

            renderPage();
        });
    });
</script>
@endsection
