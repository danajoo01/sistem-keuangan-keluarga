@extends('layouts.master')

@section('title', 'Dashboard')

@section('content')
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Dashboard</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item">Dashboard</li>
        </ul>
    </div>
</div>

<div class="main-content">

    {{-- Welcome Banner --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card stretch stretch-full border-0" style="background: linear-gradient(135deg, #1c4f82 0%, #2d7dd2 100%);">
                <div class="card-body d-flex align-items-center justify-content-between py-4">
                    <div>
                        <h4 class="text-white mb-1">Selamat datang, {{ auth()->user()->name }} 👋</h4>
                        <p class="mb-0" style="color: rgba(255,255,255,.7);">Pantau keuangan keluarga Anda dengan mudah melalui {{ config('app.name') }}.</p>
                    </div>
                    <i class="feather-bar-chart-2" style="font-size: 4rem; color: rgba(255,255,255,.2);"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="row">
        <div class="col-xxl-3 col-xl-6 col-md-6 mb-4">
            <div class="card stretch stretch-full">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between mb-3">
                        <div>
                            <p class="fs-12 fw-medium text-muted mb-1">Pemasukan Bulan Ini</p>
                            <h3 class="fw-bold mb-0">Rp 0</h3>
                        </div>
                        <div class="avatar-text avatar-md rounded-circle bg-soft-success">
                            <i class="feather-trending-up text-success"></i>
                        </div>
                    </div>
                    <p class="fs-12 text-muted mb-0">
                        <span class="badge bg-soft-success text-success me-1">0 transaksi</span>bulan ini
                    </p>
                </div>
            </div>
        </div>

        <div class="col-xxl-3 col-xl-6 col-md-6 mb-4">
            <div class="card stretch stretch-full">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between mb-3">
                        <div>
                            <p class="fs-12 fw-medium text-muted mb-1">Pengeluaran Bulan Ini</p>
                            <h3 class="fw-bold mb-0">Rp 0</h3>
                        </div>
                        <div class="avatar-text avatar-md rounded-circle bg-soft-danger">
                            <i class="feather-trending-down text-danger"></i>
                        </div>
                    </div>
                    <p class="fs-12 text-muted mb-0">
                        <span class="badge bg-soft-danger text-danger me-1">0 transaksi</span>bulan ini
                    </p>
                </div>
            </div>
        </div>

        <div class="col-xxl-3 col-xl-6 col-md-6 mb-4">
            <div class="card stretch stretch-full">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between mb-3">
                        <div>
                            <p class="fs-12 fw-medium text-muted mb-1">Saldo Saat Ini</p>
                            <h3 class="fw-bold mb-0">Rp 0</h3>
                        </div>
                        <div class="avatar-text avatar-md rounded-circle bg-soft-primary">
                            <i class="feather-credit-card text-primary"></i>
                        </div>
                    </div>
                    <p class="fs-12 text-muted mb-0">
                        <span class="badge bg-soft-primary text-primary me-1">—</span>pemasukan - pengeluaran
                    </p>
                </div>
            </div>
        </div>

        <div class="col-xxl-3 col-xl-6 col-md-6 mb-4">
            <div class="card stretch stretch-full">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between mb-3">
                        <div>
                            <p class="fs-12 fw-medium text-muted mb-1">Total Transaksi</p>
                            <h3 class="fw-bold mb-0">0</h3>
                        </div>
                        <div class="avatar-text avatar-md rounded-circle bg-soft-warning">
                            <i class="feather-activity text-warning"></i>
                        </div>
                    </div>
                    <p class="fs-12 text-muted mb-0">
                        <span class="badge bg-soft-warning text-warning me-1">0</span>bulan ini
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Transactions --}}
    <div class="row">
        <div class="col-12">
            <div class="card stretch stretch-full">
                <div class="card-header">
                    <h5 class="card-title mb-0">Transaksi Terbaru</h5>
                </div>
                <div class="card-body text-center py-5">
                    <i class="feather-inbox text-muted" style="font-size: 3rem;"></i>
                    <h6 class="text-muted mt-3 mb-1">Belum ada transaksi</h6>
                    <p class="text-muted fs-12 mb-0">Transaksi yang Anda catat akan muncul di sini.</p>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection