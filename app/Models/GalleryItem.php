<?php

namespace App\Models;

use App\Models\Concerns\MemakaiAliasKolom;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GalleryItem extends Model
{
    use MemakaiAliasKolom;

    protected $table = 'tabel_galeri_layanan';
    protected $primaryKey = 'id_galeri_layanan';
    public const CREATED_AT = 'waktu_dibuat';
    public const UPDATED_AT = 'waktu_diperbarui';

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'id_layanan', 'id_layanan');
    }

    protected array $aliasKolom = [
        'id' => 'id_galeri_layanan',
        'title' => 'judul',
        'service_id' => 'id_layanan',
        'category_id' => 'id_layanan',
        'image_path' => 'lokasi_gambar',
        'path_gambar' => 'lokasi_gambar',
        'sort_order' => 'urutan_tampil',
        'is_active' => 'aktif',
        'created_at' => 'waktu_dibuat',
        'updated_at' => 'waktu_diperbarui',
    ];

    protected $fillable = [
        'title',
        'id_layanan',
        'image_path',
        'path_gambar',
        'sort_order',
        'is_active',
        'judul',
        'lokasi_gambar',
        'urutan_tampil',
        'aktif',
        'service_id',
        'category_id',
    ];

    protected $casts = [
        'aktif' => 'boolean',
        'urutan_tampil' => 'integer',
        'waktu_dibuat' => 'datetime',
        'waktu_diperbarui' => 'datetime',
    ];
}
