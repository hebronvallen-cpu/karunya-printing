<?php

namespace App\Models;

use App\Models\Concerns\MemakaiAliasKolom;
use Illuminate\Database\Eloquent\Model;

class SiteActivityLog extends Model
{
    use MemakaiAliasKolom;

    protected $table = 'tabel_log_aktivitas_situs';
    public $timestamps = false;
    protected $primaryKey = 'id_log_aktivitas_situs';

    protected array $aliasKolom = [
        'id' => 'id_log_aktivitas_situs',
        'kunci_event' => 'kunci_peristiwa',
        'event_key' => 'kunci_peristiwa',
        'page_key' => 'kunci_halaman',
        'details' => 'detail',
        'ip_address' => 'alamat_ip',
        'user_agent' => 'agen_pengguna',
        'created_at' => 'waktu_dibuat',
    ];

    protected $fillable = [
        'event_key',
        'page_key',
        'details',
        'ip_address',
        'user_agent',
        'created_at',
        'kunci_event',
        'kunci_peristiwa',
        'kunci_halaman',
        'label',
        'detail',
        'alamat_ip',
        'agen_pengguna',
        'waktu_dibuat',
    ];

    protected $casts = [
        'waktu_dibuat' => 'datetime',
    ];
}
