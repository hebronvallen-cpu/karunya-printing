<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['judul' => 'Spanduk / Baliho', 'deskripsi' => 'Media promosi outdoor berukuran besar untuk toko, event, dan kampanye.'],
            ['judul' => 'X-Banner / Roll Banner', 'deskripsi' => 'Banner display portable untuk promosi indoor maupun pameran.'],
            ['judul' => 'Cutting Stiker', 'deskripsi' => 'Untuk branding etalase, kendaraan, label produk, dan dekorasi visual.'],
            ['judul' => 'Sablon Baju', 'deskripsi' => 'Sablon kaos komunitas, seragam, dan kebutuhan promosi usaha.'],
            ['judul' => 'ID Card / Name Tag', 'deskripsi' => 'Pembuatan identitas karyawan, panitia acara, dan name tag custom.'],
            ['judul' => 'Undangan', 'deskripsi' => 'Undangan acara resmi maupun keluarga dengan desain sesuai permintaan.'],
            ['judul' => 'Stempel', 'deskripsi' => 'Stempel usaha, kantor, dan custom dengan hasil cetak jelas.'],
            ['judul' => 'Pas Foto', 'deskripsi' => 'Layanan pas foto cepat untuk kebutuhan sekolah, kerja, dan dokumen.'],
            ['judul' => 'Scan Dokumen', 'deskripsi' => 'Digitalisasi dokumen dengan hasil rapi untuk arsip dan pengiriman online.'],
            ['judul' => 'Print, dll', 'deskripsi' => 'Layanan print harian untuk berbagai kebutuhan pribadi maupun bisnis.'],
            ['judul' => 'Cetak Foto', 'deskripsi' => 'Cetak foto dokumen dan foto umum dengan warna tajam.'],
            ['judul' => 'Batu Nisan / Prasasti', 'deskripsi' => 'Pengerjaan tulisan batu nisan dan batu prasasti sesuai permintaan.'],
            ['judul' => 'Cetak Foto di Keramik', 'deskripsi' => 'Cetak foto custom pada media keramik dengan kualitas warna tahan lama.'],
            ['judul' => 'Neon Box / Plang Nama', 'deskripsi' => 'Pembuatan identitas visual toko agar usaha lebih mudah dikenali.'],
            ['judul' => 'Brosur', 'deskripsi' => 'Cetak brosur promosi untuk usaha, acara, dan produk.'],
            ['judul' => 'Desain & Printing Media Promosi', 'deskripsi' => 'Konsep desain sampai produksi materi branding usaha.'],
            ['judul' => 'Desain & Cetak Outdoor / Indoor', 'deskripsi' => 'Layanan desain sekaligus cetak untuk media promosi luar dan dalam ruangan.'],
        ];

        foreach ($items as $index => $item) {
            Service::create(array_merge($item, ['urutan_tampil' => $index, 'aktif' => true]));
        }
    }
}
