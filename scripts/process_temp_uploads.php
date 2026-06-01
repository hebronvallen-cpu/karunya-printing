<?php
// CLI script: process files in public/temp_uploads, store logo or place image into DB and delete files
// Usage: php scripts/process_temp_uploads.php

chdir(__DIR__ . '/..');
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\GalleryItem;
use App\Models\HomeSlide;

$dir = public_path('temp_uploads');
if (! is_dir($dir)) {
    if (! mkdir($dir, 0775, true) && ! is_dir($dir)) {
        echo "Failed to create directory: $dir\n";
        exit(1);
    }
}

$files = array_values(array_filter(array_map(function($f) use ($dir){ return $dir . DIRECTORY_SEPARATOR . $f; }, scandir($dir)), 'is_file'));
if (count($files) === 0) {
    echo "No files to process in $dir\n";
    exit(0);
}

foreach ($files as $file) {
    $base = basename($file);
    $lower = mb_strtolower($base);
    $mime = mime_content_type($file) ?: 'application/octet-stream';
    $data = base64_encode(file_get_contents($file));
    $dataUri = 'data:' . $mime . ';base64,' . $data;

    try {
        if (str_contains($lower, 'logo')) {
            // find existing Logo item or create
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
            echo "Processed Logo: {$base} -> GalleryItem id={$item->getKey()}\n";
        } elseif (str_contains($lower, 'tempat') || str_contains($lower, 'home') || str_contains($lower, 'place')) {
            // update or create first HomeSlide
            $slide = HomeSlide::query()->orderBy('id_banner_beranda')->first();
            if ($slide === null) {
                $slide = new HomeSlide();
                $slide->judul = 'Slide - Tempat';
                $slide->keterangan = '';
                $slide->urutan_tampil = 0;
                $slide->aktif = true;
            }
            $slide->lokasi_gambar = $dataUri;
            $slide->save();
            echo "Processed Place image: {$base} -> HomeSlide id={$slide->getKey()}\n";
        } else {
            echo "Skipped (not logo/tempat): {$base}\n";
        }
    } catch (Throwable $e) {
        echo "Error processing {$base}: " . $e->getMessage() . "\n";
    }

    // remove file after processing (or skipped)
    if (is_file($file)) {
        @unlink($file);
    }
}

echo "Done.\n";
