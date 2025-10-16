<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $storeName = \App\Models\Setting::where('key', 'store_name')->value('value') ?? 'Restoran Sukses Maju Jaya';
        $storeLogo = \App\Models\Setting::where('key', 'store_logo')->value('value');
        $favicon = $storeLogo ? Storage::url($storeLogo) : asset('backendpenjual/assets/images/logo-icon.png');
    @endphp
    <title>@yield('title', $storeName)</title>
    <link rel="icon" type="image/png" href="{{ $favicon }}">
    @vite(['resources/css/admin.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('app.css') }}">
    @yield('style')
    <style>
        .hidden {
            opacity: 0;
            pointer-events: none;
        }
    </style>
</head>

<body>
    <div id="loadingOverlay" class="loading-overlay">
        <div class="loading-content">
            <div class="loading-spinner"></div>
            <p class="loading-text">Loading...</p>
        </div>
    </div>

    <div class="wrapper">
        @include('layouts.sidebar')
        @include('layouts.header')

        <div class="page-wrapper">
            @yield('content')
        </div>

        <div class="overlay toggle-icon"></div>
        <a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
        @include('layouts.footer')
    </div>

    <script>
        function showLoading(text = 'Loading...') {
            const overlay = document.getElementById('loadingOverlay');
            const loadingText = overlay.querySelector('.loading-text');

            if (loadingText) {
                loadingText.textContent = text;
            }

            overlay.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function hideLoading() {
            const overlay = document.getElementById('loadingOverlay');
            overlay.classList.remove('show');
            document.body.style.overflow = '';
        }

        window.addEventListener('load', function() {
            hideLoading();
        });
    </script>

    <script>
        @if (Session::has('message'))
            var type = "{{ Session::get('alert-type', 'info') }}"
            switch (type) {
                case 'info':
                    toastr.info("{{ Session::get('message') }}");
                    break;
                case 'success':
                    toastr.success("{{ Session::get('message') }}");
                    break;
                case 'warning':
                    toastr.warning("{{ Session::get('message') }}");
                    break;
                case 'error':
                    toastr.error("{{ Session::get('message') }}");
                    break;
            }
        @endif
    </script>
    @stack('script')
</body>

</html>
