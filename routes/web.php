<?php

use App\Http\Controllers\RoleAccessController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard.home');
})->middleware(['auth', 'verified', 'can:dashboard'])->name('dashboard');

Route::middleware('auth')->group(function () {
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
});

require __DIR__ . '/auth.php';
