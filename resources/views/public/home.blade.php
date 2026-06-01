@extends('public.layout')

@section('page-styles')
    <link rel="stylesheet" href="{{ asset('css/public/styles/home.css') }}?v={{ is_file(public_path('css/public/styles/home.css')) ? filemtime(public_path('css/public/styles/home.css')) : time() }}">
@endsection

@section('content')
<main class="home-main">
    {{-- Hero Slider --}}
    <div id="hero-slider" class="hero-slider" data-autoplay-ms="5000" role="region" aria-roledescription="carousel" aria-label="Slide utama" tabindex="0">
        <div class="hero-slider-viewport">
            <div class="hero-slider-track" data-hero-track>
                {{-- Clone slide terakhir di awal untuk infinite loop backward --}}
                @if (count($heroSlides) > 1)
                    @php
                        $lastSlide = $heroSlides[count($heroSlides) - 1];
                        $lastImagePath = $lastSlide['path_gambar'] ?? 'gambar/tempat.jpeg';
                        $lastImageUrl = asset($lastImagePath);
                    @endphp
                    <div class="hero-slide" data-hero-slide data-clone="last">
                        <img src="{{ $lastImageUrl }}" alt="{{ $lastSlide['title'] ?? 'Slide ' . count($heroSlides) }}">
                        <div class="hero-slide-overlay">
                            @if (!empty($lastSlide['title']))
                                <div class="hero-slide-logo">{{ $lastSlide['title'] }}</div>
                            @endif
                            @if (!empty($lastSlide['caption']))
                                <div class="hero-slide-copy">{{ $lastSlide['caption'] }}</div>
                            @endif
                        </div>
                    </div>
                @endif

                @foreach ($heroSlides as $index => $slide)
                    @php
                        $imagePath = $slide['path_gambar'] ?? 'gambar/tempat.jpeg';
                        $imageSrc = str_starts_with((string) $imagePath, 'data:image/')
                            ? (string) $imagePath
                            : asset($imagePath);
                    @endphp
                    <div class="hero-slide {{ $index === 0 ? 'is-active' : '' }}" data-hero-slide data-real-index="{{ $index }}">
                        <img src="{{ $imageSrc }}" alt="{{ $slide['title'] ?? 'Slide ' . ($index + 1) }}">

                        <div class="hero-slide-overlay">
                            @if (!empty($slide['title']))
                                <div class="hero-slide-logo">{{ $slide['title'] }}</div>
                            @endif
                            @if (!empty($slide['caption']))
                                <div class="hero-slide-copy">{{ $slide['caption'] }}</div>
                            @endif
                        </div>
                    </div>
                @endforeach

                {{-- Clone slide pertama di akhir untuk infinite loop forward --}}
                @if (count($heroSlides) > 1)
                    @php
                        $firstSlide = $heroSlides[0];
                        $firstImagePath = $firstSlide['path_gambar'] ?? 'gambar/tempat.jpeg';
                        $firstImageUrl = asset($firstImagePath);
                    @endphp
                    <div class="hero-slide" data-hero-slide data-clone="first">
                        <img src="{{ $firstImageUrl }}" alt="{{ $firstSlide['title'] ?? 'Slide 1' }}">
                        <div class="hero-slide-overlay">
                            @if (!empty($firstSlide['title']))
                                <div class="hero-slide-logo">{{ $firstSlide['title'] }}</div>
                            @endif
                            @if (!empty($firstSlide['caption']))
                                <div class="hero-slide-copy">{{ $firstSlide['caption'] }}</div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Slide dots --}}
        <div class="hero-slider-dots" role="tablist" aria-label="Pilih slide">
            @foreach ($heroSlides as $index => $slide)
                <button class="hero-slider-dot {{ $index === 0 ? 'is-active' : '' }}" data-hero-dot data-real-index="{{ $index }}" aria-pressed="{{ $index === 0 ? 'true' : 'false' }}" aria-label="Slide {{ $index + 1 }}" role="tab" tabindex="-1"></button>
            @endforeach
        </div>
    </div>

    <section class="section reveal home-value-section">
        <div class="container">
            <div class="section-head home-value-head">
                <span class="section-kicker">Keunggulan</span>
                <h2>Mengapa Memilih Kami</h2>
                <p>Fokus kami sederhana: hasil rapi, proses jelas, dan layanan yang terasa ringan saat dipesan.</p>
            </div>
            <div class="home-value-grid">
                <article class="panel home-value-card bg-cmyk-cyan">
                    <span class="home-value-index">01</span>
                    <h3>Harga Masuk Akal</h3>
                    <p>Paket cetak bisa disesuaikan dengan kebutuhan dan budget pelanggan.</p>
                </article>
                <article class="panel home-value-card bg-cmyk-magenta">
                    <span class="home-value-index">02</span>
                    <h3>Proses Jelas</h3>
                    <p>Pengerjaan lebih tenang karena alurnya mudah dipahami sejak awal.</p>
                </article>
                <article class="panel home-value-card bg-cmyk-yellow">
                    <span class="home-value-index">03</span>
                    <h3>Hasil Rapi</h3>
                    <p>Detail finishing dijaga supaya hasil cetak siap langsung dipakai.</p>
                </article>
                <article class="panel home-value-card bg-cmyk-black">
                    <span class="home-value-index">04</span>
                    <h3>Mudah Konsultasi</h3>
                    <p>Pelanggan bisa datang langsung atau chat lebih dulu lewat WhatsApp.</p>
                </article>
            </div>
        </div>
    </section>
</main>
@endsection
