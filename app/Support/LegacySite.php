<?php

namespace App\Support;

use App\Models\HomeSlide;
use App\Models\SiteActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

class LegacySite
{
    public static function galleryServiceOptions(): array
    {
        return [
            'Spanduk / Baliho',
            'X-Banner / Roll Banner',
            'Cutting Stiker',
            'Sablon Baju',
            'ID Card / Name Tag',
            'Undangan',
            'Stempel',
            'Pas Foto',
            'Scan Dokumen',
            'Print',
            'Cetak Foto',
            'Batu Nisan / Prasasti',
            'Cetak Foto di Keramik',
            'Neon Box / Plang Nama',
            'Brosur',
            'Desain & Printing Media Promosi',
            'Desain & Cetak Outdoor / Indoor',
        ];
    }

    public static function fallbackPriceItems(): array
    {
        return [
            self::priceRow('Spanduk Flexi 280gr', 'per m2', 'Rp 35.000'),
            self::priceRow('Baliho Outdoor', 'per m2', 'Rp 55.000'),
            self::priceRow('X-Banner', '60 x 160 cm + stand', 'Rp 85.000'),
            self::priceRow('Cutting Stiker', 'per m2', 'Rp 120.000'),
            self::priceRow('Undangan', '100 lembar', 'Rp 250.000'),
            self::priceRow('ID Card PVC', 'per pcs', 'Rp 15.000'),
            self::priceRow('Print Warna', 'A4 / lembar', 'Rp 2.500'),
            self::priceRow('Neon Box', '100 x 60 cm', 'Mulai Rp 650.000'),
        ];
    }

    public static function fallbackGalleryItems(): array
    {
        $root = base_path();
        $names = [
            'Brosur.jpg', 'Cutting Stiker.jpg', 'Sablon Baju.jpg', 'Stempel.jpg', 'Undangan.jpg', 'Cetak Foto.jpg', 'Scan Dokumen.jpg', 'Pas Foto.jpg',
        ];
        $rows = [];
        foreach ($names as $name) {
            $file = $root . '/public/uploads/gallery/' . $name;
            $path = 'uploads/gallery/' . $name;
            if (is_file($file)) {
                $mime = mime_content_type($file) ?: 'image/jpeg';
                $data = base64_encode(file_get_contents($file));
                $path = 'data:' . $mime . ';base64,' . $data;
            }
            $rows[] = self::galleryRow(pathinfo($name, PATHINFO_FILENAME), $path);
        }

        return $rows;
    }

    public static function fallbackHomeSlides(): array
    {
        $root = base_path();
        $path = 'gambar/tempat.jpeg';
        $file = $root . '/public/' . $path;

        if (! is_file($file) && is_file($root . '/public/temp_uploads/logo.png')) {
            $path = 'temp_uploads/logo.png';
            $file = $root . '/public/' . $path;
        }

        if (is_file($file)) {
            $mime = mime_content_type($file) ?: 'image/jpeg';
            $data = base64_encode(file_get_contents($file));
            $path = 'data:' . $mime . ';base64,' . $data;
        }

        return [
            [
                'title' => 'Karunya Printing',
                'judul' => 'Karunya Printing',
                'caption' => 'Layanan percetakan cepat dan rapi untuk promosi usaha serta kebutuhan acara.',
                'keterangan' => 'Layanan percetakan cepat dan rapi untuk promosi usaha serta kebutuhan acara.',
                'image_path' => $path,
                'path_gambar' => $path,
                'lokasi_gambar' => $path,
            ],
            [
                'title' => 'Spanduk dan Baliho',
                'judul' => 'Spanduk dan Baliho',
                'caption' => 'Produksi media promosi outdoor dengan warna tajam dan ukuran sesuai kebutuhan.',
                'keterangan' => 'Produksi media promosi outdoor dengan warna tajam dan ukuran sesuai kebutuhan.',
                'image_path' => $path,
                'path_gambar' => $path,
                'lokasi_gambar' => $path,
            ],
        ];
    }

    public static function staticServices(): array
    {
        return [
            self::serviceRow('Spanduk / Baliho', 'Media promosi outdoor berukuran besar untuk toko, event, dan kampanye.'),
            self::serviceRow('X-Banner / Roll Banner', 'Banner display portable untuk promosi indoor maupun pameran.'),
            self::serviceRow('Cutting Stiker', 'Untuk branding etalase, kendaraan, label produk, dan dekorasi visual.'),
            self::serviceRow('Sablon Baju', 'Sablon kaos komunitas, seragam, dan kebutuhan promosi usaha.'),
            self::serviceRow('ID Card / Name Tag', 'Pembuatan identitas karyawan, panitia acara, dan name tag custom.'),
            self::serviceRow('Undangan', 'Undangan acara resmi maupun keluarga dengan desain sesuai permintaan.'),
            self::serviceRow('Stempel', 'Stempel usaha, kantor, dan custom dengan hasil cetak jelas.'),
            self::serviceRow('Pas Foto', 'Layanan pas foto cepat untuk kebutuhan sekolah, kerja, dan dokumen.'),
            self::serviceRow('Scan Dokumen', 'Digitalisasi dokumen dengan hasil rapi untuk arsip dan pengiriman online.'),
            self::serviceRow('Print, dll', 'Layanan print harian untuk berbagai kebutuhan pribadi maupun bisnis.'),
            self::serviceRow('Cetak Foto', 'Cetak foto dokumen dan foto umum dengan warna tajam.'),
            self::serviceRow('Batu Nisan / Prasasti', 'Pengerjaan tulisan batu nisan dan batu prasasti sesuai permintaan.'),
            self::serviceRow('Cetak Foto di Keramik', 'Cetak foto custom pada media keramik dengan kualitas warna tahan lama.'),
            self::serviceRow('Neon Box / Plang Nama', 'Pembuatan identitas visual toko agar usaha lebih mudah dikenali.'),
            self::serviceRow('Brosur', 'Cetak brosur promosi untuk usaha, acara, dan produk.'),
            self::serviceRow('Desain & Printing Media Promosi', 'Konsep desain sampai produksi materi branding usaha.'),
            self::serviceRow('Desain & Cetak Outdoor / Indoor', 'Layanan desain sekaligus cetak untuk media promosi luar dan dalam ruangan.'),
        ];
    }

    public static function slugify(string $value): string
    {
        $value = trim(mb_strtolower($value));
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
        $value = trim($value, '-');

        return $value !== '' ? $value : 'lainnya';
    }

    public static function normalizeActivityValue(string $value, int $maxLength = 255): string
    {
        $value = trim(preg_replace('/\s+/', ' ', $value) ?? '');

        return Str::limit($value, $maxLength, '');
    }

    public static function sanitizeActivityKey(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9_-]+/', '_', $value) ?? '';

        return trim($value, '_-');
    }

    public static function activityEventLabel(string $eventKey): string
    {
        return [
            'page_view' => 'Membuka halaman',
            'gallery_preview' => 'Membuka preview galeri',
            'gallery_filter' => 'Memakai filter galeri',
            'gallery_show_more' => 'Klik lihat lainnya',
            'whatsapp_click' => 'Klik tombol WhatsApp',
        ][self::sanitizeActivityKey($eventKey)] ?? 'Aktivitas user';
    }

    public static function logActivity(Request $request, string $eventKey, string $pageKey, string $label = '', string $details = ''): void
    {
        $eventKey = self::sanitizeActivityKey($eventKey);
        if ($eventKey === '') {
            return;
        }

        SiteActivityLog::create([
            'kunci_peristiwa' => $eventKey,
            'kunci_halaman' => self::sanitizeActivityKey($pageKey),
            'label' => self::normalizeActivityValue($label, 160),
            'detail' => self::normalizeActivityValue($details, 255),
            'alamat_ip' => self::normalizeActivityValue((string) $request->ip(), 45),
            'agen_pengguna' => self::normalizeActivityValue((string) $request->userAgent(), 255),
            'waktu_dibuat' => now(),
        ]);
    }

    public static function logActivityOncePerSession(
        Request $request,
        string $sessionKey,
        string $eventKey,
        string $pageKey,
        string $label = '',
        string $details = '',
        int $ttlSeconds = 1800
    ): void {
        $sessionKey = self::sanitizeActivityKey($sessionKey);
        if ($sessionKey === '') {
            self::logActivity($request, $eventKey, $pageKey, $label, $details);

            return;
        }

        $lastLoggedAt = (int) $request->session()->get("_activity_once.{$sessionKey}", 0);
        if ($lastLoggedAt > 0 && (time() - $lastLoggedAt) < $ttlSeconds) {
            return;
        }

        self::logActivity($request, $eventKey, $pageKey, $label, $details);
        $request->session()->put("_activity_once.{$sessionKey}", time());
    }

    public static function mediaUrl(string $path): string
    {
        $path = trim($path);
        if ($path === '') {
            return '';
        }

        if (preg_match('~^https?://~i', $path) === 1) {
            return $path;
        }

        // If path is a data URI (stored in DB), return as-is so it can be used directly in img src
        if (str_starts_with($path, 'data:')) {
            return $path;
        }

        return '/' . implode('/', array_map('rawurlencode', explode('/', str_replace('\\', '/', ltrim($path, '/')))));
    }

    public static function storeUploadedImage(UploadedFile $file, string $folder): string
    {
        $folder = trim(str_replace('\\', '/', $folder), '/');
        if ($folder === '') {
            throw new RuntimeException('Folder upload tidak valid.');
        }

        $extension = strtolower((string) $file->getClientOriginalExtension());
        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
            throw new RuntimeException('Format foto harus JPG, PNG, WEBP, atau GIF.');
        }

        if ($file->getSize() > 8 * 1024 * 1024) {
            throw new RuntimeException('Ukuran foto maksimal 8 MB.');
        }

        // Store file contents as a data URI so files are kept in the database (longText)
        $mime = $file->getMimeType() ?: ('image/' . $extension);
        $contents = $file->get();
        if ($contents === null) {
            throw new RuntimeException('Gagal membaca isi file.');
        }
        $b64 = base64_encode($contents);
        return 'data:' . $mime . ';base64,' . $b64;
    }

    public static function deleteUploadedFile(?string $path): void
    {
        $path = trim((string) $path);
        if ($path === '') {
            return;
        }

        // If the path is a data URI stored in DB, nothing to delete from filesystem
        if (str_starts_with($path, 'data:')) {
            return;
        }

        // Otherwise, treat as legacy filesystem path and delete if exists
        if (! str_starts_with(str_replace('\\', '/', $path), 'uploads/')) {
            return;
        }

        $fullPath = public_path($path);
        if (File::exists($fullPath) && File::isFile($fullPath)) {
            File::delete($fullPath);
        }
    }

    public static function activeHomeSlides(): array
    {
        $slides = HomeSlide::query()
            ->where('aktif', true)
            ->orderBy('urutan_tampil')
            ->orderBy('id_banner_beranda')
            ->get(['judul', 'keterangan', 'lokasi_gambar'])
            ->map(static fn (HomeSlide $slide): array => self::bannerRow($slide->judul, $slide->keterangan, $slide->lokasi_gambar))
            ->all();

        return $slides !== [] ? $slides : self::fallbackHomeSlides();
    }

    public static function serviceRow(string $judul, string $deskripsi): array
    {
        return [
            'judul' => $judul,
            'deskripsi' => $deskripsi,
            'title' => $judul,
            'description' => $deskripsi,
        ];
    }

    public static function priceRow(string $namaLayanan, string $infoUkuran, string $teksHarga): array
    {
        return [
            'nama_layanan' => $namaLayanan,
            'info_ukuran' => $infoUkuran,
            'teks_harga' => $teksHarga,
            'service_name' => $namaLayanan,
            'size_info' => $infoUkuran,
            'price_text' => $teksHarga,
        ];
    }

    public static function galleryRow(string $judul, string $lokasiGambar): array
    {
        return [
            'judul' => $judul,
            'lokasi_gambar' => $lokasiGambar,
            'path_gambar' => $lokasiGambar,
            'title' => $judul,
            'image_path' => $lokasiGambar,
        ];
    }

    public static function bannerRow(string $judul, string $keterangan, string $lokasiGambar): array
    {
        return [
            'judul' => $judul,
            'keterangan' => $keterangan,
            'lokasi_gambar' => $lokasiGambar,
            'title' => $judul,
            'caption' => $keterangan,
            'path_gambar' => $lokasiGambar,
            'image_path' => $lokasiGambar,
        ];
    }

    public static function siteLogo(): string
    {
        // Prefer a GalleryItem explicitly named 'Logo'
        try {
            $logo = \App\Models\GalleryItem::query()->where('judul', 'LIKE', '%Logo%')->orWhere('judul', 'LIKE', '%logo%')->first();
            if ($logo !== null && filled($logo->lokasi_gambar)) {
                return (string) $logo->lokasi_gambar;
            }

            // Fallback to first home slide image
            $slide = \App\Models\HomeSlide::query()->whereNotNull('lokasi_gambar')->first();
            if ($slide !== null && filled($slide->lokasi_gambar)) {
                return (string) $slide->lokasi_gambar;
            }
        } catch (\Throwable $e) {
            // ignore DB errors and fallback to static path
        }

        return is_file(public_path('temp_uploads/logo.png'))
            ? 'temp_uploads/logo.png'
            : 'gambar/Logo.png';
    }
}
