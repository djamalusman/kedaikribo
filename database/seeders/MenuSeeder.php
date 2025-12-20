<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\NavigationMenu;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $owner = Role::where('name', 'owner')->first();
        $admin = Role::where('name', 'admin')->first();
        $kasir = Role::where('name', 'kasir')->first();

        // Helper untuk tambah menu + role
        $addMenu = function ($data, $roles = []) {
            $menu = NavigationMenu::updateOrCreate(
                ['name' => $data['name'], 'parent_id' => $data['parent_id'] ?? null],
                [
                    'route_name' => $data['route_name'] ?? null,
                    'url'        => $data['url'] ?? null,
                    'icon'       => $data['icon'] ?? null,
                    'sort_order' => $data['sort_order'] ?? 0,
                    'is_active'  => true,
                ]
            );

            if (! empty($roles)) {
                $menu->roles()->syncWithoutDetaching($roles);
            }

            return $menu;
        };

        // ==== OWNER MENU ====
        $ownerDashboard = $addMenu(
            [
                'name'       => 'Dashboard Owner',
                'route_name' => 'owner.dashboard',
                'parent_id'  => null,
                'icon'       => 'bi bi-speedometer2',
                'sort_order' => 1,
            ],
            [$owner->id]
        );

        $ownerMaster = $addMenu(
            [
                'name'       => 'Master Data',
                'parent_id'  => null,
                'icon'       => 'bi bi-collection',
                'sort_order' => 2,
            ],
            [$owner->id]
        );

        $addMenu([
            'name'       => 'Outlet',
            'route_name' => 'owner.outlets.index',
            'parent_id'  => $ownerMaster->id,
            'icon'       => 'bi bi-shop',
            'sort_order' => 1,
        ], [$owner->id]);

        $addMenu([
            'name'       => 'Users',
            'route_name' => 'owner.users.index',
            'parent_id'  => $ownerMaster->id,
            'icon'       => 'bi bi-people',
            'sort_order' => 2,
        ], [$owner->id]);

        $addMenu([
            'name'       => 'Menu',
            'route_name' => 'owner.menu.index',
            'parent_id'  => $ownerMaster->id,
            'icon'       => 'bi bi-cup-hot',
            'sort_order' => 3,
        ], [$owner->id]);

        $addMenu([
            'name'       => 'Bahan Baku',
            'route_name' => 'owner.ingredients.index',
            'parent_id'  => $ownerMaster->id,
            'icon'       => 'bi bi-box',
            'sort_order' => 4,
        ], [$owner->id]);

        // ==== ADMIN MENU ====
        $adminDashboard = $addMenu(
            [
                'name'       => 'Dashboard Admin',
                'route_name' => 'admin.dashboard',
                'parent_id'  => null,
                'icon'       => 'bi bi-speedometer',
                'sort_order' => 1,
            ],
            [$admin->id]
        );

        $adminMaster = $addMenu(
            [
                'name'       => 'Master Data',
                'parent_id'  => null,
                'icon'       => 'bi bi-collection',
                'sort_order' => 2,
            ],
            [$admin->id]
        );

        $addMenu([
            'name'       => 'Menu',
            'route_name' => 'admin.menu.index',
            'parent_id'  => $adminMaster->id,
            'icon'       => 'bi bi-cup-hot',
            'sort_order' => 1,
        ], [$admin->id]);

        $addMenu([
            'name'       => 'Bahan Baku',
            'route_name' => 'admin.ingredients.index',
            'parent_id'  => $adminMaster->id,
            'icon'       => 'bi bi-box',
            'sort_order' => 2,
        ], [$admin->id]);

        $addMenu([
            'name'       => 'Promo',
            'route_name' => 'admin.promotions.index',
            'parent_id'  => $adminMaster->id,
            'icon'       => 'bi bi-tag',
            'sort_order' => 3,
        ], [$admin->id]);

        // ==== KASIR MENU ====
        $kasirDashboard = $addMenu(
            [
                'name'       => 'Dashboard Kasir',
                'route_name' => 'kasir.dashboard',
                'parent_id'  => null,
                'icon'       => 'bi bi-speedometer',
                'sort_order' => 1,
            ],
            [$kasir->id]
        );

        $kasirTransaksi = $addMenu(
            [
                'name'       => 'Transaksi',
                'parent_id'  => null,
                'icon'       => 'bi bi-bag',
                'sort_order' => 2,
            ],
            [$kasir->id]
        );

        $addMenu([
            'name'       => 'Order',
            'route_name' => 'kasir.orders.index',
            'parent_id'  => $kasirTransaksi->id,
            'icon'       => 'bi bi-cart',
            'sort_order' => 1,
        ], [$kasir->id]);

        // ==== Hierarki Menu Minuman & Makanan untuk Kasir ====
        $minuman = $addMenu(
            [
                'name'       => 'Minuman',
                'parent_id'  => null,
                'icon'       => 'bi bi-cup-straw',
                'sort_order' => 3,
            ],
            [$kasir->id]
        );

        $kopi = $addMenu(
            [
                'name'       => 'Kopi',
                'parent_id'  => $minuman->id,
                'sort_order' => 1,
            ],
            [$kasir->id]
        );

        $addMenu([
            'name'       => 'Kopi Susu',
            'route_name' => 'kasir.menu.kopi.susu',
            'parent_id'  => $kopi->id,
            'sort_order' => 1,
        ], [$kasir->id]);

        $addMenu([
            'name'       => 'Kopi Hitam',
            'route_name' => 'kasir.menu.kopi.hitam',
            'parent_id'  => $kopi->id,
            'sort_order' => 2,
        ], [$kasir->id]);

        $teh = $addMenu(
            [
                'name'       => 'Teh',
                'parent_id'  => $minuman->id,
                'sort_order' => 2,
            ],
            [$kasir->id]
        );

        $addMenu([
            'name'       => 'Teh Manis',
            'route_name' => 'kasir.menu.teh.manis',
            'parent_id'  => $teh->id,
            'sort_order' => 1,
        ], [$kasir->id]);

        $addMenu([
            'name'       => 'Teh Tawar',
            'route_name' => 'kasir.menu.teh.tawar',
            'parent_id'  => $teh->id,
            'sort_order' => 2,
        ], [$kasir->id]);

        $makanan = $addMenu(
            [
                'name'       => 'Makanan',
                'parent_id'  => null,
                'icon'       => 'bi bi-egg-fried',
                'sort_order' => 4,
            ],
            [$kasir->id]
        );

        $addMenu([
            'name'       => 'Nasi Ayam',
            'route_name' => 'kasir.menu.makanan.nasi-ayam',
            'parent_id'  => $makanan->id,
            'sort_order' => 1,
        ], [$kasir->id]);

        $addMenu([
            'name'       => 'Kentang',
            'route_name' => 'kasir.menu.makanan.kentang',
            'parent_id'  => $makanan->id,
            'sort_order' => 2,
        ], [$kasir->id]);

        $addMenu([
            'name'       => 'Donat',
            'route_name' => 'kasir.menu.makanan.donat',
            'parent_id'  => $makanan->id,
            'sort_order' => 3,
        ], [$kasir->id]);
    }
}
