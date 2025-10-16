<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Admin Pertama</title>

    {{-- Memuat CSS & JS dari Vite (Penting untuk styling) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('app.css') }}">

    {{-- Menggunakan gaya yang sama seperti halaman login Anda --}}
    <style>
        .background-radial-gradient {
            background-color: hsl(218, 41%, 15%);
            background-image: radial-gradient(650px circle at 0% 0%, hsl(218, 41%, 35%) 15%, hsl(218, 41%, 30%) 35%, hsl(218, 41%, 20%) 75%, hsl(218, 41%, 19%) 80%, transparent 100%), radial-gradient(1250px circle at 100% 100%, hsl(218, 41%, 45%) 15%, hsl(218, 41%, 30%) 35%, hsl(218, 41%, 20%) 75%, hsl(218, 41%, 19%) 80%, transparent 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
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

        /* Custom form styles menyesuaikan halaman pengaturan */
        .form-group-custom {
            margin-bottom: 1.5rem;
        }

        .form-label-custom {
            font-weight: 500;
            margin-bottom: .5rem;
            display: block;
        }

        .form-control-custom {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #d4d4d4;
            border-radius: 8px;
            font-size: 1rem;
            background: #f8fafc;
            color: #232323;
            transition: border-color .2s;
        }

        .form-control-custom:focus {
            outline: none;
            border-color: #4f46e5;
            background: #fff;
        }

        .btn-primary-custom {
            display: inline-block;
            font-weight: 600;
            color: #fff;
            background: linear-gradient(90deg, #654aa6 0%, #402f75 100%);
            border: none;
            border-radius: 8px;
            padding: 0.85rem 2rem;
            font-size: 1.06rem;
            transition: background .2s;
        }

        .btn-primary-custom:hover,
        .btn-primary-custom:focus {
            background: linear-gradient(90deg, #4f46e5 0%, #3b2ad1 100%);
            color: #fff;
        }
    </style>
</head>

<body>
    <section class="background-radial-gradient overflow-hidden">
        <div class="container px-4 py-5 px-md-5">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5 position-relative">
                    <div id="radius-shape-1" class="position-absolute shadow-5-strong"></div>
                    <div id="radius-shape-2" class="position-absolute shadow-5-strong"></div>

                    <div class="card bg-glass">
                        <div class="card-body px-4 py-5 px-md-5">
                            <h3 class="fw-bold mb-4 text-center">Buat Akun Super Admin</h3>

                            <form method="POST" action="{{ route('setup.process') }}">
                                @csrf

                                @if ($errors->any())
                                    <div class="alert alert-danger mb-4">
                                        <ul class="mb-0">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <div class="form-group-custom">
                                    <label class="form-label-custom" for="name">
                                        <i class="bi bi-person"></i> Nama Lengkap
                                    </label>
                                    <input type="text" id="name" name="name" class="form-control-custom"
                                        placeholder="Masukkan nama Anda" required autofocus value="{{ old('name') }}"
                                        autocomplete="off" />
                                </div>

                                <div class="form-group-custom">
                                    <label class="form-label-custom" for="email">
                                        <i class="bi bi-envelope"></i> Email
                                    </label>
                                    <input type="email" id="email" name="email" class="form-control-custom"
                                        placeholder="Masukkan email" required value="{{ old('email') }}"
                                        autocomplete="off" />
                                </div>

                                <div class="form-group-custom">
                                    <label class="form-label-custom" for="password">
                                        <i class="bi bi-lock"></i> Password
                                    </label>
                                    <input type="password" id="password" name="password" class="form-control-custom"
                                        placeholder="Buat password" required autocomplete="new-password" />
                                </div>

                                <div class="form-group-custom">
                                    <label class="form-label-custom" for="password_confirmation">
                                        <i class="bi bi-shield-lock"></i> Konfirmasi Password
                                    </label>
                                    <input type="password" id="password_confirmation" name="password_confirmation"
                                        class="form-control-custom" placeholder="Ketik ulang password" required
                                        autocomplete="new-password" />
                                </div>

                                <button type="submit" class="btn-primary-custom w-100 mt-3">
                                    Buat Akun & Lanjutkan
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>

</html>
