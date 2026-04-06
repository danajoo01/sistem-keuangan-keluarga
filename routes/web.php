<?php

use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RoleAccessController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UangKeluarController;
use App\Http\Controllers\UangMasukController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified', 'can:dashboard'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/attachments/preview', [AttachmentController::class, 'preview'])->middleware('signed')->name('attachments.preview');
    Route::get('/attachments/download', [AttachmentController::class, 'download'])->middleware('signed')->name('attachments.download');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('master-data')->name('master-data.')->group(function () {
        Route::get('/users', [UserManagementController::class, 'index'])->middleware('can:users')->name('users.index');
        Route::get('/users/{user}', [UserManagementController::class, 'show'])->middleware('can:users')->name('users.show');
        Route::patch('/users/{user}', [UserManagementController::class, 'update'])->middleware('can:users')->name('users.update');
        Route::patch('/users/{user}/approve', [UserManagementController::class, 'approve'])->middleware('can:users')->name('users.approve');

        Route::get('/role-access', [RoleAccessController::class, 'index'])->middleware('can:role-akses')->name('role-access.index');
        Route::get('/role-access/{role}', [RoleAccessController::class, 'show'])->middleware('can:role-akses')->name('role-access.show');
        Route::patch('/role-access/{role}', [RoleAccessController::class, 'update'])->middleware('can:role-akses')->name('role-access.update');
    });

    Route::prefix('keuangan')->name('keuangan.')->group(function () {
        Route::get('/pemasukan', [UangMasukController::class, 'index'])->middleware('can:data-pemasukan')->name('pemasukan.index');
        Route::get('/pemasukan/data', [UangMasukController::class, 'data'])->middleware('can:data-pemasukan')->name('pemasukan.data');
        Route::post('/pemasukan', [UangMasukController::class, 'store'])->middleware('can:data-pemasukan')->name('pemasukan.store');
        Route::get('/pemasukan/{income}', [UangMasukController::class, 'show'])->middleware('can:data-pemasukan')->name('pemasukan.show');
        Route::patch('/pemasukan/{income}', [UangMasukController::class, 'update'])->middleware('can:data-pemasukan')->name('pemasukan.update');
        Route::delete('/pemasukan/{income}', [UangMasukController::class, 'destroy'])->middleware('can:data-pemasukan')->name('pemasukan.destroy');

        Route::get('/pengajuan-dana', [UangMasukController::class, 'index'])->middleware('can:pengajuan-dana')->name('pengajuan-dana.index');
        Route::get('/pengajuan-dana/data', [UangMasukController::class, 'data'])->middleware('can:pengajuan-dana')->name('pengajuan-dana.data');
        Route::post('/pengajuan-dana', [UangMasukController::class, 'store'])->middleware('can:pengajuan-dana')->name('pengajuan-dana.store');
        Route::get('/pengajuan-dana/{income}', [UangMasukController::class, 'show'])->middleware('can:pengajuan-dana')->name('pengajuan-dana.show');
        Route::patch('/pengajuan-dana/{income}', [UangMasukController::class, 'update'])->middleware('can:pengajuan-dana')->name('pengajuan-dana.update');
        Route::delete('/pengajuan-dana/{income}', [UangMasukController::class, 'destroy'])->middleware('can:pengajuan-dana')->name('pengajuan-dana.destroy');

        Route::get('/approval-pengajuan', [UangMasukController::class, 'index'])->middleware('can:approval-pengajuan')->name('approval-pengajuan.index');
        Route::get('/approval-pengajuan/data', [UangMasukController::class, 'data'])->middleware('can:approval-pengajuan')->name('approval-pengajuan.data');
        Route::get('/approval-pengajuan/{income}', [UangMasukController::class, 'show'])->middleware('can:approval-pengajuan')->name('approval-pengajuan.show');
        Route::patch('/approval-pengajuan/{income}', [UangMasukController::class, 'update'])->middleware('can:approval-pengajuan')->name('approval-pengajuan.update');
        Route::delete('/approval-pengajuan/{income}', [UangMasukController::class, 'destroy'])->middleware('can:approval-pengajuan')->name('approval-pengajuan.destroy');

        Route::get('/pengeluaran', [UangKeluarController::class, 'index'])->middleware('can:data-pengeluaran')->name('pengeluaran.index');
        Route::get('/pengeluaran/data', [UangKeluarController::class, 'data'])->middleware('can:data-pengeluaran')->name('pengeluaran.data');
        Route::post('/pengeluaran', [UangKeluarController::class, 'store'])->middleware('can:data-pengeluaran')->name('pengeluaran.store');
        Route::get('/pengeluaran/{expense}', [UangKeluarController::class, 'show'])->middleware('can:data-pengeluaran')->name('pengeluaran.show');
        Route::patch('/pengeluaran/{expense}', [UangKeluarController::class, 'update'])->middleware('can:data-pengeluaran')->name('pengeluaran.update');
        Route::delete('/pengeluaran/{expense}', [UangKeluarController::class, 'destroy'])->middleware('can:data-pengeluaran')->name('pengeluaran.destroy');

        Route::get('/approval-pengeluaran', [UangKeluarController::class, 'index'])->middleware('can:approval-pengeluaran')->name('approval-pengeluaran.index');
        Route::get('/approval-pengeluaran/data', [UangKeluarController::class, 'data'])->middleware('can:approval-pengeluaran')->name('approval-pengeluaran.data');
        Route::get('/approval-pengeluaran/{expense}', [UangKeluarController::class, 'show'])->middleware('can:approval-pengeluaran')->name('approval-pengeluaran.show');
        Route::patch('/approval-pengeluaran/{expense}', [UangKeluarController::class, 'update'])->middleware('can:approval-pengeluaran')->name('approval-pengeluaran.update');
        Route::delete('/approval-pengeluaran/{expense}', [UangKeluarController::class, 'destroy'])->middleware('can:approval-pengeluaran')->name('approval-pengeluaran.destroy');
    });
});

require __DIR__ . '/auth.php';
