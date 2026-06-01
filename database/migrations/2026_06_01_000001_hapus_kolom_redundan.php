<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop kolom redundan dari tabel_harga_layanan
        Schema::table('tabel_harga_layanan', function (Blueprint $table): void {
            if (Schema::hasColumn('tabel_harga_layanan', 'nama_layanan')) {
                $table->dropColumn('nama_layanan');
            }
        });

        // Normalisasi tabel_galeri_layanan: tambah id_layanan, drop kode/label kategori
        Schema::table('tabel_galeri_layanan', function (Blueprint $table): void {
            if (!Schema::hasColumn('tabel_galeri_layanan', 'id_layanan')) {
                $table->unsignedBigInteger('id_layanan')->nullable()->after('id_galeri_layanan');
                $table->foreign('id_layanan')
                    ->references('id_layanan')
                    ->on('tabel_layanan')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            }
        });

        // Migrate data: cocokkan kategori dengan layanan
        \Illuminate\Support\Facades\DB::statement("
            UPDATE tabel_galeri_layanan
            SET id_layanan = (
                SELECT id_layanan
                FROM tabel_layanan
                WHERE LOWER(judul) = LOWER(tabel_galeri_layanan.label_kategori)
                LIMIT 1
            )
            WHERE label_kategori != ''
        ");

        // Drop kolom redundan kategori
        Schema::table('tabel_galeri_layanan', function (Blueprint $table): void {
            $table->dropColumn('kode_kategori');
            $table->dropColumn('label_kategori');
        });
    }

    public function down(): void
    {
        // Restore kolom kategori di galeri
        Schema::table('tabel_galeri_layanan', function (Blueprint $table): void {
            $table->string('kode_kategori', 60)->default('')->after('id_galeri_layanan');
            $table->string('label_kategori', 120)->default('')->after('kode_kategori');
        });

        // Drop foreign key dan id_layanan dari galeri
        Schema::table('tabel_galeri_layanan', function (Blueprint $table): void {
            $table->dropForeign(['id_layanan']);
            $table->dropColumn('id_layanan');
        });

        // Restore kolom nama_layanan ke harga
        Schema::table('tabel_harga_layanan', function (Blueprint $table): void {
            $table->string('nama_layanan', 140)->after('id_harga_layanan');
        });
    }
};
