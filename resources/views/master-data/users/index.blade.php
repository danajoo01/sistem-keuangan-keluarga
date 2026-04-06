@extends('layouts.master')

@section('title', 'Data User')

@php
$roleBadgeMap = [
'admin' => ['bg-soft-primary', 'text-primary'],
'user' => ['bg-soft-success', 'text-success'],
];

$statusBadgeMap = [
'active' => ['bg-soft-success', 'text-success'],
'pending' => ['bg-soft-warning', 'text-warning'],
'inactive' => ['bg-soft-danger', 'text-danger'],
];
@endphp

@section('content')
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Data User</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item">Data Master</li>
            <li class="breadcrumb-item">User</li>
        </ul>
    </div>
</div>

<div class="main-content">
    <div class="row">
        <div class="col-12">
            <div class="card stretch stretch-full">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">List User</h5>
                    <span class="fs-12 text-muted">Total {{ $users->total() }} user</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                @php
                                [$roleBg, $roleText] = $roleBadgeMap[$user->role] ?? ['bg-soft-secondary', 'text-muted'];
                                [$statusBg, $statusText] = $statusBadgeMap[$user->status] ?? ['bg-soft-secondary', 'text-muted'];
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $user->name }}</div>
                                        @if(auth()->id() === $user->id)
                                        <span class="fs-12 text-muted">Akun Anda</span>
                                        @endif
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td><span class="badge {{ $roleBg }} {{ $roleText }}">{{ ucfirst($user->role) }}</span></td>
                                    <td><span class="badge {{ $statusBg }} {{ $statusText }}">{{ ucfirst($user->status) }}</span></td>
                                    <td class="text-end">
                                        @can('users')
                                        <a href="{{ route('master-data.users.show', $user) }}" class="btn btn-light btn-sm">Detail</a>
                                        @endcan
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Belum ada data user.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($users->hasPages())
                    <div class="mt-4 d-flex justify-content-end">
                        {{ $users->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection