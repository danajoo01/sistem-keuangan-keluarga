@extends('layouts.app')

@section('title', 'Forgot Password')

@section('content')
<div class="min-h-100vh flex grow bg-slate-50 dark:bg-navy-900">
    <main class="mx-auto flex w-full max-w-md flex-col justify-center p-5">
        <div class="rounded-lg bg-white p-6 shadow dark:bg-navy-700">
            <p class="text-sm text-slate-500 dark:text-navy-200">Forgot your password? Enter your email and we will send a reset link.</p>
            @if (session('status'))
            <div class="mt-4 font-medium text-sm text-green-600">{{ session('status') }}</div>
            @endif
            <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-600 dark:text-navy-100">Email</label>
                    <input id="email" class="form-input mt-1.5 w-full rounded-lg bg-slate-150 px-3 py-2 @error('email') ring-danger @enderror" type="email" name="email" value="{{ old('email') }}" required autofocus>
                    @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <button type="submit" class="btn h-10 w-full bg-primary font-medium text-white">Email Password Reset Link</button>
            </form>
        </div>
    </main>
</div>
@endsection