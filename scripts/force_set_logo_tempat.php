<?php
// Force set logo and tempat from files in public/temp_uploads
chdir(__DIR__ . '/..');
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\GalleryItem;
use App\Models\HomeSlide;

$dir = public_path('temp_uploads');
$logoNames = ['logo.png', 'Logo.png', 'logo.JPG', 'logo.jpeg'];
$tempNames = ['tempat.png', 'Tempat.png', 'tempat.jpeg', 'Tempat.jpeg', 'about.png', 'about.jpeg'];

function findFile(array $names, string $dir): ?string {
    foreach ($names as $n) {
        $p = $dir . DIRECTORY_SEPARATOR . $n;
        if (is_file($p)) return $p;
    }
    return null;
}

$logoFile = findFile($logoNames, $dir);
$tempFile = findFile($tempNames, $dir);

if ($logoFile === null && $tempFile === null) {
    echo "No files found in $dir. Expected logo and/or tempat files.\n";
    exit(0);
}

if ($logoFile !== null) {
    $mime = mime_content_type($logoFile) ?: 'image/png';
    $b64 = base64_encode(file_get_contents($logoFile));
    $dataUri = 'data:' . $mime . ';base64,' . $b64;

    $item = GalleryItem::query()->where('judul', 'LIKE', '%Logo%')->orWhere('judul', 'LIKE', '%logo%')->first();
    if ($item === null) {
        $item = new GalleryItem();
        $item->judul = 'Logo';
        $item->kode_kategori = '';
        $item->label_kategori = '';
        $item->urutan_tampil = 0;
        $item->aktif = true;
    }
    $item->lokasi_gambar = $dataUri;
    $item->save();
    echo "Logo set: id=" . $item->getKey() . "\n";
}

if ($tempFile !== null) {
    $mime = mime_content_type($tempFile) ?: 'image/jpeg';
    $b64 = base64_encode(file_get_contents($tempFile));
    $dataUri = 'data:' . $mime . ';base64,' . $b64;

    $slide = HomeSlide::query()->orderBy('id_banner_beranda')->first();
    if ($slide === null) {
        $slide = new HomeSlide();
        $slide->judul = 'Karunya Printing';
        $slide->keterangan = '';
        $slide->urutan_tampil = 0;
        $slide->aktif = true;
    }
    $slide->lokasi_gambar = $dataUri;
    $slide->save();
    echo "Tempat set as HomeSlide id=" . $slide->getKey() . "\n";
}

// Optionally delete files after processing
if ($logoFile !== null && is_file($logoFile)) { @unlink($logoFile); }
if ($tempFile !== null && is_file($tempFile)) { @unlink($tempFile); }

echo "Done.\n";
