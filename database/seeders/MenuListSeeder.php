<?php

namespace Database\Seeders;

use App\Models\MenuList;
use Illuminate\Database\Seeder;

class MenuListSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $menus = [
            ['name' => 'Dashboard', 'key' => 'dashboard', 'sort_order' => 1],
            ['name' => 'Profile', 'key' => 'profile', 'sort_order' => 2],
            ['name' => 'Master Data', 'key' => 'master-data', 'sort_order' => 3],
            ['name' => 'User', 'key' => 'users', 'sort_order' => 4],
            ['name' => 'Role Akses', 'key' => 'role-akses', 'sort_order' => 5],
            ['name' => 'Data Pemasukan', 'key' => 'data-pemasukan', 'sort_order' => 6],
            ['name' => 'Pengajuan Dana', 'key' => 'pengajuan-dana', 'sort_order' => 7],
            ['name' => 'Approval Pengajuan', 'key' => 'approval-pengajuan', 'sort_order' => 8],
            ['name' => 'Data Pengeluaran', 'key' => 'data-pengeluaran', 'sort_order' => 9],
            ['name' => 'Approval Pengeluaran', 'key' => 'approval-pengeluaran', 'sort_order' => 10],
        ];

        foreach ($menus as $menu) {
            MenuList::query()->updateOrCreate(
                ['key' => $menu['key']],
                $menu,
            );
        }
    }
}
