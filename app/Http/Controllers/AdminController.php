<?php
namespace App\Http\Controllers;

use App\Models\DatabaseBackup;
use App\Models\EnergyCost;
use App\Models\Ingredient;
use App\Models\MenuItem;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Setting;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage as FacadesStorage;
use Illuminate\Support\Str;
use Native\Laravel\Facades\Window;

class AdminController extends Controller
{
    public function index()
    {
        // Data jumlah bahan baku
        $totalIngredients = Ingredient::count();
        // Data PO menunggu
        $pendingPO = PurchaseOrder::where('status', 'pending')->count();
        // Total supplier
        $totalSuppliers = Supplier::count();

        // Hitung total penjualan bulan ini
        $currentMonth  = now()->month;
        $currentYear   = now()->year;
        $previousMonth = now()->subMonth()->month;
        $previousYear  = now()->subMonth()->year;

        $totalSales = Sale::whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->sum('total_amount');

        // Hitung total penjualan bulan lalu
        $totalSalesLastMonth = Sale::whereMonth('created_at', $previousMonth)
            ->whereYear('created_at', $previousYear)
            ->sum('total_amount');

        // Hitung pertumbuhan penjualan (sales growth) dalam persen
        if ($totalSalesLastMonth > 0) {
            $salesGrowth = number_format((($totalSales - $totalSalesLastMonth) / $totalSalesLastMonth) * 100, 1) . '%';
        } elseif ($totalSales > 0) {
            $salesGrowth = '100%';
        } else {
            $salesGrowth = '0%';
        }

        // Total transaksi bulan ini
        $totalTransactions = Sale::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Total transaksi bulan lalu
        $previousMonth              = now()->subMonth()->month;
        $previousYear               = now()->subMonth()->year;
        $totalTransactionsLastMonth = Sale::whereMonth('created_at', $previousMonth)
            ->whereYear('created_at', $previousYear)
            ->count();

        // Pertumbuhan transaksi (transactions growth) dalam persen
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
                    ->avg('total_amount'), 0
            );
        }

        // Cari 5 menu (MenuItem) terlaris berdasarkan total quantity SaleItem bulan ini
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

        $latestPurchaseOrders = PurchaseOrder::with(['supplier', 'items.ingredient'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(function ($po) {
                $items           = $po->items;
                $ingredientNames = $items->pluck('ingredient.name')->take(3)->toArray();
                $othersCount     = $items->count() - 3;
                $desc            = implode(', ', $ingredientNames);
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
                $critical = $ingredient->stock <= 0 || $ingredient->stock <= $ingredient->minimum_stock / 2;
                return [
                    'name'     => $ingredient->name,
                    'desc'     => $critical
                        ? 'Stok kritis - Order sekarang!'
                        : 'Stok menipis - Perlu restock',
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
                DB::raw("strftime('%H', created_at) as hour_of_day"),
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

        $salesChartLabels   = [];
        $dateKeys = [];
        for ($i = 6; $i >= 0; $i--) {
            $date       = now()->subDays($i);
            $salesChartLabels[]   = $date->translatedFormat('D');
            $dateKeys[] = $date->format('Y-m-d');
        }

        $salesData = Sale::query()
            ->select(
                DB::raw("strftime('%Y-%m-%d', created_at) as sale_date"),
                DB::raw("SUM(total_amount) as daily_total")
            )
            ->where('status', 'completed')
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
                    'store_logo' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                ]);

                // LOG PATH
                \Log::info('Storage paths:', [
                    'storage_path'        => storage_path('app/public/logos'),
                    'base_path'           => base_path('storage/app/public/logos'),
                    'public_root'         => config('filesystems.disks.public.root'),
                    'file_exists_storage' => file_exists(storage_path('app/public/logos')),
                    'file_exists_base'    => file_exists(base_path('storage/app/public/logos')),
                ]);

                $oldLogo = Setting::where('key', 'store_logo')->value('value');
                if ($oldLogo && FacadesStorage::disk('public')->exists($oldLogo)) {
                    FacadesStorage::disk('public')->delete($oldLogo);
                }

                // Simpan file
                $path = $request->file('store_logo')->store('logos', 'public');

                // LOG HASIL
                \Log::info('File stored:', [
                    'path_in_db'     => $path,
                    'full_path'      => FacadesStorage::disk('public')->path($path),
                    'file_exists'    => FacadesStorage::disk('public')->exists($path),
                    'physical_check' => file_exists(base_path('storage/app/public/' . $path)),
                ]);

                $setting        = Setting::where('key', 'store_logo')->firstOrNew(['key' => 'store_logo']);
                $setting->value = $path;
                $setting->save();
            }

            foreach ($dataToStore as $key => $value) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value ?? '']
                );
            }

            $newStoreName = Setting::where('key', 'store_name')->value('value');
            if ($newStoreName) {
                Window::get('main')->title($newStoreName);
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

}
