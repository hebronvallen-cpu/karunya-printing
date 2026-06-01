<?php

namespace App\Models;

use App\Models\Concerns\MemakaiAliasKolom;
use Illuminate\Database\Eloquent\Model;

class HomeSlide extends Model
{
    use MemakaiAliasKolom;

    protected $table = 'tabel_banner_beranda';
    protected $primaryKey = 'id_banner_beranda';
    public const CREATED_AT = 'waktu_dibuat';
    public const UPDATED_AT = 'waktu_diperbarui';

    protected array $aliasKolom = [
        'id' => 'id_banner_beranda',
        'title' => 'judul',
        'caption' => 'keterangan',
        'image_path' => 'lokasi_gambar',
        'path_gambar' => 'lokasi_gambar',
        'sort_order' => 'urutan_tampil',
        'is_active' => 'aktif',
        'created_at' => 'waktu_dibuat',
        'updated_at' => 'waktu_diperbarui',
    ];

    protected $fillable = [
        'title',
        'caption',
        'image_path',
        'path_gambar',
        'sort_order',
        'is_active',
        'judul',
        'keterangan',
        'lokasi_gambar',
        'urutan_tampil',
        'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
        'urutan_tampil' => 'integer',
        'waktu_dibuat' => 'datetime',
        'waktu_diperbarui' => 'datetime',
    ];
}
