<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tabel_harga_layanan', function (Blueprint $table): void {
            // Tambahkan kolom id_layanan jika belum ada
            if (!Schema::hasColumn('tabel_harga_layanan', 'id_layanan')) {
                $table->unsignedBigInteger('id_layanan')->nullable()->after('id_harga_layanan');
            }
        });

        // Update data: cocokkan berdasarkan nama_layanan
        DB::statement("
            UPDATE tabel_harga_layanan
            SET id_layanan = (
                SELECT id_layanan
                FROM tabel_layanan
                WHERE LOWER(judul) = LOWER(tabel_harga_layanan.nama_layanan)
                LIMIT 1
            )
            WHERE id_layanan IS NULL
        ");

        // Tambahkan foreign key constraint
        Schema::table('tabel_harga_layanan', function (Blueprint $table): void {
            $table->foreign('id_layanan')
                ->references('id_layanan')
                ->on('tabel_layanan')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('tabel_harga_layanan', function (Blueprint $table): void {
            // Hapus foreign key terlebih dahulu
            $table->dropForeign(['id_layanan']);
            // Hapus kolom
            $table->dropColumn('id_layanan');
        });
    }
};
