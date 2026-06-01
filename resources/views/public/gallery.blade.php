@extends('public.layout')

@section('page-styles')
    <link rel="stylesheet" href="{{ asset('css/public/styles/gallery.css') }}?v={{ is_file(public_path('css/public/styles/gallery.css')) ? filemtime(public_path('css/public/styles/gallery.css')) : time() }}">
@endsection

@section('content')
<main>
    <section class="section reveal">
        <div class="container">
            <div class="section-head">
                <h2>Galeri</h2>
                <p>Galeri hasil cetak dan layanan dari Karunya Printing.</p>
            </div>

            <div class="gallery-toolbar" role="tablist" aria-label="Filter galeri">
                <div class="gallery-toolbar__scroller" tabindex="0" aria-label="geser kategori">
                    <div class="gallery-toolbar__items">
                        @foreach ($serviceButtons as $slug => $label)
                            <button
                                class="filter-btn {{ $slug === 'all' ? 'is-active' : '' }}"
                                type="button"
                                data-filter="{{ $slug }}"
                            >
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="gallery-grid" id="gallery-track">
                @php
                    $cmykClasses = ['bg-cmyk-cyan', 'bg-cmyk-magenta', 'bg-cmyk-yellow', 'bg-cmyk-black'];
                @endphp
                @foreach ($galleryItems as $index => $item)
                    @php
                        $imagePath = (string) $item['path_gambar'];
                        $imageUrl = str_starts_with($imagePath, 'data:image/')
                            ? $imagePath
                            : (str_starts_with($imagePath, 'http') ? $imagePath : asset($imagePath));
                        $cmykClass = $cmykClasses[$index % count($cmykClasses)];
                    @endphp
                    <figure class="panel gallery-item {{ $cmykClass }}" data-service="{{ (string) ($item['category_id'] ?? '') }}">
                        <button class="gallery-trigger" type="button" data-image="{{ $imageUrl }}" data-title="{{ $item['judul'] }}">
                            <img src="{{ $imageUrl }}" alt="Hasil {{ $item['judul'] }}">
                        </button>
                        <figcaption>
                            <span>{{ $item['judul'] }}</span>
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        </div>
    </section>
</main>
@endsection
