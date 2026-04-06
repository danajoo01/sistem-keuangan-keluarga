@extends('layouts.master')

@section('title', 'Config Mail')

@section('content')
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Config Mail</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
            <li class="breadcrumb-item">Data Master</li>
            <li class="breadcrumb-item">Config Mail</li>
        </ul>
    </div>
</div>

<div class="main-content">
    <div class="row">
        <div class="col-xl-8">
            <div class="card stretch stretch-full">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Setup Mailing</h5>
                    <span class="badge {{ $mailConfigured ? 'bg-soft-success text-success' : 'bg-soft-warning text-warning' }}">
                        {{ $mailConfigured ? 'Configured' : 'Belum Lengkap' }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="alert alert-light border">
                        Jika konfigurasi mail kosong atau belum lengkap, sistem tidak akan mengirim email otomatis.
                    </div>

                    <form method="POST" action="{{ route('master-data.config-mail.update') }}">
                        @csrf
                        @method('PATCH')

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="mailer">Mailer</label>
                                <input type="text" name="mailer" id="mailer" value="{{ old('mailer', $mailSetting?->mailer ?? 'smtp') }}" class="form-control @error('mailer') is-invalid @enderror">
                                @error('mailer')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="host">Host</label>
                                <input type="text" name="host" id="host" value="{{ old('host', $mailSetting?->host) }}" class="form-control @error('host') is-invalid @enderror">
                                @error('host')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="port">Port</label>
                                <input type="number" name="port" id="port" value="{{ old('port', $mailSetting?->port) }}" class="form-control @error('port') is-invalid @enderror">
                                @error('port')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="encryption">Encryption</label>
                                <input type="text" name="encryption" id="encryption" value="{{ old('encryption', $mailSetting?->encryption) }}" class="form-control @error('encryption') is-invalid @enderror" placeholder="tls / ssl">
                                @error('encryption')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="username">Username</label>
                                <input type="text" name="username" id="username" value="{{ old('username', $mailSetting?->username) }}" class="form-control @error('username') is-invalid @enderror">
                                @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="password">Password</label>
                                <input type="password" name="password" id="password" value="{{ old('password', $mailSetting?->password) }}" class="form-control @error('password') is-invalid @enderror">
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="from_address">From Address</label>
                                <input type="email" name="from_address" id="from_address" value="{{ old('from_address', $mailSetting?->from_address) }}" class="form-control @error('from_address') is-invalid @enderror">
                                @error('from_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="from_name">From Name</label>
                                <input type="text" name="from_name" id="from_name" value="{{ old('from_name', $mailSetting?->from_name) }}" class="form-control @error('from_name') is-invalid @enderror">
                                @error('from_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="mt-4 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Simpan Config Mail</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection