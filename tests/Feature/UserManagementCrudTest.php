<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_user_from_user_management_page(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->post(route('master-data.users.store'), [
            'name' => 'User Baru',
            'email' => 'userbaru@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'user',
            'status' => 'active',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('users', [
            'name' => 'User Baru',
            'email' => 'userbaru@example.com',
            'role' => 'user',
            'status' => 'active',
        ]);
    }

    public function test_admin_can_update_user_from_user_management_page(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $managedUser = User::factory()->create([
            'role' => 'user',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->patch(route('master-data.users.update', $managedUser), [
            'name' => 'Nama Diperbarui',
            'email' => 'updated@example.com',
            'password' => '',
            'password_confirmation' => '',
            'role' => 'admin',
            'status' => 'inactive',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $managedUser->id,
            'name' => 'Nama Diperbarui',
            'email' => 'updated@example.com',
            'role' => 'admin',
            'status' => 'inactive',
        ]);
    }

    public function test_admin_can_delete_other_user_from_user_management_page(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $managedUser = User::factory()->create([
            'role' => 'user',
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->delete(route('master-data.users.destroy', $managedUser));

        $response->assertRedirect(route('master-data.users.index'));
        $this->assertDatabaseMissing('users', ['id' => $managedUser->id]);
    }
}
