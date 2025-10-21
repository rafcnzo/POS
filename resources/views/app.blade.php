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

        #pageLoadingOverlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(2px);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 99999;
            opacity: 1;
            visibility: visible;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        #pageLoadingOverlay.hidden {
            opacity: 0;
            visibility: hidden;
        }

        /* Loading Box - Kecil di Tengah */
        .loading-box {
            background: #fff;
            padding: 2rem 2.5rem;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            text-align: center;
            animation: fadeInScale 0.4s ease;
        }

        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: scale(0.9) translateY(10px);
            }

            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        /* Pulse Circle Spinner */
        .spinner-pulse {
            width: 50px;
            height: 50px;
            background: #0d6efd;
            border-radius: 50%;
            margin: 0 auto 1rem;
            animation: pulse 1.2s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(0.8);
                opacity: 0.5;
            }

            50% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .loading-text {
            color: #495057;
            font-size: 0.9rem;
            font-weight: 500;
            margin: 0;
            animation: textFade 1.5s ease-in-out infinite;
        }

        @keyframes textFade {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.6;
            }
        }
    </style>
</head>

<body>
    <div id="pageLoadingOverlay">
        <div class="loading-box">
            <div class="spinner-pulse"></div>
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
        function showLoading(message = 'Memuat data...') {
            const overlay = document.getElementById('pageLoadingOverlay');
            if (overlay) {
                const textEl = overlay.querySelector('.loading-text');
                if (textEl) textEl.textContent = message;
                overlay.classList.remove('hidden');
            }
        }

        function hideLoading() {
            const overlay = document.getElementById('pageLoadingOverlay');
            if (overlay) {
                overlay.classList.add('hidden');
            }
        }

        // Auto hide saat page selesai load
        window.addEventListener('load', function() {
            setTimeout(() => {
                hideLoading();
            }, 300);
        });

        // Show loading saat navigasi ke halaman lain
        document.addEventListener('DOMContentLoaded', function() {
            // Show loading saat klik link navigasi
            const links = document.querySelectorAll('a:not([target="_blank"]):not([href^="#"]):not(.no-loading)');
            links.forEach(link => {
                link.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');
                    if (href && !href.startsWith('javascript:') && !href.startsWith('#')) {
                        showLoading('Memuat halaman...');
                    }
                });
            });
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

    <script>
        window.correctAuthPassword = @json($globalAuthPassword ?? '');
        function withAuth(actionToPerform) {
            // Cek jika password kosong
            if (!window.correctAuthPassword) {
                Swal.fire('Otorisasi Gagal', 'Password otorisasi belum di-set di sistem.', 'error');
                return; // Hentikan aksi
            }

            Swal.fire({
                title: 'Otorisasi Diperlukan',
                text: 'Masukkan password otorisasi untuk melanjutkan:',
                input: 'password',
                inputPlaceholder: 'Password...',
                inputAttributes: {
                    autocapitalize: 'off',
                    autocorrect: 'off'
                },
                showCancelButton: true,
                confirmButtonText: 'Otorisasi',
                cancelButtonText: 'Batal',
                showLoaderOnConfirm: true,
                preConfirm: (password) => {
                    // Cek password
                    if (password === window.correctAuthPassword) {
                        return true; // Password benar
                    } else {
                        Swal.showValidationMessage('Password otorisasi salah!');
                        return false; // Password salah
                    }
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    // Jika password benar (isConfirmed), jalankan aksi
                    actionToPerform();
                }
                // Jika batal (isDismissed), tidak terjadi apa-apa
            });
        }
    </script>
    @stack('script')
</body>

</html>
