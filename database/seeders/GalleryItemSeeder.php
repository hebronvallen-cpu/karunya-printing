<?php

namespace Database\Seeders;

use App\Models\GalleryItem;
use Illuminate\Database\Seeder;

class GalleryItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['judul' => 'Spanduk / Baliho', 'lokasi_gambar' => 'uploads/gallery/Brosur.jpg', 'urutan_tampil' => 0, 'aktif' => true],
            ['judul' => 'X-Banner / Roll Banner', 'lokasi_gambar' => 'uploads/gallery/Cutting Stiker.jpg', 'urutan_tampil' => 1, 'aktif' => true],
            ['judul' => 'Cutting Stiker', 'lokasi_gambar' => 'uploads/gallery/Sablon Baju.jpg', 'urutan_tampil' => 2, 'aktif' => true],
            ['judul' => 'Sablon Baju', 'lokasi_gambar' => 'uploads/gallery/Stempel.jpg', 'urutan_tampil' => 3, 'aktif' => true],
            ['judul' => 'Undangan', 'lokasi_gambar' => 'uploads/gallery/Undangan.jpg', 'urutan_tampil' => 4, 'aktif' => true],
            ['judul' => 'Stempel', 'lokasi_gambar' => 'uploads/gallery/Cetak Foto.jpg', 'urutan_tampil' => 5, 'aktif' => true],
            ['judul' => 'Cetak Foto', 'lokasi_gambar' => 'uploads/gallery/Scan Dokumen.jpg', 'urutan_tampil' => 6, 'aktif' => true],
            ['judul' => 'Brosur', 'lokasi_gambar' => 'uploads/gallery/Pas Foto.jpg', 'urutan_tampil' => 7, 'aktif' => true],
        ];

        foreach ($items as $item) {
            // If referenced file exists in project, convert to data URI before inserting
            $root = dirname(__DIR__, 2);
            $possible = $root . '/' . ltrim($item['lokasi_gambar'], '/');
            if (is_file($possible)) {
                $mime = mime_content_type($possible) ?: 'application/octet-stream';
                $data = base64_encode(file_get_contents($possible));
                $item['lokasi_gambar'] = 'data:' . $mime . ';base64,' . $data;
            }
            GalleryItem::create($item);
        }
    }
}

