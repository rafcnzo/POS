<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SetupController extends Controller
{
    public function showSetupForm()
    {
        if (User::count() > 0) {
            return redirect()->route('login');
        }
        return view('auth.setup');
    }

    public function processSetup(Request $request)
    {
        if (User::count() > 0) {
            return redirect()->route('login');
        }

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $superAdminRole = Role::firstOrCreate(['name' => 'Super Admin']);
        $user->assignRole($superAdminRole);

        return redirect()->route('login')->with('status', 'Akun Super Admin berhasil dibuat! Silakan login.');
    }
}
