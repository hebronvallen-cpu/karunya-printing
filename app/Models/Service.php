<?php

namespace App\Models;

use App\Models\Concerns\MemakaiAliasKolom;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use MemakaiAliasKolom;

    protected $table = 'tabel_layanan';
    protected $primaryKey = 'id_layanan';
    public const CREATED_AT = 'waktu_dibuat';
    public const UPDATED_AT = 'waktu_diperbarui';

    public function priceItems(): HasMany
    {
        return $this->hasMany(PriceItem::class, 'id_layanan', 'id_layanan');
    }

    public function galleryItems(): HasMany
    {
        return $this->hasMany(GalleryItem::class, 'id_layanan', 'id_layanan');
    }

    protected array $aliasKolom = [
        'id' => 'id_layanan',
        'title' => 'judul',
        'description' => 'deskripsi',
        'sort_order' => 'urutan_tampil',
        'is_active' => 'aktif',
        'created_at' => 'waktu_dibuat',
        'updated_at' => 'waktu_diperbarui',
    ];

    protected $fillable = [
        'title',
        'description',
        'sort_order',
        'is_active',
        'judul',
        'deskripsi',
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

