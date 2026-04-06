<?php

namespace Database\Seeders;

use App\Models\MenuList;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleMenuAccessSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $roleAccessMap = [
            'admin' => ['dashboard', 'profile', 'master-data', 'users', 'role-akses'],
            'user' => ['dashboard', 'profile'],
        ];

        foreach ($roleAccessMap as $role => $keys) {
            $menuIds = MenuList::query()
                ->whereIn('key', $keys)
                ->pluck('id')
                ->all();

            DB::table('role_menu_access')->where('role', $role)->delete();

            foreach ($menuIds as $menuId) {
                DB::table('role_menu_access')->insert([
                    'role' => $role,
                    'menu_list_id' => $menuId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
