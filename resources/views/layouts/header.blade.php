<header>
    <div class="topbar d-flex align-items-center">
        <nav class="navbar navbar-expand">
            <div class="mobile-toggle-menu"><i class='bx bx-menu'></i></div>
            <div class="header-datetime d-none d-md-flex align-items-center ms-3">
                <i class="bi bi-calendar3 me-2"></i>
                <span id="currentDate">Memuat tanggal...</span>
                <i class="bi bi-clock ms-3 me-2"></i>
                <span id="currentTime">Memuat jam...</span>
            </div>
            <div class="top-menu ms-auto">
            </div>
            @php
                $id = Auth::user()->id;
                $pData = App\Models\User::find($id);
            @endphp
            <div class="user-box dropdown">
                <a class="d-flex align-items-center nav-link dropdown-toggle dropdown-toggle-nocaret" href="#"
                    role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="{{ Auth::user()->photo ? Illuminate\Support\Facades\Storage::url(Auth::user()->photo) : url('upload/no_image.jpg') }}"
                        class="user-img" alt="user avatar">
                    <div class="user-info ps-3 mt-3">
                        <p class="user-name mb-0">{{ Auth::user()->name }}</p>
                        <p class="designattionmb-0">{{ Auth::user()->email }}</p>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                            <i class="bi bi-person"></i><span> Profil</span>
                        </a>
                    </li>
                    <li>
                        <div class="dropdown-divider mb-0"></div>
                    </li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item"
                                style="border: none !important; outline: none !important; box-shadow: none !important; background: none; padding-left: 15px; width: 100%; text-align: left;">
                                <i class="bi bi-box-arrow-right"></i><span> Logout</span>
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</header>

@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            function updateClock() {
                const now = new Date();

                // 1. Format Tanggal (Contoh: "Sabtu, 18 Oktober 2025")
                const dateOptions = {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                };
                const dateString = now.toLocaleDateString('id-ID', dateOptions);

                // 2. Format Waktu (Contoh: "13:01:05")
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const seconds = String(now.getSeconds()).padStart(2, '0');
                const timeString = `${hours}:${minutes}:${seconds}`;

                // 3. Terapkan ke HTML
                const dateEl = document.getElementById('currentDate');
                const timeEl = document.getElementById('currentTime');

                if (dateEl) {
                    dateEl.textContent = dateString;
                }
                if (timeEl) {
                    timeEl.textContent = timeString;
                }
            }

            // Panggil fungsi sekali saat halaman dimuat
            updateClock();

            // Atur agar fungsi updateClock dipanggil setiap detik
            setInterval(updateClock, 1000);
        });
    </script>
@endpush
