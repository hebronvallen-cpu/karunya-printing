<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AdminSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        Admin::firstOrCreate(
            ['nama_pengguna' => 'admin'],
            [
                'nama_lengkap' => 'Administrator Karunya Printing',
                'email' => 'admin@karunyaprinting.local',
                'kata_sandi' => password_hash('admin123', PASSWORD_DEFAULT),
                'aktif' => true,
            ]
        );
    }
}
