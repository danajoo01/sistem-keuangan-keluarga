@extends('layouts.app')

@section('title', 'Verify Email')

@section('content')
<div class="min-h-100vh flex grow bg-slate-50 dark:bg-navy-900">
    <main class="mx-auto flex w-full max-w-md flex-col justify-center p-5">
        <div class="rounded-lg bg-white p-6 shadow dark:bg-navy-700">
            <p class="text-sm text-slate-500 dark:text-navy-200">Thanks for signing up. Verify your email address from the link we sent.</p>
            @if (session('status') == 'verification-link-sent')
            <div class="mt-4 font-medium text-sm text-green-600">A new verification link has been sent to your email address.</div>
            @endif
            <div class="mt-6 flex items-center justify-between gap-3">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="btn h-10 bg-primary px-4 font-medium text-white">Resend Verification Email</button>
                </form>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-primary hover:underline">Log Out</button>
                </form>
            </div>
        </div>
    </main>
</div>
@endsection