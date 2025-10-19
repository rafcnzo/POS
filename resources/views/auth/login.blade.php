<head>
    <title>Login</title>

    {{-- Load CSS & JS dari Vite --}}
    @vite(['resources/css/admin.css', 'resources/js/app.js'])
    <style>
        .background-radial-gradient {
            background-color: hsl(218, 41%, 15%);
            background-image: radial-gradient(650px circle at 0% 0%,
                    hsl(218, 41%, 35%) 15%,
                    hsl(218, 41%, 30%) 35%,
                    hsl(218, 41%, 20%) 75%,
                    hsl(218, 41%, 19%) 80%,
                    transparent 100%),
                radial-gradient(1250px circle at 100% 100%,
                    hsl(218, 41%, 45%) 15%,
                    hsl(218, 41%, 30%) 35%,
                    hsl(218, 41%, 20%) 75%,
                    hsl(218, 41%, 19%) 80%,
                    transparent 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        #radius-shape-1 {
            height: 220px;
            width: 220px;
            top: -60px;
            left: -130px;
            background: radial-gradient(#44006b, #ad1fff);
            overflow: hidden;
            position: absolute;
            border-radius: 50%;
        }

        #radius-shape-2 {
            border-radius: 38% 62% 63% 37% / 70% 33% 67% 30%;
            bottom: -60px;
            right: -110px;
            width: 300px;
            height: 300px;
            background: radial-gradient(#44006b, #ad1fff);
            overflow: hidden;
            position: absolute;
        }

        .bg-glass {
            background-color: hsla(0, 0%, 100%, 0.9) !important;
            backdrop-filter: saturate(200%) blur(25px);
            border-radius: 1rem;
        }

        /* Desktop optimizations */
        @media (min-width: 992px) {
            .content-wrapper {
                max-width: 1400px;
                margin: 0 auto;
            }

            .hero-text h1 {
                font-size: 3.5rem;
                line-height: 1.2;
            }

            .hero-text p {
                font-size: 1.1rem;
                max-width: 90%;
            }

            .login-card {
                max-width: 500px;
                margin-left: auto;
            }

            .card-body {
                padding: 3rem 2.5rem !important;
            }
        }

        @media (min-width: 1200px) {
            .hero-text h1 {
                font-size: 4rem;
            }

            .content-wrapper {
                max-width: 1600px;
            }
        }

        /* Tablet and mobile adjustments */
        @media (max-width: 991px) {
            .background-radial-gradient {
                min-height: auto;
                padding: 2rem 0;
            }

            .hero-text {
                text-align: center !important;
            }

            .hero-text h1 {
                font-size: 2.5rem;
            }
        }

        /* Form enhancements */
        .form-control {
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            border: 1px solid #dee2e6;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }

        .btn-primary {
            padding: 0.75rem 2rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.4);
        }

        .btn-primary:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #212529;
        }

        a {
            transition: all 0.3s ease;
        }

        a:hover {
            opacity: 0.8;
        }

        .shadow-5-strong {
            box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.3);
        }

        /* Loading Overlay untuk Login Card */
        .login-loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(3px);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            border-radius: 1rem;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        .login-loading-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        .login-loading-content {
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

        /* Spinner */
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

        .login-loading-text {
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

        /* Button Loading State */
        .btn-loading {
            position: relative;
            color: transparent !important;
            pointer-events: none;
        }

        .btn-loading::after {
            content: "";
            position: absolute;
            width: 16px;
            height: 16px;
            top: 50%;
            left: 50%;
            margin-left: -8px;
            margin-top: -8px;
            border: 2px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>
    <section class="background-radial-gradient overflow-hidden">
        <div class="container px-4 py-5 px-md-5">
            <div class="row gx-lg-5 align-items-center">
                <div class="col-lg-6 mb-5 mb-lg-0 hero-text" style="z-index: 10">
                    <h1 class="my-5 display-5 fw-bold ls-tight" style="color: hsl(218, 81%, 95%)">
                        Resto POS <br />
                        <span style="color: hsl(218, 81%, 75%)">Welcome Back</span>
                    </h1>
                    <p class="mb-4 opacity-70" style="color: hsl(218, 81%, 85%)">
                        Silakan login untuk melanjutkan ke panel admin atau kasir Anda.
                    </p>
                </div>

                <div class="col-lg-6 mb-5 mb-lg-0 position-relative">
                    <div id="radius-shape-1" class="position-absolute shadow-5-strong"></div>
                    <div id="radius-shape-2" class="position-absolute shadow-5-strong"></div>

                    <div class="card bg-glass login-card position-relative">
                        <div class="card-body px-4 py-5 px-md-5">
                            @if ($errors->any())
                                <div class="alert alert-danger mb-4">
                                    <div class="fw-bold">Gagal Login!</div>
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('login') }}" id="loginForm">
                                @csrf

                                <div class="mb-4">
                                    <label class="form-label" for="email">Email</label>
                                    <input type="email" id="email" name="email" class="form-control"
                                        placeholder="Enter your email" required autofocus value="{{ old('email') }}" />
                                </div>

                                <div class="mb-4">
                                    <label class="form-label" for="password">Password</label>
                                    <input type="password" id="password" name="password" class="form-control"
                                        placeholder="Enter your password" required />
                                </div>

                                <div class="form-check d-flex justify-content-start mb-4">
                                    <input class="form-check-input me-2" type="checkbox" id="remember_me"
                                        name="remember" />
                                    <label class="form-check-label" for="remember_me">
                                        Remember me
                                    </label>
                                </div>

                                <div
                                    class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3 mb-4">
                                    <a class="text-decoration-none text-primary" href="{{ route('password.request') }}">
                                        Forgot your password?
                                    </a>

                                    <button type="submit" class="btn btn-primary w-100 w-sm-auto" id="loginBtn">
                                        Log in
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Loading Overlay -->
                        <div class="login-loading-overlay" id="loginLoadingOverlay">
                            <div class="login-loading-content">
                                <div class="spinner-pulse"></div>
                                <p class="login-loading-text">Memproses login...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('loginForm');
            const loginBtn = document.getElementById('loginBtn');
            const loadingOverlay = document.getElementById('loginLoadingOverlay');

            loginForm.addEventListener('submit', function(e) {
                // Show loading overlay
                if (loadingOverlay) {
                    loadingOverlay.classList.add('show');
                }

                // Cukup disable button aja
                if (loginBtn) {
                    loginBtn.disabled = true;
                    loginBtn.classList.add('btn-loading');
                }
            });
        });
    </script>
</body>
