@extends('layouts.master')

@section('title', 'Role Akses')

@section('content')
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Role Akses</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item">Data Master</li>
            <li class="breadcrumb-item">Role Akses</li>
        </ul>
    </div>
</div>

<div class="main-content">
    <div class="row g-4">
        @foreach($roles as $role)
        <div class="col-md-6">
            <div class="card stretch stretch-full">
                <div class="card-body d-flex justify-content-between align-items-center gap-3">
                    <div>
                        <h5 class="mb-1">{{ $role['name'] }}</h5>
                        <p class="mb-0 text-muted fs-12">Atur hak akses menu untuk role {{ $role['name'] }}.</p>
                    </div>
                    @can('role-akses')
                    <a href="{{ route('master-data.role-access.show', $role['key']) }}" class="btn btn-primary btn-sm">Detail Role</a>
                    @endcan
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection