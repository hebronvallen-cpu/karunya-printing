<?php

namespace App\Models;

use App\Models\Concerns\MemakaiAliasKolom;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use MemakaiAliasKolom;

    protected $table = 'tabel_admin';
    protected $primaryKey = 'id_admin';
    public const CREATED_AT = 'waktu_dibuat';
    public const UPDATED_AT = 'waktu_diperbarui';

    protected array $aliasKolom = [
        'id' => 'id_admin',
        'username' => 'nama_pengguna',
        'password' => 'kata_sandi',
        'full_name' => 'nama_lengkap',
        'phone' => 'nomor_telepon',
        'is_active' => 'aktif',
        'email_pending' => 'email_menunggu_verifikasi',
        'pending_email' => 'email_menunggu_verifikasi',
        'otp_code' => 'kode_otp',
        'otp_expires_at' => 'waktu_otp_kedaluwarsa',
        'reset_token' => 'token_reset',
        'reset_expires_at' => 'waktu_token_reset_kedaluwarsa',
        'created_at' => 'waktu_dibuat',
        'updated_at' => 'waktu_diperbarui',
    ];

    protected $fillable = [
        'username',
        'password',
        'full_name',
        'phone',
        'is_active',
        'email_pending',
        'pending_email',
        'otp_code',
        'otp_expires_at',
        'reset_token',
        'reset_expires_at',
        'nama_pengguna',
        'kata_sandi',
        'nama_lengkap',
        'email',
        'nomor_telepon',
        'aktif',
        'email_menunggu_verifikasi',
        'kode_otp',
        'waktu_otp_kedaluwarsa',
        'token_reset',
        'waktu_token_reset_kedaluwarsa',
    ];

    protected $casts = [
        'aktif' => 'boolean',
        'waktu_otp_kedaluwarsa' => 'datetime',
        'waktu_token_reset_kedaluwarsa' => 'datetime',
        'waktu_dibuat' => 'datetime',
        'waktu_diperbarui' => 'datetime',
    ];
}
