@extends('layouts.master')

@section('title', 'Data User')

@php
$isEdit = $managedUser && $mode === 'edit';
$isView = $managedUser && $mode === 'view';
$isCreate = ! $managedUser || $mode === 'create';
$formAction = $isEdit ? $updateUrl : $storeUrl;
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
    <div class="row g-4">
        <div class="col-xl-7">
            <div class="card stretch stretch-full">
                <div class="card-header d-flex justify-content-between align-items-center gap-3 flex-wrap">
                    <div>
                        <h5 class="card-title mb-1">List User</h5>
                        <p class="text-muted mb-0 fs-12">Kelola akun user, role, dan status aktif langsung dari halaman ini.</p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="fs-12 text-muted">Total {{ $users->total() }} user</span>
                        @if($canManageUsers)
                        <a href="{{ route('master-data.users.index') }}" class="btn btn-primary btn-sm">Tambah User</a>
                        @endif
                    </div>
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
                                        <div class="d-flex justify-content-end gap-2 flex-wrap">
                                            <a href="{{ route('master-data.users.show', $user) }}" class="btn btn-light btn-sm">View</a>
                                            @if($canManageUsers)
                                            <a href="{{ route('master-data.users.show', ['user' => $user, 'mode' => 'edit']) }}" class="btn btn-primary btn-sm">Edit</a>
                                            @endif
                                        </div>
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

        <div class="col-xl-5">
            <div class="card stretch stretch-full">
                <div class="card-header d-flex justify-content-between align-items-center gap-2 flex-wrap">
                    <h5 class="card-title mb-0">
                        @if($isCreate)
                        Form User
                        @elseif($isEdit)
                        Edit User
                        @else
                        Detail User
                        @endif
                    </h5>

                    @if($managedUser)
                    @php
                    [$statusBg, $statusText] = $statusBadgeMap[$managedUser->status] ?? ['bg-soft-secondary', 'text-muted'];
                    [$roleBg, $roleText] = $roleBadgeMap[$managedUser->role] ?? ['bg-soft-secondary', 'text-muted'];
                    @endphp
                    <div class="d-flex gap-2">
                        <span class="badge {{ $statusBg }} {{ $statusText }}">{{ ucfirst($managedUser->status) }}</span>
                        <span class="badge {{ $roleBg }} {{ $roleText }}">{{ ucfirst($managedUser->role) }}</span>
                    </div>
                    @endif
                </div>
                <div class="card-body">
                    @if($managedUser)
                    <div class="alert alert-light border mb-4">
                        <div class="d-flex justify-content-between flex-wrap gap-2">
                            <div>
                                <div class="fs-12 text-muted">Bergabung</div>
                                <div class="fw-semibold">{{ $managedUser->created_at?->format('d M Y H:i') }}</div>
                            </div>
                            <div>
                                <div class="fs-12 text-muted">Verifikasi Email</div>
                                <div class="fw-semibold">{{ $managedUser->email_verified_at ? 'Terverifikasi' : 'Belum Verifikasi' }}</div>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($canManageUsers)
                    <form method="POST" action="{{ $formAction }}">
                        @csrf
                        @if($isEdit)
                        @method('PATCH')
                        @endif

                        <div class="row g-3">
                            <div class="col-12">
                                <label for="name" class="form-label">Nama</label>
                                <input
                                    type="text"
                                    name="name"
                                    id="name"
                                    value="{{ old('name', $managedUser?->name) }}"
                                    class="form-control @error('name') is-invalid @enderror"
                                    @disabled($isView)>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <label for="email" class="form-label">Email</label>
                                <input
                                    type="email"
                                    name="email"
                                    id="email"
                                    value="{{ old('email', $managedUser?->email) }}"
                                    class="form-control @error('email') is-invalid @enderror"
                                    @disabled($isView)>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label for="role" class="form-label">Role</label>
                                <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" @disabled($isView)>
                                    <option value="user" @selected(old('role', $managedUser?->role ?? 'user') === 'user')>User</option>
                                    <option value="admin" @selected(old('role', $managedUser?->role) === 'admin')>Admin</option>
                                </select>
                                @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" @disabled($isView)>
                                    <option value="pending" @selected(old('status', $managedUser?->status ?? 'pending') === 'pending')>Pending</option>
                                    <option value="active" @selected(old('status', $managedUser?->status) === 'active')>Active</option>
                                    <option value="inactive" @selected(old('status', $managedUser?->status) === 'inactive')>Inactive</option>
                                </select>
                                @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label for="password" class="form-label">{{ $isEdit ? 'Password Baru' : 'Password' }}</label>
                                <input
                                    type="password"
                                    name="password"
                                    id="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    @disabled($isView)>
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                @if($isEdit)
                                <div class="fs-12 text-muted mt-1">Kosongkan jika password tidak ingin diubah.</div>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                                <input
                                    type="password"
                                    name="password_confirmation"
                                    id="password_confirmation"
                                    class="form-control"
                                    @disabled($isView)>
                            </div>
                        </div>

                        <div class="mt-4 d-flex justify-content-between align-items-center gap-2 flex-wrap">
                            <div class="d-flex gap-2 flex-wrap">
                                @if($managedUser && $managedUser->status === 'pending')
                                <button type="submit" form="approve-user-form" class="btn btn-success">Approve</button>
                                @endif

                                @if($managedUser && $canDeleteManagedUser)
                                <button type="submit" form="delete-user-form" class="btn btn-outline-danger" onclick="return confirm('Hapus user ini?');">Hapus</button>
                                @endif
                            </div>

                            @if(! $isView || $isCreate)
                            <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Simpan Perubahan' : 'Simpan User' }}</button>
                            @endif
                        </div>
                    </form>

                    @if($managedUser && $managedUser->status === 'pending')
                    <form id="approve-user-form" method="POST" action="{{ route('master-data.users.approve', $managedUser) }}">
                        @csrf
                        @method('PATCH')
                    </form>
                    @endif

                    @if($managedUser && $canDeleteManagedUser)
                    <form id="delete-user-form" method="POST" action="{{ $deleteUrl }}">
                        @csrf
                        @method('DELETE')
                    </form>
                    @endif
                    @else
                    <div class="alert alert-light border mb-0">
                        Anda hanya dapat melihat daftar user tanpa mengubah data.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection