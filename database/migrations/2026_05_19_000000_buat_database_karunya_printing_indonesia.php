<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tabel_admin', function (Blueprint $table): void {
            $table->bigIncrements('id_admin');
            $table->string('nama_pengguna', 60)->unique('unik_admin_nama_pengguna');
            $table->string('kata_sandi', 255);
            $table->string('nama_lengkap', 120);
            $table->string('email', 160)->unique('unik_admin_email');
            $table->string('nomor_telepon', 30)->nullable();
            $table->boolean('aktif')->default(true);
            $table->string('email_menunggu_verifikasi', 160)->nullable();
            $table->string('kode_otp', 12)->nullable();
            $table->timestamp('waktu_otp_kedaluwarsa')->nullable();
            $table->string('token_reset', 128)->nullable();
            $table->timestamp('waktu_token_reset_kedaluwarsa')->nullable();
            $table->timestamp('waktu_dibuat')->useCurrent();
            $table->timestamp('waktu_diperbarui')->useCurrent()->useCurrentOnUpdate();

            $table->index('aktif', 'idx_admin_aktif');
        });

        Schema::create('tabel_layanan', function (Blueprint $table): void {
            $table->bigIncrements('id_layanan');
            $table->string('judul', 160);
            $table->text('deskripsi');
            $table->integer('urutan_tampil')->default(0);
            $table->boolean('aktif')->default(true);
            $table->timestamp('waktu_dibuat')->useCurrent();
            $table->timestamp('waktu_diperbarui')->useCurrent()->useCurrentOnUpdate();

            $table->index(['aktif', 'urutan_tampil'], 'idx_layanan_aktif_urutan');
        });

        Schema::create('tabel_harga_layanan', function (Blueprint $table): void {
            $table->bigIncrements('id_harga_layanan');
            $table->string('nama_layanan', 140);
            $table->string('info_ukuran', 180);
            $table->string('teks_harga', 100);
            $table->integer('urutan_tampil')->default(0);
            $table->boolean('aktif')->default(true);
            $table->timestamp('waktu_dibuat')->useCurrent();
            $table->timestamp('waktu_diperbarui')->useCurrent()->useCurrentOnUpdate();

            $table->index(['aktif', 'urutan_tampil'], 'idx_harga_aktif_urutan');
        });

        Schema::create('tabel_galeri_layanan', function (Blueprint $table): void {
            $table->bigIncrements('id_galeri_layanan');
            $table->string('judul', 140);
            $table->string('kode_kategori', 60)->default('');
            $table->string('label_kategori', 120)->default('');
            $table->longText('lokasi_gambar');
            $table->integer('urutan_tampil')->default(0);
            $table->boolean('aktif')->default(true);
            $table->timestamp('waktu_dibuat')->useCurrent();
            $table->timestamp('waktu_diperbarui')->useCurrent()->useCurrentOnUpdate();

            $table->index(['aktif', 'urutan_tampil'], 'idx_galeri_aktif_urutan');
        });

        Schema::create('tabel_banner_beranda', function (Blueprint $table): void {
            $table->bigIncrements('id_banner_beranda');
            $table->string('judul', 140)->default('');
            $table->string('keterangan', 255)->default('');
            $table->longText('lokasi_gambar');
            $table->integer('urutan_tampil')->default(0);
            $table->boolean('aktif')->default(true);
            $table->timestamp('waktu_dibuat')->useCurrent();
            $table->timestamp('waktu_diperbarui')->useCurrent()->useCurrentOnUpdate();

            $table->index(['aktif', 'urutan_tampil'], 'idx_banner_aktif_urutan');
        });

        Schema::create('tabel_log_aktivitas_situs', function (Blueprint $table): void {
            $table->bigIncrements('id_log_aktivitas_situs');
            $table->string('kunci_peristiwa', 60);
            $table->string('kunci_halaman', 60)->default('');
            $table->string('label', 160)->default('');
            $table->string('detail', 255)->default('');
            $table->string('alamat_ip', 45)->default('');
            $table->string('agen_pengguna', 255)->default('');
            $table->timestamp('waktu_dibuat')->useCurrent();

            $table->index(['kunci_peristiwa', 'waktu_dibuat'], 'idx_log_peristiwa_waktu');
            $table->index(['kunci_halaman', 'waktu_dibuat'], 'idx_log_halaman_waktu');
            $table->index('waktu_dibuat', 'idx_log_waktu');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tabel_log_aktivitas_situs');
        Schema::dropIfExists('tabel_banner_beranda');
        Schema::dropIfExists('tabel_galeri_layanan');
        Schema::dropIfExists('tabel_harga_layanan');
        Schema::dropIfExists('tabel_layanan');
        Schema::dropIfExists('tabel_admin');
    }
};
