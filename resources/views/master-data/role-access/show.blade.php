@extends('layouts.master')

@section('title', 'Detail Role Akses')

@section('content')
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Detail Role {{ ucfirst($selectedRole) }}</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item">Data Master</li>
            <li class="breadcrumb-item"><a href="{{ route('master-data.role-access.index') }}">Role Akses</a></li>
            <li class="breadcrumb-item">{{ ucfirst($selectedRole) }}</li>
        </ul>
    </div>
</div>

<div class="main-content">
    <div class="row">
        <div class="col-12">
            <div class="card stretch stretch-full">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Mapping Akses Menu</h5>
                    <span class="badge bg-soft-primary text-primary">Role: {{ ucfirst($selectedRole) }}</span>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('master-data.role-access.update', $selectedRole) }}">
                        @csrf
                        @method('PATCH')
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th width="80">Akses</th>
                                        <th>Name</th>
                                        <th>Key</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($menus as $menu)
                                    <tr>
                                        <td>
                                            <div class="form-check form-switch mb-0">
                                                <input
                                                    class="form-check-input"
                                                    type="checkbox"
                                                    role="switch"
                                                    id="menu_{{ $menu->id }}"
                                                    name="menu_ids[]"
                                                    value="{{ $menu->id }}"
                                                    @checked(old('menu_ids') ? in_array($menu->id, old('menu_ids', [])) : $menu->enabled)
                                                >
                                            </div>
                                        </td>
                                        <td>
                                            <label for="menu_{{ $menu->id }}" class="fw-semibold text-dark">{{ $menu->name }}</label>
                                        </td>
                                        <td><code>{{ $menu->key }}</code></td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">Belum ada data menu akses.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Simpan Mapping</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection