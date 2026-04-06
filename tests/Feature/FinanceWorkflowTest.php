<?php

namespace Tests\Feature;

use App\Providers\AppServiceProvider;
use App\Models\UangKeluar;
use App\Models\UangMasuk;
use App\Models\User;
use Database\Seeders\MenuListSeeder;
use Database\Seeders\RoleMenuAccessSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FinanceWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(MenuListSeeder::class);
        $this->seed(RoleMenuAccessSeeder::class);

        (new AppServiceProvider($this->app))->boot();
    }

    public function test_user_can_submit_pengajuan_dana_with_pending_status(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->post(route('keuangan.pengajuan-dana.store'), [
            'jumlah' => 150000,
            'deskripsi' => 'Pengajuan dana belanja bulanan',
            'tanggal' => '2026-04-06',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('uang_masuk', [
            'created_by' => $user->id,
            'source' => 'pengajuan',
            'status' => 'pending',
            'deskripsi' => 'Pengajuan dana belanja bulanan',
        ]);
    }

    public function test_user_can_submit_pengeluaran_with_pending_status_and_bukti(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'role' => 'user',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->post(route('keuangan.pengeluaran.store'), [
            'jumlah' => 250000,
            'deskripsi' => 'Pembelian kebutuhan rumah',
            'tanggal' => '2026-04-06',
            'bukti' => UploadedFile::fake()->image('bukti.png'),
        ]);

        $response->assertRedirect();

        $expense = UangKeluar::query()->firstOrFail();

        $this->assertSame('pending', $expense->status);
        Storage::disk('public')->assertExists($expense->bukti_path);
    }

    public function test_admin_can_approve_pending_pengajuan_dana(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $requester = User::factory()->create([
            'role' => 'user',
            'status' => 'active',
        ]);

        $income = UangMasuk::query()->create([
            'created_by' => $requester->id,
            'source' => 'pengajuan',
            'jumlah' => 500000,
            'deskripsi' => 'Pengajuan dana sekolah',
            'tanggal' => '2026-04-06',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->patch(route('keuangan.approval-pengajuan.update', $income), [
            'status' => 'approved',
            'approval_note' => 'Disetujui.',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('uang_masuk', [
            'id' => $income->id,
            'status' => 'approved',
            'approval_note' => 'Disetujui.',
            'approved_by' => $admin->id,
        ]);
    }
}
