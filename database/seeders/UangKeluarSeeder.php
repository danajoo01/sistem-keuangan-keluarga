<?php

namespace Database\Seeders;

use App\Models\UangKeluar;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class UangKeluarSeeder extends Seeder
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
                'created_by' => $user->id,
                'approved_by' => $admin->id,
                'jumlah' => 425000,
                'deskripsi' => 'Belanja kebutuhan rumah mingguan',
                'tanggal' => now()->startOfMonth()->addDays(2)->toDateString(),
                'status' => 'approved',
                'approval_note' => 'Bukti lengkap dan valid.',
                'file_name' => 'dummy-belanja-rumah.pdf',
            ],
            [
                'created_by' => $user->id,
                'approved_by' => $admin->id,
                'jumlah' => 180000,
                'deskripsi' => 'Transportasi dan parkir',
                'tanggal' => now()->startOfMonth()->addDays(5)->toDateString(),
                'status' => 'rejected',
                'approval_note' => 'Nominal tidak sesuai bukti.',
                'file_name' => 'dummy-transportasi.pdf',
            ],
            [
                'created_by' => $user->id,
                'approved_by' => null,
                'jumlah' => 95000,
                'deskripsi' => 'Pembelian alat tulis anak',
                'tanggal' => now()->startOfMonth()->addDays(8)->toDateString(),
                'status' => 'pending',
                'approval_note' => null,
                'file_name' => 'dummy-alat-tulis.pdf',
            ],
        ];

        foreach ($items as $item) {
            $relativePath = 'bukti-pengeluaran/' . $item['file_name'];

            if (! Storage::disk('public')->exists($relativePath)) {
                Storage::disk('public')->put($relativePath, $this->minimalPdfContent($item['deskripsi']));
            }

            UangKeluar::query()->updateOrCreate(
                [
                    'deskripsi' => $item['deskripsi'],
                    'tanggal' => $item['tanggal'],
                ],
                [
                    'created_by' => $item['created_by'],
                    'approved_by' => $item['approved_by'],
                    'jumlah' => $item['jumlah'],
                    'deskripsi' => $item['deskripsi'],
                    'tanggal' => $item['tanggal'],
                    'bukti_path' => $relativePath,
                    'bukti_original_name' => $item['file_name'],
                    'status' => $item['status'],
                    'approval_note' => $item['approval_note'],
                ],
            );
        }
    }

    private function minimalPdfContent(string $title): string
    {
        return "%PDF-1.1\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Count 1/Kids[3 0 R]>>endobj\n3 0 obj<</Type/Page/Parent 2 0 R/MediaBox[0 0 300 144]/Contents 4 0 R/Resources<</Font<</F1 5 0 R>>>>>>endobj\n4 0 obj<</Length 62>>stream\nBT\n/F1 12 Tf\n20 100 Td\n(" . $title . ") Tj\nET\nendstream\nendobj\n5 0 obj<</Type/Font/Subtype/Type1/BaseFont/Helvetica>>endobj\nxref\n0 6\n0000000000 65535 f \n0000000010 00000 n \n0000000063 00000 n \n0000000122 00000 n \n0000000249 00000 n \n0000000361 00000 n \ntrailer<</Root 1 0 R/Size 6>>\nstartxref\n431\n%%EOF";
    }
}
