<?php

namespace App\Http\Controllers;

use App\Models\UangKeluar;
use App\Models\UangMasuk;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $startOfMonth = now()->startOfMonth()->toDateString();
        $endOfMonth = now()->endOfMonth()->toDateString();

        $approvedIncomeQuery = UangMasuk::query()
            ->where('status', 'approved')
            ->when(! $user->isAdmin(), fn(Builder $query) => $query->where('created_by', $user->id));

        $approvedExpenseQuery = UangKeluar::query()
            ->where('status', 'approved')
            ->when(! $user->isAdmin(), fn(Builder $query) => $query->where('created_by', $user->id));

        $incomeThisMonth = (clone $approvedIncomeQuery)
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->sum('jumlah');

        $expenseThisMonth = (clone $approvedExpenseQuery)
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->sum('jumlah');

        $incomeCountThisMonth = (clone $approvedIncomeQuery)
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->count();

        $expenseCountThisMonth = (clone $approvedExpenseQuery)
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->count();

        $approvedIncomeTotal = (clone $approvedIncomeQuery)->sum('jumlah');
        $approvedExpenseTotal = (clone $approvedExpenseQuery)->sum('jumlah');

        $recentIncomes = (clone $approvedIncomeQuery)
            ->latest('tanggal')
            ->latest('id')
            ->take(5)
            ->get()
            ->map(fn(UangMasuk $income) => [
                'jenis' => $income->isSubmission() ? 'Pemasukan Pengajuan' : 'Pemasukan',
                'deskripsi' => $income->deskripsi,
                'tanggal' => $income->tanggal,
                'jumlah' => (float) $income->jumlah,
                'badge' => 'bg-soft-success text-success',
                'icon' => 'feather-trending-up',
            ]);

        $recentExpenses = (clone $approvedExpenseQuery)
            ->latest('tanggal')
            ->latest('id')
            ->take(5)
            ->get()
            ->map(fn(UangKeluar $expense) => [
                'jenis' => 'Pengeluaran',
                'deskripsi' => $expense->deskripsi,
                'tanggal' => $expense->tanggal,
                'jumlah' => (float) $expense->jumlah,
                'badge' => 'bg-soft-danger text-danger',
                'icon' => 'feather-trending-down',
            ]);

        $recentTransactions = $recentIncomes
            ->concat($recentExpenses)
            ->sortByDesc(fn(array $transaction) => Carbon::parse($transaction['tanggal'])->timestamp)
            ->take(8)
            ->values();

        return view('dashboard.home', [
            'incomeThisMonth' => $incomeThisMonth,
            'expenseThisMonth' => $expenseThisMonth,
            'incomeCountThisMonth' => $incomeCountThisMonth,
            'expenseCountThisMonth' => $expenseCountThisMonth,
            'currentBalance' => $approvedIncomeTotal - $approvedExpenseTotal,
            'totalTransactionsThisMonth' => $incomeCountThisMonth + $expenseCountThisMonth,
            'recentTransactions' => $recentTransactions,
        ]);
    }
}
