<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $ownerRole = Role::where('name', 'owner')->first();
        $adminRole = Role::where('name', 'admin')->first();
        $kasirRole = Role::where('name', 'kasir')->first();

        User::updateOrCreate(
            ['email' => 'owner@pos.test'],
            [
                'name'     => 'Owner POS',
                'password' => Hash::make('password'),
                'role_id'  => $ownerRole->id,
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin@pos.test'],
            [
                'name'     => 'Admin POS',
                'password' => Hash::make('password'),
                'role_id'  => $adminRole->id,
            ]
        );

        User::updateOrCreate(
            ['email' => 'kasir@pos.test'],
            [
                'name'     => 'Kasir POS',
                'password' => Hash::make('password'),
                'role_id'  => $kasirRole->id,
            ]
        );
    }
}
