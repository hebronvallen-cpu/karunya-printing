@php
    $styleVersion = is_file(public_path('css/public/base.css'))
        ? filemtime(public_path('css/public/base.css'))
        : time();
    $scriptVersion = is_file(public_path('site.js'))
        ? filemtime(public_path('site.js'))
        : time();
    $pageTitle = $pageTitle ?? 'Karunya Printing';
    $pageDescription = $pageDescription ?? 'Website Karunya Printing.';
    $bodyClass = trim($bodyClass ?? '');
    $brandHref = url('/home.php');
    $navLinks = [
        ['label' => 'Home', 'href' => url('/home.php'), 'active' => ($pageKey ?? '') === 'home'],
        ['label' => 'Tentang', 'href' => url('/tentang.php'), 'active' => ($pageKey ?? '') === 'tentang'],
        ['label' => 'Layanan', 'href' => url('/layanan.php'), 'active' => ($pageKey ?? '') === 'layanan'],
        ['label' => 'Harga', 'href' => url('/harga.php'), 'active' => ($pageKey ?? '') === 'harga'],
        ['label' => 'Galeri', 'href' => url('/galeri.php'), 'active' => ($pageKey ?? '') === 'galeri'],
        ['label' => 'Kontak', 'href' => url('/kontak.php'), 'active' => ($pageKey ?? '') === 'kontak'],
    ];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="id_ID">
    <meta property="og:site_name" content="Karunya Printing">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:image" content="{{ $openGraphImage }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">
    <meta name="twitter:image" content="{{ $openGraphImage }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Allura&family=Bebas+Neue&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('style-modern.css') }}?v={{ is_file(public_path('style-modern.css')) ? filemtime(public_path('style-modern.css')) : time() }}">
    @hasSection('page-styles')
        @yield('page-styles')
    @endif
    <link rel="stylesheet" href="{{ asset('style-responsive.css') }}?v={{ is_file(public_path('style-responsive.css')) ? filemtime(public_path('style-responsive.css')) : time() }}">
    <script type="application/ld+json">
        {!! json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>
    <style>
        @media (max-width: 768px) {
            .footer-admin-block {
                display: flex;
                flex-direction: column;
                align-items: center;
                text-align: center;
            }
        }
    </style>
</head>
<body @class([$bodyClass => $bodyClass !== '']) data-activity-url="{{ $activityUrl }}" data-activity-page="{{ $pageKey }}">
    <header class="header" id="site-header">
        <div class="container nav-wrap">
            <a href="{{ $brandHref }}" class="brand">
                <img src="{{ asset('gambar/logo.png') }}" alt="Karunya Printing" class="brand-logo">
                <span class="brand-title wordmark header-wordmark" aria-label="Karunya Printing">
                    <span class="wordmark-script">Karunya Printing</span>
                </span>
            </a>
            <button class="nav-toggle" type="button" id="nav-toggle" aria-expanded="false" aria-controls="primary-nav" aria-label="Buka menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <div class="nav-actions">
                <nav id="primary-nav" class="nav-links" aria-label="Navigasi utama">
                    @foreach ($navLinks as $navLink)
                        <a @class(['nav-link', 'active' => $navLink['active']]) href="{{ $navLink['href'] }}">
                            {{ $navLink['label'] }}
                        </a>
                    @endforeach
                </nav>
            </div>
        </div>
    </header>
    <button class="nav-backdrop" id="nav-backdrop" type="button" aria-label="Tutup menu navigasi" tabindex="-1"></button>

    <div id="main-content" class="page-content" role="main">
        @yield('content')
    </div>


    <footer class="footer">
        <div class="container footer-grid">
            <div class="footer-brand-block">
                <div class="footer-brand">
                    <img src="{{ asset('gambar/logo.png') }}" alt="Karunya Printing" class="brand-logo brand-logo-footer">
                    <div class="footer-wordmark wordmark header-wordmark" aria-label="Karunya Printing">
                        <span class="wordmark-script">Karunya Printing</span>
                    </div>
                </div>
                <p>Partner percetakan cepat, murah, dan berkualitas untuk kebutuhan bisnis maupun personal.</p>
            </div>
            <div>
                <h4>Sosial Media</h4>
                <div class="social-links">
                    <a href="{{ $instagramUrl }}" target="_blank" rel="noopener" aria-label="Instagram" class="social-link social-instagram">
                        <svg class="social-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <defs>
                                <linearGradient id="ig-grad" x1="0%" y1="100%" x2="100%" y2="0%">
                                    <stop offset="0%" stop-color="#f58529"/>
                                    <stop offset="30%" stop-color="#dd2a7b"/>
                                    <stop offset="60%" stop-color="#8134af"/>
                                    <stop offset="100%" stop-color="#515bd4"/>
                                </linearGradient>
                            </defs>
                            <rect x="2" y="2" width="20" height="20" rx="5" ry="5" fill="none" stroke="url(#ig-grad)" stroke-width="2"/>
                            <circle cx="12" cy="12" r="5" fill="none" stroke="url(#ig-grad)" stroke-width="2"/>
                            <circle cx="17.5" cy="6.5" r="1.5" fill="url(#ig-grad)"/>
                        </svg>
                    </a>
                    <a href="{{ $facebookUrl }}" target="_blank" rel="noopener" aria-label="Facebook" class="social-link social-facebook">
                        <svg class="social-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <g transform="translate(0, 1.5)">
                                <path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3V2z" fill="none" stroke="#1877F2" stroke-width="2" stroke-linejoin="round"/>
                            </g>
                        </svg>
                    </a>
                    <a href="{{ $tiktokUrl }}" target="_blank" rel="noopener" aria-label="TikTok" class="social-link social-tiktok">
                        <svg class="social-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <g transform="translate(-0.5, 1.5)">
                                <path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5" fill="none" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </g>
                        </svg>
                    </a>
                </div>
            </div>
            <div>
                <h4>Kontak Cepat</h4>
                <p><a href="tel:+{{ $contactPhoneInternational }}">{{ $contactPhone }}</a><br><a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a><br><a href="{{ $contactMapUrl }}" target="_blank" rel="noopener">Lihat Lokasi</a></p>
            </div>
            <div class="footer-admin-block">
                <h4>Admin</h4>
                <p class="footer-admin-note">Masuk ke panel internal untuk mengelola konten website.</p>
                <a class="footer-admin-link" href="{{ $adminLoginUrl }}">Masuk Admin</a>
            </div>
        </div>
    </footer>

    @if (!empty($withLightbox))
        <div class="lightbox" id="lightbox" hidden>
            <button class="lightbox-backdrop" type="button" data-close-lightbox aria-label="Tutup preview"></button>
            <figure class="lightbox-content" role="dialog" aria-modal="true" aria-label="Preview gambar galeri">
                <button class="lightbox-close" type="button" data-close-lightbox aria-label="Tutup">&times;</button>
                <img id="lightbox-image" src="" alt="">
                <figcaption id="lightbox-caption"></figcaption>
            </figure>
        </div>
    @endif

    <button class="to-top" id="to-top" type="button" aria-label="Kembali ke atas">&uarr;</button>
    <a class="wa-float" href="{{ $whatsAppUrl }}" target="_blank" rel="noopener" aria-label="Chat WhatsApp">WhatsApp</a>
    <script src="{{ asset('site.js') }}?v={{ $scriptVersion }}"></script>
</body>
</html>
