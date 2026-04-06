<?php

namespace Database\Seeders;

use App\Models\UangMasuk;
use App\Models\User;
use Illuminate\Database\Seeder;

class UangMasukSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@example.com')->first();
        $user = User::query()->where('email', 'test@example.com')->first();

        if (! $admin || ! $user) {
            return;
        }

        $items = [
            [
                'created_by' => $admin->id,
                'approved_by' => $admin->id,
                'source' => 'admin',
                'jumlah' => 2500000,
                'deskripsi' => 'Gaji bulanan keluarga',
                'tanggal' => now()->startOfMonth()->addDays(1)->toDateString(),
                'status' => 'approved',
                'approval_note' => 'Input langsung admin.',
            ],
            [
                'created_by' => $user->id,
                'approved_by' => $admin->id,
                'source' => 'pengajuan',
                'jumlah' => 750000,
                'deskripsi' => 'Pengajuan dana kebutuhan sekolah',
                'tanggal' => now()->startOfMonth()->addDays(4)->toDateString(),
                'status' => 'approved',
                'approval_note' => 'Disetujui admin.',
            ],
            [
                'created_by' => $user->id,
                'approved_by' => null,
                'source' => 'pengajuan',
                'jumlah' => 300000,
                'deskripsi' => 'Pengajuan dana belanja dapur tambahan',
                'tanggal' => now()->startOfMonth()->addDays(7)->toDateString(),
                'status' => 'pending',
                'approval_note' => null,
            ],
        ];

        foreach ($items as $item) {
            UangMasuk::query()->updateOrCreate(
                [
                    'source' => $item['source'],
                    'deskripsi' => $item['deskripsi'],
                    'tanggal' => $item['tanggal'],
                ],
                $item,
            );
        }
    }
}
