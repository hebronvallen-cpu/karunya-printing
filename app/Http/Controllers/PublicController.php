<?php

namespace App\Http\Controllers;

use App\Models\GalleryItem;
use App\Models\HomeSlide;
use App\Models\PriceItem;
use App\Models\Service;
use App\Support\LegacySite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;

class PublicController extends Controller
{
    public function home(Request $request)
    {
        LegacySite::logActivityOncePerSession(
            $request,
            'public_home_view',
            'page_view',
            'home',
            'Membuka halaman utama',
            'Halaman utama website Karunya Printing'
        );

        $slides = HomeSlide::query()
            ->where('aktif', true)
            ->orderBy('urutan_tampil')
            ->orderBy('id_banner_beranda')
            ->get(['judul', 'keterangan', 'lokasi_gambar'])
            ->map(static fn (HomeSlide $slide): array => LegacySite::bannerRow(
                (string) $slide->judul,
                (string) $slide->keterangan,
                (string) $slide->lokasi_gambar
            ))
            ->all();

        if (empty($slides)) {
            $slides = [
                LegacySite::bannerRow('Karunya Printing', 'Jasa percetakan cepat, murah, dan berkualitas', 'gambar/tempat.jpeg'),
            ];
        }

        return view('public.home', array_merge($this->baseViewData('home'), [
            'heroSlides' => $slides,
        ]));
    }

    public function about(Request $request)
    {
        LegacySite::logActivityOncePerSession(
            $request,
            'public_tentang_view',
            'page_view',
            'tentang',
            'Membuka halaman tentang',
            'Halaman tentang Karunya Printing'
        );

        return view('public.about', $this->baseViewData('tentang'));
    }

    public function gallery(Request $request)
    {
        LegacySite::logActivityOncePerSession(
            $request,
            'public_galeri_view',
            'page_view',
            'galeri',
            'Membuka halaman galeri',
            'Halaman galeri Karunya Printing'
        );

        $galleryItems = $this->galleryItems();

        return view('public.gallery', array_merge($this->baseViewData('galeri'), [
            'galleryItems' => $galleryItems,
            'serviceButtons' => $this->galleryServiceButtons($galleryItems),
            'galleryInitialLimit' => 10,
            'hasMoreGalleryItems' => count($galleryItems) > 10,
        ]));
    }

    public function prices(Request $request)
    {
        LegacySite::logActivityOncePerSession(
            $request,
            'public_harga_view',
            'page_view',
            'harga',
            'Membuka halaman harga',
            'Halaman daftar harga Karunya Printing'
        );

        return view('public.prices', array_merge($this->baseViewData('harga'), [
            'priceItems' => $this->priceItems(),
        ]));
    }

    public function services(Request $request)
    {
        LegacySite::logActivityOncePerSession(
            $request,
            'public_layanan_view',
            'page_view',
            'layanan',
            'Membuka halaman layanan',
            'Halaman layanan Karunya Printing'
        );

        return view('public.services', array_merge($this->baseViewData('layanan'), [
            'services' => $this->activeServices(),
        ]));
    }

    public function contact(Request $request)
    {
        LegacySite::logActivityOncePerSession(
            $request,
            'public_kontak_view',
            'page_view',
            'kontak',
            'Membuka halaman kontak',
            'Halaman kontak Karunya Printing'
        );

        return view('public.contact', $this->baseViewData('kontak'));
    }

    public function sitemap()
    {
        $urls = collect($this->publicPages())
            ->map(function (array $page): string {
                return sprintf(
                    "    <url>\n        <loc>%s</loc>\n        <changefreq>%s</changefreq>\n        <priority>%s</priority>\n    </url>",
                    e(url($page['path'])),
                    e($page['changefreq']),
                    e($page['priority'])
                );
            })
            ->implode("\n");

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
        $xml .= $urls . "\n";
        $xml .= "</urlset>\n";

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    public function trackActivity(Request $request)
    {
        $eventKey = LegacySite::sanitizeActivityKey((string) ($request->input('event') ?? $request->input('event_key', '')));
        $pageKey = LegacySite::sanitizeActivityKey((string) ($request->input('page') ?? $request->input('page_key', 'home')));
        $label = LegacySite::normalizeActivityValue((string) $request->input('label', ''), 160);
        $details = LegacySite::normalizeActivityValue((string) $request->input('details', ''), 255);

        $allowedEvents = [
            'gallery_preview',
            'gallery_filter',
            'gallery_show_more',
            'whatsapp_click',
        ];

        if ($eventKey !== '' && in_array($eventKey, $allowedEvents, true)) {
            LegacySite::logActivity(
                $request,
                $eventKey,
                $pageKey !== '' ? $pageKey : 'home',
                $label !== '' ? $label : LegacySite::activityEventLabel($eventKey),
                $details
            );
        }

        return response('', 204);
    }

    private function baseViewData(string $pageKey): array
    {
        $contact = config('legacy.contact');
        $socialLinks = config('legacy.social_links');

        $settings = Cache::rememberForever('site_settings', function () {
            if (!Schema::hasTable('site_settings')) {
                return [];
            }
            return DB::table('site_settings')->pluck('value', 'key')->toArray();
        });

        $phone = $settings['contact_phone'] ?? $contact['phone'];
        $phoneInt = $settings['contact_phone_international'] ?? $contact['phone_international'];
        $email = $settings['contact_email'] ?? $contact['email'];
        $address = $settings['contact_address'] ?? $contact['address'];
        $mapUrl = $settings['contact_map_url'] ?? $contact['map_url'];
        $whatsAppDefaultMessage = $settings['whatsapp_default_message'] ?? config('legacy.whatsapp.default_message');
        $instagramUrl = $this->externalUrl($settings['social_instagram'] ?? $socialLinks['instagram'] ?? '');
        $facebookUrl = $this->externalUrl($settings['social_facebook'] ?? $socialLinks['facebook'] ?? '');
        $tiktokUrl = $this->externalUrl($settings['social_tiktok'] ?? $socialLinks['tiktok'] ?? '');

        $pageMeta = [
            'home' => [
                'pageTitle' => 'Karunya Printing | Jasa Percetakan Cepat & Murah',
                'pageDescription' => 'Website profil Karunya Printing: layanan percetakan lengkap, galeri hasil, dan kontak WhatsApp cepat.',
                'bodyClass' => 'page-home',
                'withLightbox' => false,
                'path' => '/home.php',
            ],
            'tentang' => [
                'pageTitle' => 'Tentang | Karunya Printing',
                'pageDescription' => 'Profil Karunya Printing: sejarah, visi, dan cara kami bekerja.',
                'bodyClass' => 'page-tentang',
                'withLightbox' => false,
                'path' => '/tentang.php',
            ],
            'galeri' => [
                'pageTitle' => 'Galeri | Karunya Printing',
                'pageDescription' => 'Galeri hasil cetak dan layanan dari Karunya Printing. Lihat contoh karya kami.',
                'bodyClass' => 'page-galeri',
                'withLightbox' => true,
                'path' => '/galeri.php',
            ],
            'harga' => [
                'pageTitle' => 'Harga | Karunya Printing',
                'pageDescription' => 'Daftar harga layanan percetakan Karunya Printing. Harga mulai dan detail ukuran.',
                'bodyClass' => 'page-harga',
                'withLightbox' => false,
                'path' => '/harga.php',
            ],
            'layanan' => [
                'pageTitle' => 'Layanan | Karunya Printing',
                'pageDescription' => 'Layanan percetakan lengkap dari Karunya Printing: spanduk, stiker, brosur, dan lainnya.',
                'bodyClass' => 'page-layanan',
                'withLightbox' => false,
                'path' => '/layanan.php',
            ],
            'kontak' => [
                'pageTitle' => 'Kontak | Karunya Printing',
                'pageDescription' => 'Kontak Karunya Printing: WhatsApp, alamat, email, dan lokasi.',
                'bodyClass' => 'page-kontak',
                'withLightbox' => false,
                'path' => '/kontak.php',
            ],
        ][$pageKey] ?? [
            'pageTitle' => 'Karunya Printing',
            'pageDescription' => 'Website Karunya Printing.',
            'bodyClass' => '',
            'withLightbox' => false,
            'path' => '/home.php',
        ];

        $canonicalUrl = url($pageMeta['path']);
        $openGraphImage = asset('gambar/tempat.jpeg');
        $structuredData = $this->localBusinessStructuredData(
            $phone,
            $phoneInt,
            $email,
            $address,
            $mapUrl,
            array_filter([$instagramUrl, $facebookUrl, $tiktokUrl])
        );

        return [
            'pageKey' => $pageKey,
            'pageTitle' => $pageMeta['pageTitle'],
            'pageDescription' => $pageMeta['pageDescription'],
            'bodyClass' => $pageMeta['bodyClass'],
            'withLightbox' => $pageMeta['withLightbox'],
            'contactPhone' => $phone,
            'contactPhoneInternational' => $phoneInt,
            'contactEmail' => $email,
            'contactAddress' => $address,
            'contactMapUrl' => $mapUrl,
            'instagramUrl' => $instagramUrl,
            'facebookUrl' => $facebookUrl,
            'tiktokUrl' => $tiktokUrl,
            'whatsAppUrl' => $this->whatsAppUrl($phoneInt, $whatsAppDefaultMessage),
            'whatsAppDefaultMessage' => $whatsAppDefaultMessage,
            'canonicalUrl' => $canonicalUrl,
            'openGraphImage' => $openGraphImage,
            'structuredData' => $structuredData,
            'adminLoginUrl' => url('/admin/?key=' . config('legacy.admin_access_key')),
            'activityUrl' => url('/track-activity.php'),
        ];
    }

    private function publicPages(): array
    {
        return [
            ['path' => '/home.php', 'changefreq' => 'weekly', 'priority' => '1.0'],
            ['path' => '/tentang.php', 'changefreq' => 'monthly', 'priority' => '0.7'],
            ['path' => '/layanan.php', 'changefreq' => 'weekly', 'priority' => '0.9'],
            ['path' => '/harga.php', 'changefreq' => 'weekly', 'priority' => '0.8'],
            ['path' => '/galeri.php', 'changefreq' => 'weekly', 'priority' => '0.8'],
            ['path' => '/kontak.php', 'changefreq' => 'monthly', 'priority' => '0.9'],
        ];
    }

    private function externalUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }

        if (preg_match('~^https?://~i', $url) === 1) {
            return $url;
        }

        return 'https://' . ltrim($url, '/');
    }

    private function whatsAppUrl(string $phoneInternational, string $message): string
    {
        $phoneInternational = preg_replace('/\D+/', '', $phoneInternational) ?? '';

        return 'https://wa.me/' . $phoneInternational . '?text=' . rawurlencode($message);
    }

    private function localBusinessStructuredData(string $phone, string $phoneInternational, string $email, string $address, string $mapUrl, array $sameAs): array
    {
        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => 'Karunya Printing',
            'url' => url('/home.php'),
            'image' => asset('gambar/tempat.jpeg'),
            'logo' => asset('gambar/logo.png'),
            'telephone' => '+' . preg_replace('/\D+/', '', $phoneInternational),
            'email' => $email,
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $address,
                'addressLocality' => 'Maumere',
                'addressRegion' => 'Nusa Tenggara Timur',
                'addressCountry' => 'ID',
            ],
            'areaServed' => 'Sikka, Nusa Tenggara Timur',
            'sameAs' => array_values($sameAs),
            'hasMap' => $mapUrl,
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'telephone' => '+' . preg_replace('/\D+/', '', $phoneInternational),
                'contactType' => 'customer service',
                'availableLanguage' => ['id'],
            ],
        ], static fn ($value): bool => $value !== '' && $value !== []);
    }

    private function activeServices(): array
    {
        $items = Service::query()
            ->where('aktif', true)
            ->orderBy('urutan_tampil')
            ->orderBy('id_layanan')
            ->get(['judul', 'deskripsi'])
            ->map(static fn (Service $service): array => LegacySite::serviceRow(
                (string) $service->judul,
                (string) $service->deskripsi
            ))
            ->all();

        if (! empty($items)) {
            return $items;
        }

        return array_map(static function (array $row) {
            return LegacySite::serviceRow(
                (string) ($row['title'] ?? $row['judul'] ?? ''),
                (string) ($row['description'] ?? $row['deskripsi'] ?? '')
            );
        }, LegacySite::staticServices());
    }


    private function priceItems(): array
    {
        $items = PriceItem::query()
            ->where('aktif', true)
            ->with('service')
            ->orderBy('urutan_tampil')
            ->orderBy('id_harga_layanan')
            ->get(['id_harga_layanan', 'id_layanan', 'info_ukuran', 'teks_harga'])
            ->map(static fn (PriceItem $item): array => LegacySite::priceRow(
                (string) ($item->service?->judul ?? ''),
                (string) $item->info_ukuran,
                (string) $item->teks_harga
            ))
            ->all();

        return $items !== [] ? $items : LegacySite::fallbackPriceItems();
    }

    private function galleryItems(): array
    {
        // Ambil kategori dari relasi service melalui id_layanan
        $items = GalleryItem::query()
            ->where('aktif', true)
            ->with('service')
            ->orderBy('urutan_tampil')
            ->orderBy('id_galeri_layanan')
            ->get(['id_galeri_layanan', 'judul', 'lokasi_gambar', 'id_layanan'])
            ->map(static function (GalleryItem $item): array {
                return [
                    'judul' => (string) $item->judul,
                    'lokasi_gambar' => (string) $item->lokasi_gambar,
                    'path_gambar' => (string) $item->lokasi_gambar,
                    // id_layanan sebagai category_id
                    'category_id' => (string) ($item->id_layanan ?? ''),
                ];
            })
            ->all();

        return $items !== [] ? $items : LegacySite::fallbackGalleryItems();
    }


    private function galleryServiceButtons(array $galleryItems): array
    {
        // Ikuti layanan yang ada (tabel layanan) dan status aktif.
        $serviceButtons = ['all' => 'Semua'];

        $services = Service::query()
            ->where('aktif', true)
            ->orderBy('urutan_tampil')
            ->orderBy('id_layanan')
            ->get(['id_layanan', 'judul']);

        foreach ($services as $service) {
            $serviceId = (string) $service->id_layanan;
            $serviceButtons[$serviceId] = (string) $service->judul;
        }

        return $serviceButtons;
    }
}
