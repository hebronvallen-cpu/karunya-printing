<?php

namespace App\Models;

use App\Models\Concerns\MemakaiAliasKolom;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceItem extends Model
{
    use MemakaiAliasKolom;

    protected $table = 'tabel_harga_layanan';
    protected $primaryKey = 'id_harga_layanan';
    public const CREATED_AT = 'waktu_dibuat';
    public const UPDATED_AT = 'waktu_diperbarui';

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'id_layanan', 'id_layanan');
    }

    protected array $aliasKolom = [
        'id' => 'id_harga_layanan',
        'service_id' => 'id_layanan',
        'size_info' => 'info_ukuran',
        'price_text' => 'teks_harga',
        'sort_order' => 'urutan_tampil',
        'is_active' => 'aktif',
        'created_at' => 'waktu_dibuat',
        'updated_at' => 'waktu_diperbarui',
    ];

    protected $fillable = [
        'id_layanan',
        'info_ukuran',
        'teks_harga',
        'urutan_tampil',
        'aktif',
        'service_id',
        'size_info',
        'price_text',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'aktif' => 'boolean',
        'urutan_tampil' => 'integer',
        'waktu_dibuat' => 'datetime',
        'waktu_diperbarui' => 'datetime',
    ];
}
