<?php

namespace App\Http\Controllers;

use App\Models\MenuList;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RoleAccessController extends Controller
{
    private const ROLES = ['admin', 'user'];

    public function index(Request $request): View
    {
        $request->user()->can('role-akses') || abort(403);

        $roles = collect(self::ROLES)->map(function (string $role) {
            return [
                'name' => ucfirst($role),
                'key' => $role,
            ];
        });

        return view('master-data.role-access.index', [
            'roles' => $roles,
        ]);
    }

    public function show(Request $request, string $role): View
    {
        $request->user()->can('role-akses') || abort(403);

        abort_unless(in_array($role, self::ROLES, true), 404);

        $menus = MenuList::query()
            ->orderBy('sort_order')
            ->get()
            ->map(function (MenuList $menu) use ($role) {
                $menu->enabled = DB::table('role_menu_access')
                    ->where('role', $role)
                    ->where('menu_list_id', $menu->id)
                    ->exists();

                return $menu;
            });

        return view('master-data.role-access.show', [
            'selectedRole' => $role,
            'menus' => $menus,
        ]);
    }

    public function update(Request $request, string $role): RedirectResponse
    {
        $request->user()->can('role-akses') || abort(403);

        abort_unless(in_array($role, self::ROLES, true), 404);

        $validated = $request->validate([
            'menu_ids' => ['nullable', 'array'],
            'menu_ids.*' => ['integer', 'exists:menu_list,id'],
        ]);

        $menuIds = collect($validated['menu_ids'] ?? [])->map(fn($id) => (int) $id)->unique()->values();

        DB::transaction(function () use ($role, $menuIds) {
            DB::table('role_menu_access')->where('role', $role)->delete();

            foreach ($menuIds as $menuId) {
                DB::table('role_menu_access')->insert([
                    'role' => $role,
                    'menu_list_id' => $menuId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return redirect()
            ->route('master-data.role-access.show', $role)
            ->with('success', 'Role akses berhasil diperbarui.');
    }
}
