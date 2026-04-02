@extends('layouts.app')

@section('title', 'Confirm Password')

@section('content')
<div class="min-h-100vh flex grow bg-slate-50 dark:bg-navy-900">
    <main class="mx-auto flex w-full max-w-md flex-col justify-center p-5">
        <div class="rounded-lg bg-white p-6 shadow dark:bg-navy-700">
            <p class="text-sm text-slate-500 dark:text-navy-200">This is a secure area. Confirm your password before continuing.</p>
            <form method="POST" action="{{ route('password.confirm') }}" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-600 dark:text-navy-100">Password</label>
                    <input id="password" type="password" name="password" required autocomplete="current-password" class="form-input mt-1.5 w-full rounded-lg bg-slate-150 px-3 py-2 @error('password') ring-danger @enderror">
                    @error('password')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>
                <button type="submit" class="btn h-10 w-full bg-primary font-medium text-white">Confirm</button>
            </form>
        </div>
    </main>
</div>
@endsection