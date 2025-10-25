<?php
namespace App\Http\Controllers;

use App\Models\User;
use Brotzka\DotenvEditor\DotenvEditor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Native\Laravel\Facades\App;
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

    public function databaseIndex()
    {

        $dbSettings = [
            'DB_CONNECTION'   => env('DB_CONNECTION', 'sqlite'),
            'DB_HOST'         => env('DB_HOST', '127.0.0.1'),
            'DB_PORT'         => env('DB_PORT', '3306'),
            'DB_USERNAME'     => env('DB_USERNAME', 'root'),
            'DB_PASSWORD'     => env('DB_PASSWORD', ''),
            'DB_DATABASE_ENV' => env('DB_DATABASE', ''),
        ];

        $defaultSqlitePath                    = database_path('nativephp.sqlite');
        $dbSettings['DB_SQLITE_PATH_DEFAULT'] = ($dbSettings['DB_CONNECTION'] == 'sqlite')
            ? $dbSettings['DB_DATABASE_ENV']
            : $defaultSqlitePath;

        $dbSettings['DB_MYSQL_DB_DEFAULT'] = ($dbSettings['DB_CONNECTION'] == 'mysql')
            ? $dbSettings['DB_DATABASE_ENV']
            : '';

        return view('admin.db.index', compact('dbSettings', 'defaultSqlitePath'));
    }

    public function testConnection(Request $request)
    {
        $validated = $request->validate([
            'db_connection' => ['required', Rule::in(['mysql', 'sqlite'])],
            'db_host'       => 'required_if:db_connection,mysql|nullable|string',
            'db_port'       => 'required_if:db_connection,mysql|nullable|integer',
            'db_database'   => 'required|string',
            'db_username'   => 'required_if:db_connection,mysql|nullable|string',
            'db_password'   => 'nullable|string',
        ]);

        // === CEK DRIVER PDO YANG TERSEDIA ===
        $availableDrivers = \PDO::getAvailableDrivers();
        if ($validated['db_connection'] === 'mysql' && ! in_array('mysql', $availableDrivers)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Driver PDO MySQL tidak tersedia. Driver yang tersedia: ' . implode(', ', $availableDrivers) .
                '. Silakan aktifkan extension=pdo_mysql di php.ini Anda.',
            ], 422);
        }

        $connectionName = 'test_connection';
        $config         = [];

        if ($validated['db_connection'] === 'mysql') {
            $config = [
                'driver'    => 'mysql',
                'host'      => $validated['db_host'],
                'port'      => $validated['db_port'],
                'database'  => $validated['db_database'],
                'username'  => $validated['db_username'],
                'password'  => $validated['db_password'] ?? '',
                'charset'   => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix'    => '',
                'strict'    => true,
                'engine'    => null,
                'timeout'   => 5,
                'options'   => [
                    \PDO::ATTR_TIMEOUT => 5,
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                ],
            ];
        } else {
            // Validasi path SQLite
            $dbPath = $validated['db_database'];

            // Jika path relatif, buat absolut
            if (! str_starts_with($dbPath, '/') && ! preg_match('/^[A-Z]:/i', $dbPath)) {
                $dbPath = base_path($dbPath);
            }

            $directory = dirname($dbPath);

            if (! file_exists($directory)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => "Direktori tidak ditemukan: {$directory}",
                ], 422);
            }

            if (! is_writable($directory)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => "Direktori tidak bisa ditulis: {$directory}",
                ], 422);
            }

            $config = [
                'driver'                  => 'sqlite',
                'database'                => $dbPath,
                'prefix'                  => '',
                'foreign_key_constraints' => true,
            ];
        }

        // Set koneksi dinamis
        config(["database.connections.$connectionName" => $config]);
        DB::purge($connectionName);

        try {
            $pdo = DB::connection($connectionName)->getPdo();

            // Test query untuk validasi
            if ($validated['db_connection'] === 'mysql') {
                DB::connection($connectionName)->select('SELECT 1');
                $serverInfo = $pdo->getAttribute(\PDO::ATTR_SERVER_VERSION);
                return response()->json([
                    'status'  => 'success',
                    'message' => "Koneksi MySQL berhasil! (Server: {$serverInfo})",
                ]);
            } else {
                DB::connection($connectionName)->select('SELECT 1');
                return response()->json([
                    'status'  => 'success',
                    'message' => "Koneksi SQLite berhasil! (Path: {$dbPath})",
                ]);
            }

        } catch (\Throwable $e) {
            $errorMessage = $e->getMessage();

            // Pesan error yang lebih deskriptif
            if (str_contains($errorMessage, 'could not find driver')) {
                $errorMessage = 'Driver database tidak ditemukan. Pastikan extension PHP sudah diaktifkan di php.ini';
            } elseif (str_contains($errorMessage, 'Access denied')) {
                $errorMessage = 'Username atau password salah';
            } elseif (str_contains($errorMessage, 'Unknown database')) {
                $errorMessage = 'Database tidak ditemukan. Buat database terlebih dahulu di phpMyAdmin';
            }

            return response()->json([
                'status'  => 'error',
                'message' => "Koneksi Gagal: {$errorMessage}",
            ], 422);
        }
    }

    public function saveConnection(Request $request)
    {
        $validated = $request->validate([
            'db_connection' => ['required', Rule::in(['mysql', 'sqlite'])],
            'db_host'       => 'required_if:db_connection,mysql|nullable|string',
            'db_port'       => 'required_if:db_connection,mysql|nullable|integer',
            'db_database'   => 'required|string',
            'db_username'   => 'required_if:db_connection,mysql|nullable|string',
            'db_password'   => 'nullable|string',
        ]);

        try {
            $env = new DotenvEditor();

            $env->setKey('DB_CONNECTION', $validated['db_connection']);

            if ($validated['db_connection'] === 'mysql') {
                // --- SIMPAN SETTING MYSQL ---
                $env->setKey('DB_HOST', $validated['db_host'] ?? '127.0.0.1');
                $env->setKey('DB_PORT', $validated['db_port'] ?? '3306');
                $env->setKey('DB_DATABASE', $validated['db_database']);
                $env->setKey('DB_USERNAME', $validated['db_username']);
                $env->setKey('DB_PASSWORD', $validated['db_password'] ?? '');
            } else {
                // --- SIMPAN SETTING SQLITE ---
                $env->setKey('DB_DATABASE', $validated['db_database']);
                // Kosongkan key MySQL agar tidak membingungkan
                $env->setKey('DB_HOST', '');
                $env->setKey('DB_PORT', '');
                $env->setKey('DB_USERNAME', '');
                $env->setKey('DB_PASSWORD', '');
            }

            $env->save();
            Artisan::call('config:clear'); // Hapus cache config

            return response()->json([
                'status'  => 'success',
                'message' => 'Pengaturan database berhasil disimpan! Aplikasi akan di-restart...',
            ]);

        } catch (Throwable $e) {
            Log::error("Gagal simpan .env: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Gagal menyimpan pengaturan: ' . $e->getMessage()], 500);
        }
    }
}
