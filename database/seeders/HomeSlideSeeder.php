<?php

namespace Database\Seeders;

use App\Models\HomeSlide;
use Illuminate\Database\Seeder;

class HomeSlideSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'judul' => 'Karunya Printing',
                'keterangan' => 'Layanan percetakan cepat dan rapi untuk promosi usaha serta kebutuhan acara.',
                'lokasi_gambar' => 'gambar/tempat.jpeg',
                'urutan_tampil' => 0,
                'aktif' => true,
            ],
            [
                'judul' => 'Spanduk dan Baliho',
                'keterangan' => 'Produksi media promosi outdoor dengan warna tajam dan ukuran sesuai kebutuhan.',
                'lokasi_gambar' => 'gambar/tempat.jpeg',
                'urutan_tampil' => 1,
                'aktif' => true,
            ],
        ];

        foreach ($items as $item) {
            // Convert referenced image file into data URI if present
            $root = dirname(__DIR__, 2);
            $possible = $root . '/' . ltrim($item['lokasi_gambar'], '/');
            if (is_file($possible)) {
                $mime = mime_content_type($possible) ?: 'application/octet-stream';
                $data = base64_encode(file_get_contents($possible));
                $item['lokasi_gambar'] = 'data:' . $mime . ';base64,' . $data;
            }
            HomeSlide::create($item);
        }
    }
}

