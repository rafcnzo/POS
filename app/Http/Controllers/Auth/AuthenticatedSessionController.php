<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View | RedirectResponse
    {
        if (User::count() === 0) {
            if (Role::count() === 0) {
                \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'RolePermissionSeeder']);
            }
            return redirect()->route('setup.show');
        }

        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $url  = '';
        $user = Auth::user();

        if ($user->hasRole('Super Admin')) {
            $url = '/admin';
        } elseif ($user->hasRole('Accounting')) {
            $url = '/accounting';
        } elseif ($user->hasRole('Chef')) {
            $url = '/chef';
        } elseif ($user->hasRole('Cashier')) {
            $url = '/cashier/pos';
        } else {
            $url = '';
        }

        return redirect()->intended($url);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
