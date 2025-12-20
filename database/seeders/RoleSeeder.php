<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::updateOrCreate(['name' => 'owner'], [
            'description' => 'Pemilik usaha, akses penuh.',
        ]);

        Role::updateOrCreate(['name' => 'admin'], [
            'description' => 'Admin, mengelola master data & laporan.',
        ]);

        Role::updateOrCreate(['name' => 'kasir'], [
            'description' => 'Kasir, fokus ke transaksi & pelanggan.',
        ]);
    }
}
