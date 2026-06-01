@extends('admin.layout')

@section('page-styles-admin')
    <link rel="stylesheet" href="{{ url('/css/admin/styles/dashboard.css') }}?v={{ filemtime(public_path('css/admin/styles/dashboard.css')) }}">
@endsection

@section('content')
<section class="panel">
    <div class="panel-head">
        <div>
            <h2>Dashboard Overview</h2>
            <p>Ringkasan konten & analytics website Karunya Printing</p>
        </div>
    </div>
    <div class="stats-grid">
        <article class="panel stat-card reveal" data-tilt>
            <h3>{{ $galleryCount }}</h3>
            <p>Total Galeri</p>
        </article>
        <article class="panel stat-card reveal" data-tilt>
            <h3>{{ $priceCount }}</h3>
            <p>Total Harga</p>
        </article>
        <article class="panel stat-card reveal" data-tilt>
            <h3>{{ $homeSlidesCount }}</h3>
            <p>Slide Home</p>
        </article>
        <article class="panel stat-card reveal" data-tilt>
            <h3>{{ $todayViews }}</h3>
            <p>Hari Ini</p>
        </article>
        <article class="panel stat-card reveal" data-tilt>
            <h3>{{ $weeklyUniqueVisitors }}</h3>
            <p>Unik 7 Hari</p>
        </article>
        <article class="panel stat-card reveal" data-tilt>
            <h3>{{ $weeklyWhatsappClicks }}</h3>
            <p>WhatsApp 7 Hari</p>
        </article>
    </div>
</section>

<section class="panel">
    <div class="panel-head">
        <div>
            <h2>Aktivitas User 7 Hari</h2>
            <p class="panel-subtitle">Ringkasan interaksi user di website publik selama 7 hari terakhir.</p>
        </div>
    </div>

    @if ($activityBreakdown->isEmpty())
        <p>Belum ada aktivitas user yang tercatat.</p>
    @else
        <div class="stats-grid">
            @foreach ($activityBreakdown as $activity)
                <article class="stat-card">
                    <h3>{{ (int) $activity->total }}</h3>
                    <p>{{ \App\Support\LegacySite::activityEventLabel((string) $activity->event_key) }}</p>
                </article>
            @endforeach
        </div>
    @endif
</section>

<section class="panel">
    <div class="panel-head">
        <div>
            <h2>Aktivitas Website Terbaru</h2>
            <p class="panel-subtitle">Aktivitas user yang terakhir tercatat dari website publik.</p>
        </div>
    </div>

    @if ($recentActivities->isEmpty())
        <p>Belum ada aktivitas user yang tercatat.</p>
    @else
        <div class="table-scroll">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Aktivitas</th>
                        <th>Detail</th>
                        <th>Halaman</th>
                        <th>IP</th>
                        <th>Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recentActivities as $activity)
                        <tr>
                            <td>{{ $activity->label ?: \App\Support\LegacySite::activityEventLabel((string) $activity->event_key) }}</td>
                            <td>{{ $activity->details ?: '-' }}</td>
                            <td>{{ $activity->page_key ?: '-' }}</td>
                            <td>{{ $activity->ip_address ?: '-' }}</td>
                            <td>{{ $activity->created_at }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>

<section class="panel">
    <h2>Update Galeri Terbaru</h2>
    @if ($latestGallery->isEmpty())
        <p>Belum ada data galeri.</p>
    @else
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Jenis Layanan</th>
                    <th>Diupdate</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($latestGallery as $row)
                    <tr>
                        <td>{{ $row->title }}</td>
                        <td>{{ $row->updated_at }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</section>

<section class="panel">
    <h2>Update Harga Terbaru</h2>
    @if ($latestPrices->isEmpty())
        <p>Belum ada data harga.</p>
    @else
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Layanan</th>
                    <th>Harga</th>
                    <th>Diupdate</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($latestPrices as $row)
                    <tr>
                        <td>{{ $row->service_name }}</td>
                        <td>{{ $row->price_text }}</td>
                        <td>{{ $row->updated_at }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</section>

<section class="panel">
    <h2>Update Slider Home Terbaru</h2>
    @if ($latestHomeSlides->isEmpty())
        <p>Belum ada slide home.</p>
    @else
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Judul Slide</th>
                    <th>Diupdate</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($latestHomeSlides as $row)
                    <tr>
                        <td>{{ $row->title }}</td>
                        <td>{{ $row->updated_at }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</section>
@endsection
