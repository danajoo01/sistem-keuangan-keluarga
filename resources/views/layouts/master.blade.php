<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ config('app.name') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" type="image/png" href="{{ asset('assets/images/logo-full-bu.png') }}">

    <!--! BEGIN: Apps Title-->
    <title>@yield('title', config('app.name'))</title>
    <!--! END: Apps Title-->

    <!--! BEGIN: Bootstrap CSS-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/bootstrap.min.css') }}" />
    <!--! END: Bootstrap CSS-->

    @stack('styles')

    <!-- message toastr -->
    <link rel="stylesheet" href="{{ asset('assets_bu/css/toastr.min.css') }}">
    <script src="{{ asset('assets_bu/js/toastr_jquery.min.js') }}"></script>
    <script src="{{ asset('assets_bu/js/toastr.min.js') }}"></script>

    <!--! BEGIN: Vendors CSS-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendors/css/vendors.min.css') }}" />
    <!--! END: Vendors CSS-->

    <!--! BEGIN: Custom CSS-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/theme.min.css') }}" />
    <!--! END: Custom CSS-->
</head>

<body>
    @include('layouts.sidebar')
    @include('layouts.header')

    <main class="nxl-container">
        <div class="nxl-content">
            <!-- message toastr -->
            @if(session('success'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    toastr.success("{{ session('success') }}");
                });
            </script>
            @elseif(session('error'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    toastr.error("{{ session('error') }}");
                });
            </script>
            @elseif(session('warning'))
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    toastr.warning("{{ session('warning') }}");
                });
            </script>
            @endif

            @yield('content')
        </div>
        @include('layouts.footer')
    </main>

    @include('layouts.js')
</body>

</html>