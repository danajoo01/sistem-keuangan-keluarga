<?php

namespace App\Support;

use App\Models\UangKeluar;
use App\Models\UangMasuk;
use App\Models\User;
use App\Notifications\FinanceWorkflowNotification;

class FinanceNotifier
{
    public static function notifyAdminsForSubmission(UangMasuk $income, bool $sendMail = false): void
    {
        $income->loadMissing('creator');

        $payload = [
            'title' => 'Pengajuan dana baru',
            'message' => ($income->creator?->name ?? 'User') . ' mengajukan dana sebesar Rp ' . number_format((float) $income->jumlah, 0, ',', '.'),
            'url' => route('keuangan.approval-pengajuan.show', $income),
            'subject' => 'Pengajuan Dana Baru',
            'action_text' => 'Tinjau Pengajuan',
        ];

        self::notifyAdmins($payload, $sendMail);
    }

    public static function notifyAdminsForExpense(UangKeluar $expense, bool $sendMail = false): void
    {
        $expense->loadMissing('creator');

        $payload = [
            'title' => 'Pengeluaran baru diajukan',
            'message' => ($expense->creator?->name ?? 'User') . ' mengajukan pengeluaran sebesar Rp ' . number_format((float) $expense->jumlah, 0, ',', '.'),
            'url' => route('keuangan.approval-pengeluaran.show', $expense),
            'subject' => 'Pengajuan Pengeluaran Baru',
            'action_text' => 'Tinjau Pengeluaran',
        ];

        self::notifyAdmins($payload, $sendMail);
    }

    public static function notifyUserForSubmissionStatus(UangMasuk $income, bool $sendMail = false): void
    {
        $income->loadMissing('creator');

        if (! $income->creator) {
            return;
        }

        $payload = [
            'title' => 'Status pengajuan dana diperbarui',
            'message' => 'Pengajuan dana Anda kini berstatus ' . strtoupper($income->status) . '.',
            'url' => route('keuangan.pengajuan-dana.show', $income),
            'subject' => 'Update Status Pengajuan Dana',
            'action_text' => 'Lihat Pengajuan',
        ];

        self::notifyUser($income->creator, $payload, $sendMail);
    }

    public static function notifyUserForExpenseStatus(UangKeluar $expense, bool $sendMail = false): void
    {
        $expense->loadMissing('creator');

        if (! $expense->creator) {
            return;
        }

        $payload = [
            'title' => 'Status pengeluaran diperbarui',
            'message' => 'Pengeluaran Anda kini berstatus ' . strtoupper($expense->status) . '.',
            'url' => route('keuangan.pengeluaran.show', $expense),
            'subject' => 'Update Status Pengeluaran',
            'action_text' => 'Lihat Pengeluaran',
        ];

        self::notifyUser($expense->creator, $payload, $sendMail);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private static function notifyAdmins(array $payload, bool $sendMail): void
    {
        if ($sendMail) {
            MailConfiguration::apply();
        }

        $shouldSendMail = $sendMail && MailConfiguration::isConfigured();

        User::query()
            ->where('role', 'admin')
            ->where('status', 'active')
            ->get()
            ->each(fn(User $admin) => $admin->notify(new FinanceWorkflowNotification($payload, $shouldSendMail)));
    }

    /**
     * @param array<string, mixed> $payload
     */
    private static function notifyUser(User $user, array $payload, bool $sendMail): void
    {
        if ($sendMail) {
            MailConfiguration::apply();
        }

        $user->notify(new FinanceWorkflowNotification($payload, $sendMail && MailConfiguration::isConfigured()));
    }
}
