<?php

namespace Database\Seeders;

use App\Models\PriceItem;
use Illuminate\Database\Seeder;

class PriceItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['nama_layanan' => 'Spanduk Flexi 280gr', 'info_ukuran' => 'per m2', 'teks_harga' => 'Rp 35.000', 'urutan_tampil' => 0, 'aktif' => true],
            ['nama_layanan' => 'Baliho Outdoor', 'info_ukuran' => 'per m2', 'teks_harga' => 'Rp 55.000', 'urutan_tampil' => 1, 'aktif' => true],
            ['nama_layanan' => 'X-Banner', 'info_ukuran' => '60 x 160 cm + stand', 'teks_harga' => 'Rp 85.000', 'urutan_tampil' => 2, 'aktif' => true],
            ['nama_layanan' => 'Cutting Stiker', 'info_ukuran' => 'per m2', 'teks_harga' => 'Rp 120.000', 'urutan_tampil' => 3, 'aktif' => true],
            ['nama_layanan' => 'Undangan', 'info_ukuran' => '100 lembar', 'teks_harga' => 'Rp 250.000', 'urutan_tampil' => 4, 'aktif' => true],
            ['nama_layanan' => 'ID Card PVC', 'info_ukuran' => 'per pcs', 'teks_harga' => 'Rp 15.000', 'urutan_tampil' => 5, 'aktif' => true],
            ['nama_layanan' => 'Print Warna', 'info_ukuran' => 'A4 / lembar', 'teks_harga' => 'Rp 2.500', 'urutan_tampil' => 6, 'aktif' => true],
            ['nama_layanan' => 'Neon Box', 'info_ukuran' => '100 x 60 cm', 'teks_harga' => 'Mulai Rp 650.000', 'urutan_tampil' => 7, 'aktif' => true],
        ];

        foreach ($items as $item) {
            PriceItem::create($item);
        }
    }
}

