@extends('layouts.app')

@section('title', 'Reset Password')

@section('content')
<div class="min-h-100vh flex grow bg-slate-50 dark:bg-navy-900">
    <main class="mx-auto flex w-full max-w-md flex-col justify-center p-5">
        <div class="rounded-lg bg-white p-6 shadow dark:bg-navy-700">
            <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="token" value="{{ $request->route('token') }}">
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-600 dark:text-navy-100">Email</label>
                    <input id="email" class="form-input mt-1.5 w-full rounded-lg bg-slate-150 px-3 py-2 @error('email') ring-danger @enderror" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username">
                    @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-600 dark:text-navy-100">Password</label>
                    <input id="password" class="form-input mt-1.5 w-full rounded-lg bg-slate-150 px-3 py-2 @error('password') ring-danger @enderror" type="password" name="password" required autocomplete="new-password">
                    @error('password')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-600 dark:text-navy-100">Confirm Password</label>
                    <input id="password_confirmation" class="form-input mt-1.5 w-full rounded-lg bg-slate-150 px-3 py-2 @error('password_confirmation') ring-danger @enderror" type="password" name="password_confirmation" required autocomplete="new-password">
                    @error('password_confirmation')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <button type="submit" class="btn h-10 w-full bg-primary font-medium text-white">Reset Password</button>
            </form>
        </div>
    </main>
</div>
@endsection