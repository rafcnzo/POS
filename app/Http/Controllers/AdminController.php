<?php
namespace App\Http\Controllers;

use App\Models\DatabaseBackup;
use App\Models\EnergyCost;
use App\Models\Ingredient;
use App\Models\Karyawan;
use App\Models\MenuItem;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Setting;
use App\Models\AllowedIp;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage as FacadesStorage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Cache;
use Throwable;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    public function index()
    {
        $totalIngredients = Ingredient::count();
        $pendingPO        = PurchaseOrder::where('status', 'diproses')->count();
        $totalSuppliers   = Supplier::count();

        $currentMonth  = now()->month;
        $currentYear   = now()->year;
        $previousMonth = now()->subMonth()->month;
        $previousYear  = now()->subMonth()->year;

        $totalSales = Sale::whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->where('status', 'completed')
            ->where('type', 'regular')
            ->sum('total_amount');

        $totalSalesLastMonth = Sale::whereMonth('created_at', $previousMonth)
            ->whereYear('created_at', $previousYear)
            ->where('status', 'completed')
            ->where('type', 'regular')
            ->sum('total_amount');

        if ($totalSalesLastMonth > 0) {
            $salesGrowth = number_format((($totalSales - $totalSalesLastMonth) / $totalSalesLastMonth) * 100, 1) . '%';
        } elseif ($totalSales > 0) {
            $salesGrowth = '100%';
        } else {
            $salesGrowth = '0%';
        }

        $totalTransactions = Sale::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('type', 'regular')
            ->count();

        $previousMonth              = now()->subMonth()->month;
        $previousYear               = now()->subMonth()->year;
        $totalTransactionsLastMonth = Sale::whereMonth('created_at', $previousMonth)
            ->whereYear('created_at', $previousYear)
            ->count();

        if ($totalTransactionsLastMonth > 0) {
            $transactionsGrowth = number_format((($totalTransactions - $totalTransactionsLastMonth) / $totalTransactionsLastMonth) * 100, 1) . '%';
        } elseif ($totalTransactions > 0) {
            $transactionsGrowth = '100%';
        } else {
            $transactionsGrowth = '0%';
        }

        $averageTransaction = 0;
        if ($totalTransactions > 0) {
            $averageTransaction = round(
                Sale::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->where('type', 'regular')
                    ->where('status', 'completed')
                    ->avg('total_amount'), 0
            );
        }

        $topProductsRaw = SaleItem::selectRaw('menu_item_id, SUM(quantity) as total_sold, SUM(subtotal) as total_revenue')
            ->whereHas('sale', function ($q) {
                $q->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
            })
            ->groupBy('menu_item_id')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        $topProducts = [];
        foreach ($topProductsRaw as $saleItem) {
            $menuItem = MenuItem::find($saleItem->menu_item_id);
            if ($menuItem) {
                $topProducts[] = [
                    'name'    => $menuItem->name,
                    'sales'   => $saleItem->total_sold,
                    'unit'    => $menuItem->unit ?? 'x terjual',
                    'revenue' => $saleItem->total_revenue,
                ];
            }
        }

        $latestPurchaseOrders = PurchaseOrder::with([
            'supplier',
            'items.itemable',
        ])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(function ($po) {
                $items = $po->items;
                // Compose item descriptions as: [qty]x [nama] @ [harga_satuan] (contoh: 15x Gula @ Rp 12.000)
                $itemSummaries = $items->map(function ($poItem) {
                    if (! $poItem->itemable) {
                        return '[Item Dihapus]';
                    }

                    // Nama item
                    if ($poItem->itemable instanceof \App\Models\Ingredient) {
                        return $poItem->itemable->name;
                    }
                    else if ($poItem->itemable instanceof \App\Models\Ffne) {
                        return $poItem->itemable->nama_ffne; // Ambil nama ffne
                    } else {
                        $name = '[Tipe Tidak Dikenal]';
                    }

                    // Format: 10x Gula @ Rp 12.000
                    $qty = (int) $poItem->quantity;
                    $unitPrice = (int) $poItem->price;
                    $unitPriceString = 'Rp ' . number_format($unitPrice, 0, ',', '.');

                    return "{$qty}x {$name} @ {$unitPriceString}";
                });

                $showItems = $itemSummaries->take(3)->toArray(); // 3 item pertama
                $othersCount = $items->count() - 3;
                $desc = implode(', ', $showItems);

                if ($othersCount > 0) {
                    $desc .= ' (+' . $othersCount . ' lainnya)';
                }

                $desc = $desc ?: '-';
                $time = $po->created_at ? $po->created_at->diffForHumans() : '-';

                return [
                    'code'   => $po->po_number,
                    'vendor' => $po->supplier->name ?? '-',
                    'desc'   => $desc . " ({$items->count()} items)",
                    'time'   => $time,
                    'status' => ucfirst($po->status),
                    'amount' => $po->total_amount,
                ];
            })
            ->toArray();

        $stockAlertsCount = Ingredient::count();

        $stockAlerts = Ingredient::orderBy('stock', 'asc')
            ->limit(5)
            ->get()
            ->map(function ($ingredient) {
                if ($ingredient->stock < $ingredient->minimum_stock) {
                    $critical = true;
                    $desc = 'Stok kritis - Order sekarang!';
                } elseif ($ingredient->stock == $ingredient->minimum_stock) {
                    $critical = false;
                    $desc = 'Stok menipis - Perlu restock';
                } else {
                    $critical = false;
                    $desc = 'Stok aman';
                }
                return [
                    'name'     => $ingredient->name,
                    'desc'     => $desc,
                    'qty'      => $ingredient->stock . ' ' . $ingredient->unit,
                    'critical' => $critical,
                ];
            })
            ->toArray();

        $energyCost = EnergyCost::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('cost');

        $result = Sale::query()
            ->select(
                DB::raw("HOUR(created_at) as hour_of_day"),
                DB::raw("COUNT(id) as sales_count")
            )
            ->where('status', 'completed')
            ->groupBy('hour_of_day')
            ->orderByDesc('sales_count')
            ->first();

        $busiestHour = null;

        if ($result) {
            $hour = (int) $result->hour_of_day;

            $startTime = Carbon::createFromTime($hour)->format('H:i');
            $endTime   = Carbon::createFromTime($hour + 1)->format('H:i');

            $busiestHour = "{$startTime} - {$endTime}";
        }

        $salesChartLabels = [];
        $dateKeys         = [];
        for ($i = 6; $i >= 0; $i--) {
            $date               = now()->subDays($i);
            $salesChartLabels[] = $date->translatedFormat('D');
            $dateKeys[]         = $date->format('Y-m-d');
        }

        $salesData = Sale::query()
            ->select(
                DB::raw("DATE(created_at) as sale_date"),
                DB::raw("SUM(total_amount) as daily_total")
            )
            ->where('status', 'completed')
            ->where('type', 'regular')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('sale_date')
            ->get()
            ->pluck('daily_total', 'sale_date');

        $salesChartData = [];
        foreach ($dateKeys as $date) {
            $salesChartData[] = $salesData->get($date, 0);
        }

        $targetHarian     = (float) (Setting::where('key', 'daily_sales_target')->value('value') ?? 6000000);
        $salesChartTarget = array_fill(0, 7, $targetHarian);

        return view('admin.index', compact(
            'totalIngredients',
            'pendingPO',
            'totalSuppliers',
            'totalSales',
            'totalSalesLastMonth',
            'salesGrowth',
            'totalTransactions',
            'averageTransaction',
            'totalTransactionsLastMonth',
            'transactionsGrowth',
            'topProducts',
            'latestPurchaseOrders',
            'stockAlerts',
            'busiestHour',
            'salesChartLabels',
            'salesChartData',
            'salesChartTarget',
            'stockAlertsCount',
            'energyCost'
        ));
    }

    public function users_index()
    {
        $users = \App\Models\User::with('roles')->get();
        $roles = \Spatie\Permission\Models\Role::all();
        return view('admin.users.index', compact('users', 'roles'));
    }

    public function users_submit(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255',
            'password' => $request->input('id') ? 'nullable|string|min:6' : 'required|string|min:6',
            'roles'    => 'required|array', // Validasi 'roles' sebagai array
            'roles.*'  => 'string|exists:roles,name',
        ]);

        try {
            $data = [
                'name'  => $validated['name'],
                'email' => $validated['email'],
            ];

            if (! empty($validated['password'])) {
                $data['password'] = bcrypt($validated['password']);
            }

            if ($request->filled('id')) {
                $user = \App\Models\User::find($request->input('id'));
                if ($user) {
                    $user->update($data);
                    $user->syncRoles($request->roles);
                    return response()->json([
                        'status'  => 'success',
                        'message' => 'Data user berhasil diupdate.',
                    ]);
                } else {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'User tidak ditemukan.',
                    ], 404);
                }
            } else {
                $exists = \App\Models\User::where('email', $validated['email'])->exists();
                if ($exists) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'Email sudah terdaftar.',
                    ], 422);
                }

                $user = \App\Models\User::create($data);
                $user->syncRoles($request->roles);

                return response()->json([
                    'status'  => 'success',
                    'message' => 'User baru berhasil ditambahkan.',
                ]);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validasi gagal.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function users_delete($id)
    {
        try {
            $user = \App\Models\User::find($id);
            if (! $user) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'User tidak ditemukan.',
                ], 404);
            }

            $user->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'User berhasil dihapus.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function rolesIndex()
    {
        $roles       = Role::withCount('users', 'permissions')->get();
        $permissions = Permission::all()->sortBy('name');

        return view('admin.roles.index', compact('roles', 'permissions'));
    }

    public function rolesSubmit(Request $request)
    {
        $roleId = $request->input('id');

        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:100', Rule::unique('roles', 'name')->ignore($roleId)],
            'permissions'   => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ], [
            'name.required'        => 'Nama role wajib diisi.',
            'name.unique'          => 'Nama role ini sudah ada.',
            'permissions.*.exists' => 'Permission yang dipilih tidak valid.',
        ]);

        try {
            DB::beginTransaction();

            $role = null;
            if ($roleId) {
                $role = Role::findOrFail($roleId);
                if ($role->name === 'Super Admin' && $validated['name'] !== 'Super Admin') {
                    return response()->json(['status' => 'error', 'message' => 'Nama role Super Admin tidak boleh diubah.'], 403);
                }
                $role->update(['name' => $validated['name']]);
                $message = 'Role berhasil diperbarui.';
            } else {
                $role    = Role::create(['name' => $validated['name']]);
                $message = 'Role baru berhasil ditambahkan.';
            }

            if ($role->name !== 'Super Admin') {
                $role->syncPermissions($validated['permissions'] ?? []);
            }

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => $message,
            ]);

        } catch (Throwable $e) {
            DB::rollBack();
            \Log::error("Error saving role: " . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function rolesDestroy(Role $role)
    {
        try {
            if ($role->name === 'Super Admin') {
                return response()->json(['status' => 'error', 'message' => 'Role Super Admin tidak bisa dihapus.'], 403);
            }

            if ($role->users()->count() > 0) {
                return response()->json(['status' => 'error', 'message' => 'Role tidak bisa dihapus karena masih digunakan oleh user.'], 422);
            }

            $role->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'Role berhasil dihapus.',
            ]);

        } catch (Throwable $e) {
            \Log::error("Error deleting role: " . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }
    public function settings()
    {
        $settings = Setting::pluck('value', 'key');
        $printers = [];
        try {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                // Perintah untuk Windows
                $output = shell_exec('wmic printer get name');
                $lines  = explode("\n", $output);
                // Parsing hasil dari wmic
                foreach ($lines as $line) {
                    $trimmedLine = trim($line);
                    if (! empty($trimmedLine) && $trimmedLine !== 'Name') {
                        $printers[] = ['name' => $trimmedLine];
                    }
                }
            } else {
                // Perintah untuk macOS & Linux
                $output = shell_exec('lpstat -p');
                $lines  = explode("\n", $output);
                // Parsing hasil dari lpstat
                foreach ($lines as $line) {
                    if (strpos($line, 'printer') === 0) {
                        $parts      = explode(' ', $line);
                        $printers[] = ['name' => $parts[1]];
                    }
                }
            }
        } catch (\Exception $e) {
            // Jika exec() gagal, setidaknya halaman tidak error
            $printers = [];
        }

        return view('admin.settings.index', compact('settings', 'printers'));
    }

    public function settingsUpdate(Request $request)
    {
        $dataToStore = $request->except('_token', 'store_logo');

        try {
            if ($request->hasFile('store_logo')) {
                $request->validate([
                    'store_logo' => [
                        'image',
                        'mimes:jpeg,png,jpg,gif,svg',
                        'max:2048',
                        function($attribute, $value, $fail) use ($request) {
                            $image = $request->file('store_logo');
                            if ($image) {
                                $dimensions = getimagesize($image->getRealPath());
                                if ($dimensions) {
                                    $width = $dimensions[0];
                                    $height = $dimensions[1];
                                    if ($width > 250 || $height > 250) {
                                        $fail('Pixel gambar maksimal 250 x 250.');
                                    }
                                }
                            }
                        }
                    ]
                ]);

                // Hapus logo lama jika ada
                $oldLogo = Setting::where('key', 'store_logo')->value('value');
                if ($oldLogo && FacadesStorage::disk('public')->exists($oldLogo)) {
                    FacadesStorage::disk('public')->delete($oldLogo);
                }

                // Simpan file baru
                $path = $request->file('store_logo')->store('logos', 'public');

                $setting        = Setting::where('key', 'store_logo')->firstOrNew(['key' => 'store_logo']);
                $setting->value = $path;
                $setting->save();
            }

            foreach ($dataToStore as $key => $value) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value ?? '']
                );

                // Jika yang diupdate adalah authorization_password, hapus cache terkait
                if ($key === 'authorization_password') {
                    Cache::forget('global_auth_password');
                }
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Pengaturan berhasil diperbarui!',
            ]);
        } catch (\Exception $e) {
            \Log::error('Settings update error:', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function backupIndex()
    {
        $backups = DatabaseBackup::latest()->get();
        return view('admin.backups.index', compact('backups'));
    }

    public function backupCreate()
    {
        $dbPathFromConfig = config('database.connections.sqlite.database');

        $isAbsolutePath = Str::startsWith($dbPathFromConfig, ['/', '\\']) || preg_match('/^[A-Z]:[\\\\\/]/', $dbPathFromConfig);

        if (! $isAbsolutePath) {
            $sourcePath = base_path($dbPathFromConfig);
        } else {
            $sourcePath = $dbPathFromConfig;
        }

        if (! file_exists($sourcePath)) {
            $notification = ['message' => 'File database sumber tidak ditemukan di: ' . $sourcePath, 'alert-type' => 'error'];
            return redirect()->back()->with($notification);
        }

        $fileName        = 'backup-' . now()->format('Y-m-d_H-i-s') . '.sqlite';
        $destinationPath = 'backups/' . $fileName;

        FacadesStorage::put($destinationPath, file_get_contents($sourcePath));

        DatabaseBackup::create([
            'file_name' => $fileName,
            'file_path' => $destinationPath,
            'file_size' => FacadesStorage::size($destinationPath),
        ]);

        $notification = ['message' => 'Backup database berhasil dibuat!', 'alert-type' => 'success'];
        return redirect()->back()->with($notification);
    }

    public function backupDownload($id)
    {
        try {
            $backup = DatabaseBackup::findOrFail($id);

            if (! FacadesStorage::exists($backup->file_path)) {
                $notification = ['message' => 'File backup tidak ditemukan di storage!', 'alert-type' => 'error'];
                return redirect()->back()->with($notification);
            }

            return FacadesStorage::download($backup->file_path, $backup->file_name);

        } catch (\Exception $e) {
            $notification = ['message' => 'Terjadi kesalahan: ' . $e->getMessage(), 'alert-type' => 'error'];
            return redirect()->back()->with($notification);
        }
    }

    // Tambahkan route baru untuk download langsung file backup
    public function directDownload($id)
    {
        $backup = DatabaseBackup::findOrFail($id);

        if (! FacadesStorage::exists($backup->file_path)) {
            abort(404, 'File backup tidak ditemukan!');
        }

        return FacadesStorage::download($backup->file_path, $backup->file_name);
    }

    public function backupDestroy($id)
    {
        try {
            $backup = DatabaseBackup::findOrFail($id);

            // Hapus file fisik
            FacadesStorage::delete($backup->file_path);

            // Hapus catatan dari database
            $backup->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'File backup berhasil dihapus!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menghapus backup: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function karyawanIndex()
    {
        $karyawans = Karyawan::get();
        return view('admin.karyawans.index', compact('karyawans'));
    }

    public function karyawanStore(Request $request)
    {
        $validated = $request->validate([
            'no_karyawan'    => 'required|string|unique:karyawans,no_karyawan,' . $request->id,
            'nama'           => 'required|string',
            'department'     => 'nullable|string',
            'position'       => 'nullable|string',
            'alamat'         => 'nullable|string',
            'no_hp'          => 'nullable|string|max:20',
            'kontak_darurat' => 'nullable|string|max:100',
        ]);

        try {
            if ($request->filled('id')) {
                // Update
                $karyawan = Karyawan::findOrFail($request->id);
                $karyawan->update($validated);
                $message = 'Data karyawan berhasil diperbarui.';
            } else {
                // Create
                $karyawan = Karyawan::create($validated);
                $message  = 'Data karyawan berhasil ditambahkan.';
            }

            return response()->json([
                'status'  => 'success',
                'message' => $message,
                'data'    => $karyawan,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function karyawanDestroy($id)
    {
        try {
            $karyawan = Karyawan::findOrFail($id);
            $karyawan->delete();

            return response()->json([
                'status'  => 'success',
                'message' => 'Data karyawan berhasil dihapus.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menghapus data karyawan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function ipWhitelistIndex()
    {
        $ips = AllowedIp::latest()->paginate(20);
        return view('admin.allowed-ips.index', compact('ips'));
    }

    public function ipWhitelistStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ip' => [
                'required',
                'ip',
                'unique:allowed_ips,ip,' . $request->input('id') 
            ],
            'label' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        AllowedIp::updateOrCreate(
            ['id' => $request->input('id')], // Kunci untuk mencari
            [
                'ip' => $request->input('ip'),         // Data untuk diisi
                'label' => $request->input('label')
            ]
        );

        Cache::forget('allowed_ips');

        return response()->json(['success' => 'IP address saved successfully.']);
    }

    public function ipWhitelistShow(AllowedIp $ip)
    {
        return response()->json($ip);
    }

    public function ipWhitelistDestroy(AllowedIp $ip)
    {
        if (AllowedIp::count() === 1) {
            return response()->json(['error' => 'Cannot delete the last IP address.'], 400);
        }

        $ip->delete();
        Cache::forget('allowed_ips'); // Hapus cache

        return response()->json(['success' => 'IP address deleted successfully.']);
    }
}
