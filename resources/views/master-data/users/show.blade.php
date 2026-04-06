@extends('layouts.master')

@section('title', 'Detail User')

@php
$statusBadgeMap = [
'active' => ['bg-soft-success', 'text-success'],
'pending' => ['bg-soft-warning', 'text-warning'],
'inactive' => ['bg-soft-danger', 'text-danger'],
];
[$statusBg, $statusText] = $statusBadgeMap[$managedUser->status] ?? ['bg-soft-secondary', 'text-muted'];
@endphp

@section('content')
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Detail User</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item">Data Master</li>
            <li class="breadcrumb-item"><a href="{{ route('master-data.users.index') }}">User</a></li>
            <li class="breadcrumb-item">{{ $managedUser->name }}</li>
        </ul>
    </div>
</div>

<div class="main-content">
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card stretch stretch-full">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informasi User</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0 detail-list">
                        <dt class="col-sm-4">Nama</dt>
                        <dd class="col-sm-8">{{ $managedUser->name }}</dd>

                        <dt class="col-sm-4">Email</dt>
                        <dd class="col-sm-8">{{ $managedUser->email }}</dd>

                        <dt class="col-sm-4">Role</dt>
                        <dd class="col-sm-8">{{ ucfirst($managedUser->role) }}</dd>

                        <dt class="col-sm-4">Status</dt>
                        <dd class="col-sm-8"><span class="badge {{ $statusBg }} {{ $statusText }}">{{ ucfirst($managedUser->status) }}</span></dd>

                        <dt class="col-sm-4">Bergabung</dt>
                        <dd class="col-sm-8">{{ $managedUser->created_at?->format('d M Y H:i') }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card stretch stretch-full">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Pengaturan User</h5>
                    @if($managedUser->status === 'pending' && auth()->user()->isAdmin())
                    <form method="POST" action="{{ route('master-data.users.approve', $managedUser) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-success btn-sm">Approve</button>
                    </form>
                    @endif
                </div>
                <div class="card-body">
                    @if(auth()->user()->isAdmin())
                    <form method="POST" action="{{ route('master-data.users.update', $managedUser) }}">
                        @csrf
                        @method('PATCH')
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="role" class="form-label">Role</label>
                                <select name="role" id="role" class="form-select @error('role') is-invalid @enderror">
                                    <option value="user" @selected(old('role', $managedUser->role) === 'user')>User</option>
                                    <option value="admin" @selected(old('role', $managedUser->role) === 'admin')>Admin</option>
                                </select>
                                @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" id="status" class="form-select @error('status') is-invalid @enderror">
                                    <option value="pending" @selected(old('status', $managedUser->status) === 'pending')>Pending</option>
                                    <option value="active" @selected(old('status', $managedUser->status) === 'active')>Active</option>
                                    <option value="inactive" @selected(old('status', $managedUser->status) === 'inactive')>Inactive</option>
                                </select>
                                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="mt-4 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        </div>
                    </form>
                    @else
                    <div class="alert alert-light border mb-0">
                        Anda hanya dapat melihat detail akun milik sendiri.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection