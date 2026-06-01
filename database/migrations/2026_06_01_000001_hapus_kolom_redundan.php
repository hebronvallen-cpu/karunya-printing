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
        // Jika tidak ada yang cocok, set null (akan handle manual atau defaultkan)
        \Illuminate\Support\Facades\DB::statement("
            UPDATE tabel_galeri_layanan g
            SET g.id_layanan = (
                SELECT l.id_layanan
                FROM tabel_layanan l
                WHERE LOWER(l.judul) = LOWER(g.label_kategori)
                LIMIT 1
            )
            WHERE g.label_kategori != ''
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
