@extends('layouts.app')

@section('title', 'Sign In')

@section('content')
<div id="root" class="min-h-100vh flex grow bg-slate-50 dark:bg-navy-900" x-cloak>
    <div class="fixed top-0 hidden p-6 lg:block lg:px-12">
        <a href="{{ url('/') }}" class="flex items-center space-x-2">
            <img class="size-12" style="width: 120px;" src="{{ asset('assets/images/logo-full-bu.png') }}" alt="logo">
            <p class="text-xl font-semibold uppercase text-slate-700 dark:text-navy-100">{{ config('app.name') }}</p>
        </a>
    </div>

    <div class="hidden w-full place-items-center lg:grid">
        <div class="w-full max-w-lg p-6">
            <img class="w-full" x-show="!$store.global.isDarkModeEnabled" src="{{ asset('assets_bu/images/illustrations/dashboard-check.svg') }}" alt="image">
            <img class="w-full" x-show="$store.global.isDarkModeEnabled" src="{{ asset('assets_bu/images/illustrations/dashboard-check-dark.svg') }}" alt="image">
        </div>
    </div>

    <main class="flex w-full flex-col items-center bg-white dark:bg-navy-700 lg:max-w-md">
        <div class="flex w-full max-w-sm grow flex-col justify-center p-5">
            <div class="text-center">
                <img class="mx-auto size-16 lg:hidden" style="width: 120px;" src="{{ asset('assets/images/logo-full-bu.png') }}" alt="logo">
                <div class="mt-4">
                    <h2 class="text-2xl font-semibold text-slate-600 dark:text-navy-100">Welcome Back</h2>
                    <p class="text-slate-400 dark:text-navy-300">Please sign in to continue</p>
                </div>
            </div>

            @if (session('status'))
            <div class="mt-6 text-center font-medium text-sm text-green-600">{{ session('status') }}</div>
            @endif

            <form action="{{ route('login') }}" method="POST">
                @csrf

                <div class="mt-8">
                    <label class="relative flex">
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="@error('email') ring-danger @enderror form-input peer w-full rounded-lg bg-slate-150 px-3 py-2 pl-9 ring-primary/50 placeholder:text-slate-400 hover:bg-slate-200 focus:ring dark:bg-navy-900/90 dark:ring-accent/50 dark:placeholder:text-navy-300 dark:hover:bg-navy-900 dark:focus:bg-navy-900" placeholder="Enter Email">
                        <span class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5 transition-colors duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </span>
                    </label>
                    @error('email')
                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror

                    <label class="relative mt-4 flex">
                        <input type="password" name="password" required autocomplete="current-password" class="@error('password') ring-danger @enderror form-input peer w-full rounded-lg bg-slate-150 px-3 py-2 pl-9 ring-primary/50 placeholder:text-slate-400 hover:bg-slate-200 focus:ring dark:bg-navy-900/90 dark:ring-accent/50 dark:placeholder:text-navy-300 dark:hover:bg-navy-900 dark:focus:bg-navy-900" placeholder="Enter Password">
                        <span class="pointer-events-none absolute flex h-full w-10 items-center justify-center text-slate-400 peer-focus:text-primary dark:text-navy-300 dark:peer-focus:text-accent">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5 transition-colors duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </span>
                    </label>
                    @error('password')
                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror

                    <div class="mt-4 flex items-center justify-between text-sm">
                        <label class="inline-flex items-center gap-2 text-slate-500 dark:text-navy-200">
                            <input id="remember_me" type="checkbox" name="remember" class="form-checkbox rounded border-slate-400/70">
                            <span>Remember me</span>
                        </label>
                        @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-primary hover:underline dark:text-accent">Forgot password?</a>
                        @endif
                    </div>

                    <button type="submit" class="btn mt-8 h-10 w-full bg-primary font-medium text-white hover:bg-primary-focus focus:bg-primary-focus active:bg-primary-focus/90 dark:bg-accent dark:hover:bg-accent-focus dark:focus:bg-accent-focus dark:active:bg-accent/90">
                        Sign In
                    </button>
                </div>
            </form>
        </div>

        <div class="my-5 flex justify-center text-xs text-slate-400 dark:text-navy-300">
            <span>Copyright &copy;</span>
            <script>
                document.write(new Date().getFullYear());
            </script>
            <span class="ml-1">{{ config('app.name') }}</span>
        </div>
    </main>
</div>
@endsection