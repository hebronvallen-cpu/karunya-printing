@extends('public.layout')

@section('page-styles')
    <link rel="stylesheet" href="{{ asset('css/public/styles/services.css') }}?v={{ is_file(public_path('css/public/styles/services.css')) ? filemtime(public_path('css/public/styles/services.css')) : time() }}">
@endsection

@section('content')
<main>
    <section class="section section-soft reveal">
        <div class="container">
            <div class="section-head">
                <h2>Layanan / Produk</h2>
                <p>Jenis layanan utama dari Karunya Printing ditampilkan dalam kartu yang rapi dan mudah dibaca.</p>
            </div>

            <div class="card-grid services-grid">
                @php
                    $cmykClasses = ['bg-cmyk-cyan', 'bg-cmyk-magenta', 'bg-cmyk-yellow', 'bg-cmyk-black'];
                @endphp
                @foreach ($services as $index => $service)
                    <article class="panel service-card {{ $cmykClasses[$index % count($cmykClasses)] }}">
                        <div class="service-card-content">
                            <h3>{{ $service['judul'] }}</h3>
                            <p>{{ $service['deskripsi'] }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
</main>
@endsection
