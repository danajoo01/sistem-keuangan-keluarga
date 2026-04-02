@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="min-h-100vh flex grow bg-slate-50 dark:bg-navy-900">
    <main class="mx-auto flex w-full max-w-md flex-col justify-center p-5">
        <div class="rounded-lg bg-white p-6 shadow dark:bg-navy-700">
            <h2 class="text-2xl font-semibold text-slate-600 dark:text-navy-100">Create Account</h2>
            <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-600 dark:text-navy-100">Name</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" class="form-input mt-1.5 w-full rounded-lg bg-slate-150 px-3 py-2 @error('name') ring-danger @enderror">
                    @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-600 dark:text-navy-100">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" class="form-input mt-1.5 w-full rounded-lg bg-slate-150 px-3 py-2 @error('email') ring-danger @enderror">
                    @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-600 dark:text-navy-100">Password</label>
                    <input id="password" type="password" name="password" required autocomplete="new-password" class="form-input mt-1.5 w-full rounded-lg bg-slate-150 px-3 py-2 @error('password') ring-danger @enderror">
                    @error('password')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-600 dark:text-navy-100">Confirm Password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="form-input mt-1.5 w-full rounded-lg bg-slate-150 px-3 py-2 @error('password_confirmation') ring-danger @enderror">
                    @error('password_confirmation')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <div class="flex items-center justify-between pt-2">
                    <a class="text-sm text-primary hover:underline" href="{{ route('login') }}">Already registered?</a>
                    <button type="submit" class="btn h-10 bg-primary px-4 font-medium text-white">Register</button>
                </div>
            </form>
        </div>
    </main>
</div>
@endsection