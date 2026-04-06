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
        $this->assertTrue(Storage::disk('public')->exists($expense->bukti_path));
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

    public function test_user_can_edit_and_delete_pending_pengajuan_dana(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'status' => 'active',
        ]);

        $income = UangMasuk::query()->create([
            'created_by' => $user->id,
            'source' => 'pengajuan',
            'jumlah' => 120000,
            'deskripsi' => 'Pengajuan awal',
            'tanggal' => '2026-04-06',
            'status' => 'pending',
        ]);

        $updateResponse = $this->actingAs($user)->patch(route('keuangan.pengajuan-dana.update', $income), [
            'jumlah' => 175000,
            'deskripsi' => 'Pengajuan revisi',
            'tanggal' => '2026-04-07',
        ]);

        $updateResponse->assertRedirect();

        $this->assertDatabaseHas('uang_masuk', [
            'id' => $income->id,
            'jumlah' => 175000,
            'deskripsi' => 'Pengajuan revisi',
            'status' => 'pending',
        ]);

        $deleteResponse = $this->actingAs($user)->delete(route('keuangan.pengajuan-dana.destroy', $income));

        $deleteResponse->assertRedirect(route('keuangan.pengajuan-dana.index'));
        $this->assertDatabaseMissing('uang_masuk', ['id' => $income->id]);
    }

    public function test_user_can_edit_and_delete_pending_pengeluaran(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'role' => 'user',
            'status' => 'active',
        ]);

        $expense = UangKeluar::query()->create([
            'created_by' => $user->id,
            'jumlah' => 200000,
            'deskripsi' => 'Belanja awal',
            'tanggal' => '2026-04-06',
            'bukti_path' => UploadedFile::fake()->image('lama.png')->store('bukti-pengeluaran', 'public'),
            'bukti_original_name' => 'lama.png',
            'status' => 'pending',
        ]);

        $updateResponse = $this->actingAs($user)->patch(route('keuangan.pengeluaran.update', $expense), [
            'jumlah' => 225000,
            'deskripsi' => 'Belanja revisi',
            'tanggal' => '2026-04-07',
            'bukti' => UploadedFile::fake()->image('baru.png'),
        ]);

        $updateResponse->assertRedirect();

        $expense->refresh();

        $this->assertSame('Belanja revisi', $expense->deskripsi);
        $this->assertSame('pending', $expense->status);
        $this->assertTrue(Storage::disk('public')->exists($expense->bukti_path));

        $deleteResponse = $this->actingAs($user)->delete(route('keuangan.pengeluaran.destroy', $expense));

        $deleteResponse->assertRedirect(route('keuangan.pengeluaran.index'));
        $this->assertDatabaseMissing('uang_keluar', ['id' => $expense->id]);
    }

    public function test_admin_can_delete_any_income_and_expense_status(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $user = User::factory()->create([
            'role' => 'user',
            'status' => 'active',
        ]);

        $income = UangMasuk::query()->create([
            'created_by' => $user->id,
            'source' => 'pengajuan',
            'jumlah' => 300000,
            'deskripsi' => 'Pengajuan approved',
            'tanggal' => '2026-04-06',
            'status' => 'approved',
        ]);

        $expense = UangKeluar::query()->create([
            'created_by' => $user->id,
            'jumlah' => 110000,
            'deskripsi' => 'Pengeluaran rejected',
            'tanggal' => '2026-04-06',
            'bukti_path' => UploadedFile::fake()->image('reject.png')->store('bukti-pengeluaran', 'public'),
            'bukti_original_name' => 'reject.png',
            'status' => 'rejected',
        ]);

        $this->actingAs($admin)->delete(route('keuangan.approval-pengajuan.destroy', $income))
            ->assertRedirect(route('keuangan.approval-pengajuan.index'));

        $this->actingAs($admin)->delete(route('keuangan.approval-pengeluaran.destroy', $expense))
            ->assertRedirect(route('keuangan.approval-pengeluaran.index'));

        $this->assertDatabaseMissing('uang_masuk', ['id' => $income->id]);
        $this->assertDatabaseMissing('uang_keluar', ['id' => $expense->id]);
    }

    public function test_dashboard_only_counts_approved_transactions(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        UangMasuk::query()->create([
            'created_by' => $admin->id,
            'source' => 'admin',
            'jumlah' => 1000000,
            'deskripsi' => 'Pemasukan approved',
            'tanggal' => now()->toDateString(),
            'status' => 'approved',
        ]);

        UangMasuk::query()->create([
            'created_by' => $admin->id,
            'source' => 'admin',
            'jumlah' => 500000,
            'deskripsi' => 'Pemasukan pending',
            'tanggal' => now()->toDateString(),
            'status' => 'pending',
        ]);

        UangKeluar::query()->create([
            'created_by' => $admin->id,
            'jumlah' => 300000,
            'deskripsi' => 'Pengeluaran approved',
            'tanggal' => now()->toDateString(),
            'bukti_path' => 'bukti-pengeluaran/approved.png',
            'bukti_original_name' => 'approved.png',
            'status' => 'approved',
        ]);

        UangKeluar::query()->create([
            'created_by' => $admin->id,
            'jumlah' => 90000,
            'deskripsi' => 'Pengeluaran pending',
            'tanggal' => now()->toDateString(),
            'bukti_path' => 'bukti-pengeluaran/pending.png',
            'bukti_original_name' => 'pending.png',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Rp 1.000.000');
        $response->assertSee('Rp 300.000');
        $response->assertSee('Rp 700.000');
        $response->assertDontSee('Rp 1.500.000');
    }
}
