<header>
    <div class="topbar d-flex align-items-center">
        <nav class="navbar navbar-expand">
            <div class="mobile-toggle-menu"><i class='bx bx-menu'></i></div>
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
