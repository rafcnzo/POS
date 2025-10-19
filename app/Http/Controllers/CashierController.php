<?php
namespace App\Http\Controllers;

use App\Events\PrintReceipt;
use App\Models\Ingredient;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Modifier;
use App\Models\Sale;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CashierController extends Controller
{
    public function index()
    {
        // Ambil seluruh menu item beserta ingredients-nya
        $menuItems = MenuItem::with('ingredients', 'modifierGroups.modifiers')->get();

        // Hitung stok yang dapat dijual untuk setiap menu item berdasarkan stok ingredients
        $menuItemsWithStock = $menuItems->map(function ($menuItem) {
            if ($menuItem->ingredients->isEmpty()) {
                $menuItem->calculated_stock = 0;
                return $menuItem;
            }

            $possiblePortions = [];

            foreach ($menuItem->ingredients as $ingredient) {
                $quantityNeeded = $ingredient->pivot->quantity;
                $stockAvailable = $ingredient->stock;

                if ($quantityNeeded > 0) {
                    $portions           = floor($stockAvailable / $quantityNeeded);
                    $possiblePortions[] = $portions;
                } else {
                    $possiblePortions[] = INF;
                }
            }

            $menuItem->calculated_stock = min($possiblePortions);

            return $menuItem;
        });

        // Sekarang ambil categories beserta menuItems-nya (yang sudah dihitung stoknya)
        $categories = MenuCategory::with(['menuItems' => function ($query) {
            $query->orderBy('name');
        }])->orderBy('name')->get();

        // Pasangkan calculated_stock ke products pada setiap kategori
        foreach ($categories as $category) {
            // Buat array baru dari menuItems pada kategori ini, dengan menambahkan calculated_stock dari $menuItemsWithStock
            $category->products = $category->menuItems->map(function ($item) use ($menuItemsWithStock) {
                $stockInfo = $menuItemsWithStock->firstWhere('id', $item->id);
                if ($stockInfo) {
                    $item->calculated_stock = $stockInfo->calculated_stock;
                } else {
                    $item->calculated_stock = 0;
                }
                return $item;
            });
        }

        // Kembalikan SEMUA data variabel ke view
        return view('cashier.index', [
            'categories'         => $categories,
            'menuItems'          => $menuItems,
            'menuItemsWithStock' => $menuItemsWithStock,
        ]);
    }

    // public function Submit(Request $request)
    // {
    //     // Ambil data keranjang dan total dari session
    //     $cartData = session('pos_cart');
    //     $total    = session('pos_total');

    //     // Validasi data customer dari form pembayaran
    //     $customerData = $request->validate([
    //         'customer_name' => 'nullable|string|max:255',
    //         'table_number'  => 'nullable|string|max:50',
    //         'order_type'    => 'required|in:dine_in,take_away',
    //         'notes'         => 'nullable|string',
    //     ]);

    //     // Gabungkan data keranjang dan data customer untuk full transaksi
    //     $fullTransactionData = array_merge($customerData, [
    //         'items'    => $cartData,
    //         'payments' => [
    //             [
    //                 'method'        => $request->payment_method,
    //                 'amount'        => $total,
    //                 'cash_received' => $request->cash_received,
    //                 'change_amount' => ($request->cash_received - $total) >= 0 ? ($request->cash_received - $total) : 0,
    //             ],
    //         ],
    //     ]);

    //     try {
    //         $taxPercentage = Setting::where('key', 'tax')->value('value') ?? 0;

    //         $sale = DB::transaction(function () use ($fullTransactionData, $taxPercentage) {

    //             $subtotal = 0;
    //             foreach ($fullTransactionData['items'] as $itemData) {
    //                 $menuItem       = MenuItem::find($itemData['menu_item_id']);
    //                 $modifiersPrice = Modifier::whereIn('id', $itemData['modifiers'] ?? [])->sum('price');
    //                 $subtotal += ($menuItem->price + $modifiersPrice) * $itemData['quantity'];
    //             }

    //             $taxAmount   = $subtotal * ($taxPercentage / 100);
    //             $totalAmount = $subtotal + $taxAmount;

    //             $date            = Carbon::now()->format('Ymd');
    //             $latestSaleToday = Sale::where('transaction_code', 'like', "TRX-{$date}-%")->latest('id')->first();
    //             $nextNumber      = $latestSaleToday ? intval(substr($latestSaleToday->transaction_code, -3)) + 1 : 1;
    //             $transactionCode = "TRX-{$date}-" . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

    //             $sale = Sale::create([
    //                 'transaction_code' => $transactionCode,
    //                 'user_id'          => Auth::id(),
    //                 'customer_name'    => $fullTransactionData['customer_name'] ?? null,
    //                 'table_number'     => $fullTransactionData['table_number'] ?? null,
    //                 'order_type'       => $fullTransactionData['order_type'],
    //                 'subtotal'         => $subtotal,
    //                 'tax_amount'       => $taxAmount,
    //                 'total_amount'     => $totalAmount,
    //                 'notes'            => $fullTransactionData['notes'] ?? null,
    //             ]);

    //             foreach ($fullTransactionData['items'] as $itemData) {
    //                 $menuItem          = MenuItem::with('ingredients')->find($itemData['menu_item_id']);
    //                 $selectedModifiers = Modifier::with('ingredient')->find($itemData['modifiers'] ?? []);
    //                 $modifiersPrice    = $selectedModifiers->sum('price');

    //                 $saleItem = $sale->items()->create([
    //                     'menu_item_id' => $menuItem->id,
    //                     'quantity'     => $itemData['quantity'],
    //                     'price'        => $menuItem->price + $modifiersPrice,
    //                     'subtotal'     => ($menuItem->price + $modifiersPrice) * $itemData['quantity'],
    //                     'notes'        => $itemData['notes'] ?? null,
    //                 ]);

    //                 foreach ($selectedModifiers as $modifier) {
    //                     $saleItem->selectedModifiers()->create([
    //                         'modifier_id' => $modifier->id,
    //                         'price'       => $modifier->price,
    //                     ]);
    //                 }

    //                 foreach ($menuItem->ingredients as $ingredient) {
    //                     $totalToDecrement = $ingredient->pivot->quantity * $itemData['quantity'];
    //                     $ingredient->decrement('stock', $totalToDecrement);
    //                 }

    //                 foreach ($selectedModifiers as $modifier) {
    //                     if ($modifier->ingredient) {
    //                         $totalToDecrement = $modifier->quantity_used * $itemData['quantity'];
    //                         $modifier->ingredient->decrement('stock', $totalToDecrement);
    //                     }
    //                 }
    //             }

    //             foreach ($fullTransactionData['payments'] as $paymentData) {
    //                 $sale->payments()->create([
    //                     'payment_method' => $paymentData['method'],
    //                     'amount'         => $paymentData['amount'],
    //                     'cash_received'  => $paymentData['cash_received'] ?? null,
    //                     'change_amount'  => $paymentData['change_amount'] ?? 0,
    //                 ]);
    //             }

    //             return $sale;
    //         });

    //         // Clear session setelah transaksi selesai
    //         session()->forget(['pos_cart', 'pos_total']);

    //         return response()->json([
    //             'status'  => 'success',
    //             'message' => 'Transaksi berhasil disimpan!',
    //             'sale_id' => $sale->id,
    //         ]);

    //     } catch (\Throwable $e) {
    //         return response()->json([
    //             'status'  => 'error',
    //             'message' => 'Terjadi kesalahan internal: ' . $e->getMessage(),
    //         ], 500);
    //     }
    // }

    public function startTransaction(Request $request)
    {
        $cartData = json_decode($request->cart_data, true);

        if (empty($cartData)) {
            return back()->with('error', 'Keranjang tidak boleh kosong.');
        }

        try {
            // Logika ini mirip dengan 'store', tapi hanya untuk membuat record awal
            $sale = DB::transaction(function () use ($cartData) {
                // Kalkulasi total
                $subtotal = 0;
                foreach ($cartData as $itemData) {
                    $menuItem       = MenuItem::find($itemData['menu_item_id']);
                    $modifiersPrice = Modifier::whereIn('id', $itemData['modifier_ids'] ?? [])->sum('price');
                    $subtotal += ($menuItem->price + $modifiersPrice) * $itemData['quantity'];
                }

                // Buat record Sale dengan status PENDING
                $sale = Sale::create([
                    'transaction_code' => "TEMP-" . uniqid(), // Kode sementara
                    'user_id'          => Auth::id(),
                    'subtotal'         => $subtotal,
                    'total_amount'     => $subtotal, // Asumsi belum ada pajak
                    'status'           => 'pending', // <-- STATUS PENTING
                ]);

                // Simpan item & kurangi stok
                foreach ($cartData as $itemData) {
                    $menuItem          = MenuItem::with('ingredients')->find($itemData['menu_item_id']);
                    $selectedModifiers = Modifier::with('ingredient')->find($itemData['modifier_ids'] ?? []);
                    $modifiersPrice    = $selectedModifiers->sum('price');

                    $saleItem = $sale->items()->create([
                        'menu_item_id' => $menuItem->id,
                        'quantity'     => $itemData['quantity'],
                        'price'        => $menuItem->price + $modifiersPrice,
                        'subtotal'     => ($menuItem->price + $modifiersPrice) * $itemData['quantity'],
                        'notes'        => $itemData['notes'] ?? null,
                    ]);

                    foreach ($selectedModifiers as $modifier) {
                        $saleItem->selectedModifiers()->create([
                            'modifier_id' => $modifier->id,
                            'price'       => $modifier->price,
                        ]);
                    }

                    foreach ($menuItem->ingredients as $ingredient) {
                        $totalToDecrement = $ingredient->pivot->quantity * $itemData['quantity'];
                        $ingredient->decrement('stock', $totalToDecrement);
                    }

                    foreach ($selectedModifiers as $modifier) {
                        if ($modifier->ingredient) {
                            $totalToDecrement = $modifier->quantity_used * $itemData['quantity'];
                            $modifier->ingredient->decrement('stock', $totalToDecrement);
                        }
                    }
                }

                return $sale;
            });

            // Redirect ke halaman pembayaran dengan ID sale yang baru dibuat
            return redirect()->route('cashier.payment.page', ['sale' => $sale->id]);

        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal memulai transaksi: ' . $e->getMessage());
        }
    }

    public function processPayment(Request $request, Sale $sale)
    {
        if ($sale->status !== 'pending') {
            return response()->json(['status' => 'error', 'message' => 'Transaksi sudah tidak valid.'], 422);
        }

        $validated = $request->validate([
            'customer_name'  => 'required|string|max:150',
            'order_type'     => 'required|string|in:dine_in,take_away',
            'payment_method' => 'required|string',
            'table_number'   => 'nullable|string|max:50',
            'notes'          => 'nullable|string|max:500',
            'cash_received'  => 'nullable|numeric',
            'discount_type'  => 'nullable|in:fixed,percentage',
            'discount_value' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($request, $sale) {
                $subtotal       = $sale->subtotal;
                $discountType   = $request->discount_type;
                $discountValue  = $request->discount_value ?? 0;
                $discountAmount = 0; // Ini yang akan disimpan

                if ($discountType === 'percentage') {
                    $discountAmount = $subtotal * ($discountValue / 100);
                } elseif ($discountType === 'fixed') {
                    $discountAmount = $discountValue;
                }
                $discountAmount = min($subtotal, $discountAmount);

                $totalAfterDiscount = $subtotal - $discountAmount;

                $taxPercentage   = (float) (Setting::where('key', 'tax')->value('value') ?? 0);
                $taxAmount       = $totalAfterDiscount * ($taxPercentage / 100);
                $totalAmount     = $totalAfterDiscount + $taxAmount;
                $date            = Carbon::now()->format('Ymd');
                $latestSaleToday = Sale::where('transaction_code', 'like', "TRX-{$date}-%")->latest('id')->first();
                $nextNumber      = $latestSaleToday ? intval(substr($latestSaleToday->transaction_code, -3)) + 1 : 1;
                $transactionCode = "TRX-{$date}-" . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

                $sale->update([
                    'transaction_code' => $transactionCode,
                    'customer_name'    => $request->customer_name,
                    'table_number'     => $request->table_number,
                    'order_type'       => $request->order_type,
                    'notes'            => $request->notes,
                    'discount_amount'  => $discountAmount, // <-- Simpan nilai diskon final dalam Rupiah
                    'tax_amount'       => $taxAmount,
                    'total_amount'     => $totalAmount,
                    'status'           => 'completed',
                ]);

                $changeAmount = 0;
                if ($request->payment_method === 'cash') {
                    $cashReceived = (float) ($request->cash_received ?? 0);
                    $change       = $cashReceived - $totalAmount;
                    $changeAmount = $change >= 0 ? $change : 0;
                }

                $sale->payments()->create([
                    'payment_method' => $request->payment_method,
                    'amount'         => $totalAmount,
                    'cash_received'  => $request->cash_received,
                    'change_amount'  => $changeAmount,
                ]);
            });

            return response()->json(['status' => 'success', 'message' => 'Transaksi berhasil!', 'sale_id' => $sale->id]);

        } catch (Throwable $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    public function voidTransaction(Sale $sale)
    {
        if ($sale->status !== 'pending') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Hanya transaksi yang tertunda yang bisa dibatalkan.',
            ], 422);
        }

        try {
            DB::transaction(function () use ($sale) {
                foreach ($sale->items as $item) {
                    foreach ($item->menuItem->ingredients as $ingredient) {
                        $totalToIncrement = $ingredient->pivot->quantity * $item->quantity;
                        $ingredient->increment('stock', $totalToIncrement);
                    }
                    foreach ($item->selectedModifiers as $saleModifier) {
                        if ($saleModifier->modifier && $saleModifier->modifier->ingredient) {
                            $totalToIncrement = $saleModifier->modifier->quantity_used * $item->quantity;
                            $saleModifier->modifier->ingredient->increment('stock', $totalToIncrement);
                        }
                    }
                }

                $sale->update(['status' => 'void']);
            });

            return response()->json([
                'status'  => 'success',
                'message' => 'Transaksi berhasil dibatalkan dan stok telah dikembalikan.',
            ]);

        } catch (Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal membatalkan transaksi: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function printCustomerReceipt($id)
    {
        $sale = Sale::with([
            'user',
            'items.menuItem',
            'items.selectedModifiers.modifier',
            'payments',
        ])->findOrFail($id);

        $settings    = Setting::pluck('value', 'key')->toArray();
        $printerName = $settings['printer_kasir'] ?? null;

        try {
            $html = view('cashier._print_cust', compact('sale', 'settings'))->render();

            $isVirtualPrinter = in_array($printerName, ['Nitro PDF Creator', 'Microsoft Print to PDF']);

            if ($printerName && ! $isVirtualPrinter) {
                Printing::printer($printerName)->html($html)->send();
                return response()->json(['success' => true, 'message' => "Struk dikirim ke printer: {$printerName}"]);

            } else {
                return view('cashier._print_cust', compact('sale', 'settings'));
            }

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mencetak: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Print kitchen receipt (struk dapur).
     */
    public function printKitchenReceipt($id)
    {
        $sale = Sale::with([
            'user',
            'items.menuItem',
            'items.selectedModifiers.modifier',
            'payments',
        ])->findOrFail($id);

        $settings    = Setting::pluck('value', 'key')->toArray();
        $printerName = $settings['printer_dapur'] ?? null;

        try {
            $html = view('cashier._print_kitchen', compact('sale', 'settings'))->render();

            $isVirtualPrinter = in_array($printerName, ['Nitro PDF Creator', 'Microsoft Print to PDF']);

            if ($printerName && ! $isVirtualPrinter) {
                PrintReceipt::dispatch($html, $printerName);

                return response()->json([
                    'success' => true,
                    'message' => "Struk dapur sedang dicetak ke printer: {$printerName}",
                ]);
            } else {
                return view('cashier._print_kitchen', compact('sale', 'settings'));
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencetak: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get list of available printers (untuk settings).
     */
    public function getPrinters()
    {
        try {
            $printers = \Native\Laravel\Facades\System::printers();

            $printerList = collect($printers)->map(function ($printer) {
                return [
                    'name'        => $printer->name,
                    'displayName' => $printer->displayName,
                    'status'      => $printer->status ?? 'unknown',
                    'isDefault'   => $printer->isDefault ?? false,
                ];
            });

            return response()->json([
                'success'  => true,
                'printers' => $printerList,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function showPaymentPage(Sale $sale)
    {
        if ($sale->status !== 'pending') {
            return redirect()->route('cashier.index')->with('error', 'Transaksi ini sudah tidak valid.');
        }

        $taxPercentage = (float) (Setting::where('key', 'tax')->value('value') ?? 0);
        $taxAmount     = $sale->subtotal * ($taxPercentage / 100);

        return view('cashier.payment', [
            'sale'          => $sale,
            'taxPercentage' => $taxPercentage,
            'taxAmount'     => $taxAmount,
        ]);
    }

    public function historyIndex(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'search'     => 'nullable|string',
        ]);

        $salesQuery = Sale::with('user')
            ->where('status', 'completed')
            ->latest();

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate   = Carbon::parse($request->end_date)->endOfDay();
            $salesQuery->whereBetween('created_at', [$startDate, $endDate]);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);

            $salesQuery->where(function ($query) use ($search) {
                $query->where(function ($sub) use ($search) {
                    if (mb_strlen($search) >= 3) {
                        $sub->where('transaction_code', '=', $search)
                            ->orWhere('transaction_code', 'like', $search . '%');
                    } else {
                        $sub->where('transaction_code', 'like', '%' . $search . '%');
                    }
                })
                // Pencarian Nama Kasir
                ->orWhereHas('user', function ($q) use ($search) {
                    if (mb_strlen($search) >= 3) {
                        $q->where('name', '=', $search)
                          ->orWhere('name', 'like', $search . '%');
                    } else {
                        $q->where('name', 'like', '%' . $search . '%');
                    }
                });
            });
        
        }

        $sales = $salesQuery->paginate(10);
        return view('cashier.history.index', compact('sales'));
    }

    public function historyShow(Sale $sale)
    {
        $sale->load([
            'user:id,name',                             
            'items.menuItem:id,name',                   
            'items.selectedModifiers.modifier:id,name', 
            'payments',
        ]);

        if (! $sale) {
            return response()->json(['status' => 'error', 'message' => 'Transaksi tidak ditemukan.'], 404);
        }
        return response()->json([
            'status' => 'success',
            'sale'   => $sale,
        ]);
    }
}
