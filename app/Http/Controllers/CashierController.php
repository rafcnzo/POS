<?php
namespace App\Http\Controllers;

use App\Events\PrintReceipt;
use App\Models\Ingredient;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Modifier;
use App\Models\Reservation;
use App\Models\Sale;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Throwable;

class CashierController extends Controller
{
    public function index()
    {
        // Ambil seluruh menu item beserta ingredients-nya
        $menuItems             = MenuItem::with('ingredients', 'modifierGroups.modifiers')->get();
        $authorizationPassword = Setting::where('key', 'authorization_password')->value('value');

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
            'categories'            => $categories,
            'menuItems'             => $menuItems,
            'menuItemsWithStock'    => $menuItemsWithStock,
            'authorizationPassword' => $authorizationPassword,
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

                                                                                    // --- 1. DEFINISIKAN $transactionType DI SINI (SEBELUM DB::transaction) ---
        $transactionType = $request->input('transaction_type', Sale::TYPE_REGULAR); // Default 'regular'

        if (! in_array($transactionType, [Sale::TYPE_REGULAR, Sale::TYPE_EMPLOYEE, Sale::TYPE_COMPLIMENTARY])) {
            return back()->with('error', 'Tipe transaksi tidak valid.');
        }

        if (empty($cartData)) {
            return back()->with('error', 'Keranjang tidak boleh kosong.');
        }

        try {
            $sale = DB::transaction(function () use ($cartData, $transactionType, $request) {
                $subtotal = 0;
                foreach ($cartData as $itemData) {
                    $menuItem = MenuItem::find($itemData['menu_item_id']);
                    if (! $menuItem) {
                        throw new \Exception("Menu item ID {$itemData['menu_item_id']} tidak ditemukan.");
                    }

                    $modifiersPrice = Modifier::whereIn('id', $itemData['modifier_ids'] ?? [])->sum('price');
                    $subtotal += ($menuItem->price + $modifiersPrice) * $itemData['quantity'];
                }

                $totalAmountForSale = ($transactionType === Sale::TYPE_COMPLIMENTARY) ? 0 : $subtotal;

                $sale = Sale::create([
                    'transaction_code' => "TEMP-" . uniqid(),
                    'user_id'          => Auth::id(),
                    'type'             => $transactionType,
                    'subtotal'         => $subtotal,
                    'total_amount'     => $totalAmountForSale,
                    'status'           => ($transactionType === Sale::TYPE_COMPLIMENTARY) ? 'completed' : 'pending',
                ]);

                foreach ($cartData as $itemData) {
                    $menuItem = MenuItem::with('ingredients')->find($itemData['menu_item_id']);
                    if (! $menuItem) {
                        continue;
                    }

                    $selectedModifiers = Modifier::with('ingredient')->find($itemData['modifier_ids'] ?? []);
                    $modifiersPrice    = $selectedModifiers->sum('price');
                    $saleItem          = $sale->items()->create([
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
                            $totalToDecrement = ($modifier->quantity_used ?? 1) * $itemData['quantity'];
                            $modifier->ingredient->decrement('stock', $totalToDecrement);
                        }
                    }
                }

                if ($transactionType === Sale::TYPE_COMPLIMENTARY) {
                    $date            = now()->format('Ymd');
                    $latestSaleToday = Sale::where('transaction_code', 'like', "TRX-{$date}-%")->latest('id')->first();
                    $nextNumber      = $latestSaleToday ? intval(substr($latestSaleToday->transaction_code, -3)) + 1 : 1;
                    $transactionCode = "TRX-{$date}-" . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

                    $sale->update([
                        'transaction_code' => $transactionCode,
                        'customer_name'    => $request->input('customer_name', 'Complimentary'),
                        'order_type'       => $request->input('order_type', 'dine_in'),
                        'discount_amount'  => $subtotal,
                        'tax_amount'       => 0,
                        'total_amount'     => 0,
                    ]);
                }

                return $sale;
            });

            if ($sale->type === Sale::TYPE_COMPLIMENTARY) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'status'  => 'success',
                        'message' => "Transaksi Complimentary #{$sale->transaction_code} berhasil disimpan.",
                        'is_complimentary' => true, // <-- Flag untuk JS
                        'sale_id'          => $sale->id,
                        'transaction_code' => $sale->transaction_code,
                        'redirect_url'     => route('cashier.index'), // <-- URL untuk redirect JS
                    ]);
                } else {
                    return redirect()->route('cashier.index')
                        ->with('success', "Transaksi Complimentary #{$sale->transaction_code} berhasil disimpan.");
                }

            } else {
                return redirect()->route('cashier.payment.page', ['sale' => $sale->id]);
            }

        } catch (Throwable $e) {
            \Log::error("Error startTransaction: " . $e->getMessage() . "\n" . $e->getTraceAsString());
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
            'payment_method' => 'required|string', // Metode bayar customer (jika perlu)
            'table_number'   => 'nullable|string|max:50',
            'notes'          => 'nullable|string|max:500',
            'cash_received'  => 'nullable|numeric|required_if:payment_method,cash', // Wajib jika cash & ada sisa bayar
            'discount_type'  => 'nullable|in:fixed,percentage',
            'discount_value' => 'nullable|numeric|min:0',
            'reservation_id' => 'nullable|exists:reservations,id',
        ]);

        try {
            $result = DB::transaction(function () use ($request, $sale) {

                $transactionType = $sale->type; // 'regular', 'employee_meal', atau 'complimentary'

                $reservation   = null;
                $depositAmount = 0;
                if ($transactionType !== Sale::TYPE_COMPLIMENTARY && $request->filled('reservation_id')) {
                    $reservation = Reservation::find($request->reservation_id);
                    if (! $reservation || ! in_array($reservation->status, ['confirmed', 'seated']) || $reservation->sale_id !== null) {
                        throw new \Exception('Reservasi tidak valid atau sudah digunakan.');
                    }
                    $depositAmount = $reservation->deposit_amount;
                }

                $subtotal         = $sale->subtotal; // Subtotal asli dari item
                $discountAmount   = 0;
                $taxAmount        = 0;
                $finalTotalAmount = 0; // Total akhir yang HARUS dibayar

                if ($transactionType === Sale::TYPE_COMPLIMENTARY) {
                                                   // Jika Complimentary: Diskon 100%, Pajak 0, Total Akhir 0
                    $discountAmount   = $subtotal; // Diskon 100%
                    $taxAmount        = 0;
                    $finalTotalAmount = 0; // <-- Total HARUS 0
                } else {
                    $discountType  = $request->discount_type;
                    $discountValue = $request->discount_value ?? 0;
                    if ($discountType === 'percentage') {
                        $discountAmount = $subtotal * ($discountValue / 100);
                    } elseif ($discountType === 'fixed') {
                        $discountAmount = $discountValue;
                    }
                    $discountAmount = min($subtotal, $discountAmount); // Validasi diskon

                    $totalAfterDiscount = $subtotal - $discountAmount;
                    $taxPercentage      = (float) (Setting::where('key', 'tax')->value('value') ?? 0);
                    $taxAmount          = $totalAfterDiscount * ($taxPercentage / 100);

                    $finalTotalAmount = $totalAfterDiscount + $taxAmount;
                }

                $date            = now()->format('Ymd');
                $latestSaleToday = Sale::where('transaction_code', 'like', "TRX-{$date}-%")->latest('id')->first();
                $nextNumber      = $latestSaleToday ? intval(substr($latestSaleToday->transaction_code, -3)) + 1 : 1;
                $transactionCode = "TRX-{$date}-" . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

                $sale->update([
                    'transaction_code' => $transactionCode,
                    'customer_name'    => $request->customer_name,
                    'table_number'     => $request->table_number,
                    'order_type'       => $request->order_type,
                    'notes'            => $request->notes,
                    'discount_amount'  => $discountAmount,   // Akan 0 jika complimentary (atau subtotal jika 100%)
                    'tax_amount'       => $taxAmount,        // Akan 0 jika complimentary
                    'total_amount'     => $finalTotalAmount, // Akan 0 jika complimentary
                    'status'           => 'completed',       // Anggap selesai
                ]);

                if ($transactionType !== Sale::TYPE_COMPLIMENTARY && $reservation && $depositAmount > 0) {
                    $sale->payments()->create([
                        'payment_method' => 'Deposit Reservasi',
                        'amount'         => $depositAmount,
                        'payment_date'   => now(),
                        'reservation_id' => $reservation->id,
                    ]);
                }

                $amountDue             = $finalTotalAmount - $depositAmount;
                $amountDue             = max(0, $amountDue); // Pastikan tidak negatif
                $changeAmount          = 0;                  // Default kembalian 0
                $customerPaymentAmount = 0;                  // Berapa yg dibayar customer
                $cashReceived          = null;

                if ($amountDue > 0 && $transactionType !== Sale::TYPE_COMPLIMENTARY) {

                    if ($request->payment_method === 'cash') {
                        $cashReceived = (float) ($request->cash_received ?? 0);
                        if ($cashReceived < $amountDue) {
                            throw new \Exception('Jumlah uang tunai yang diterima kurang dari sisa tagihan.');
                        }
                        $customerPaymentAmount = $amountDue; // Catat sebesar sisa tagihan
                        $changeAmount          = $cashReceived - $amountDue;
                    } else {
                        $customerPaymentAmount = $amountDue; // Anggap dibayar pas
                    }

                    // Catat Payment Customer
                    $sale->payments()->create([
                        'payment_method' => $request->payment_method,
                        'amount'         => $customerPaymentAmount,
                        'cash_received'  => $cashReceived,
                        'change_amount'  => $changeAmount,
                        'payment_date'   => now(),
                    ]);
                } else {
                    if ($sale->status !== 'completed') { // Double check
                        $sale->status = 'completed';
                        $sale->save();
                    }
                }

                if ($reservation) {
                    $reservation->update([
                        'status'  => 'completed',
                        'sale_id' => $sale->id,
                    ]);
                }

                return [
                    'sale_id'       => $sale->id,
                    'change_amount' => $changeAmount, // Kembalian (akan 0 jika non-cash/complimentary)
                ];

            }); // Akhir DB::transaction

            return response()->json([
                'status'        => 'success',
                'message'       => 'Transaksi berhasil!',
                'sale_id'       => $result['sale_id'],
                'change_amount' => $result['change_amount'],
            ]);

        } catch (Throwable $e) { // Lebih baik catch Throwable
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

    public function smartPrintAfterPayment($id)
    {
        try {
            $sale = Sale::with([
                'user',
                'items.menuItem.menuCategory',
                'items.selectedModifiers.modifier',
            ])->findOrFail($id);

            $settings           = Setting::pluck('value', 'key')->toArray();
            $kitchenPrinterName = $settings['printer_dapur'] ?? null;
            $barPrinterName     = $settings['printer_bar'] ?? null;

            // Validasi: Minimal satu printer harus dikonfigurasi
            if (empty($kitchenPrinterName) && empty($barPrinterName)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Belum ada printer yang dikonfigurasi di pengaturan.',
                ], 400);
            }

            // Ambil daftar printer yang tersedia
            $allPrinters = \Native\Laravel\Facades\System::printers();

            if (empty($allPrinters)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada printer yang terdeteksi di sistem.',
                ], 400);
            }

            // Pisahkan items berdasarkan kategori
            $kitchenItems = collect();
            $barItems     = collect();

            foreach ($sale->items as $item) {
                if ($item->menuItem && $item->menuItem->menuCategory &&
                    strtolower($item->menuItem->menuCategory->name) === 'minuman') {
                    $barItems->push($item);
                } else {
                    $kitchenItems->push($item);
                }
            }

            // Validasi: Harus ada item untuk dicetak
            if ($kitchenItems->isEmpty() && $barItems->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada item untuk dicetak.',
                ], 400);
            }

            $successPrinters = [];
            $failedPrinters  = [];

            // ============================================
            // CETAK KE PRINTER DAPUR (item non-minuman)
            // ============================================
            if ($kitchenItems->isNotEmpty() && ! empty($kitchenPrinterName)) {
                try {
                    // Render HTML untuk kitchen
                    $kitchenHtml = view('cashier._print_kitchen', [
                        'sale'         => $sale,
                        'itemsToPrint' => $kitchenItems,
                        'settings'     => $settings,
                    ])->render();

                    // Debug: Log HTML length untuk memastikan ada content
                    Log::info("Kitchen HTML length: " . strlen($kitchenHtml) . " chars");
                    Log::info("Kitchen items count: " . $kitchenItems->count());

                    // Cari printer berdasarkan name atau displayName
                    $printer = collect($allPrinters)->first(function ($p) use ($kitchenPrinterName) {
                        return $p->name === $kitchenPrinterName ||
                        $p->displayName === $kitchenPrinterName;
                    });

                    if (! $printer) {
                        $failedPrinters[] = "Dapur ({$kitchenPrinterName}) - Printer tidak ditemukan";
                        Log::warning("Kitchen printer not found: {$kitchenPrinterName}");
                    } else {
                        // Print langsung
                        \Native\Laravel\Facades\System::print($kitchenHtml, $printer, [
                            'silent'   => false, // false = muncul dialog untuk virtual printer
                            'pageSize' => [
                                'width'  => 80000,  // 80mm
                                'height' => 297000, // Auto height
                            ],
                            'margins'  => [
                                'top'    => 0,
                                'bottom' => 0,
                                'left'   => 0,
                                'right'  => 0,
                            ],
                        ]);

                        $successPrinters[] = "Dapur ({$kitchenPrinterName})";
                        Log::info("Kitchen print success: {$kitchenPrinterName}");
                    }
                } catch (\Exception $e) {
                    $failedPrinters[] = "Dapur ({$kitchenPrinterName}) - {$e->getMessage()}";
                    Log::error("Kitchen print failed: " . $e->getMessage());
                }
            }

            // ============================================
            // CETAK KE PRINTER BAR (item minuman)
            // ============================================
            if ($barItems->isNotEmpty() && ! empty($barPrinterName)) {
                try {
                    // Gunakan view khusus bar jika ada, fallback ke kitchen
                    $barViewName = \View::exists('cashier._print_bar')
                        ? 'cashier._print_bar'
                        : 'cashier._print_kitchen';

                    $barHtml = view($barViewName, [
                        'sale'         => $sale,
                        'itemsToPrint' => $barItems,
                        'settings'     => $settings,
                    ])->render();

                    // Debug: Log HTML length untuk memastikan ada content
                    Log::info("Bar HTML length: " . strlen($barHtml) . " chars");
                    Log::info("Bar items count: " . $barItems->count());

                    // Cari printer berdasarkan name atau displayName
                    $printer = collect($allPrinters)->first(function ($p) use ($barPrinterName) {
                        return $p->name === $barPrinterName ||
                        $p->displayName === $barPrinterName;
                    });

                    if (! $printer) {
                        $failedPrinters[] = "Bar ({$barPrinterName}) - Printer tidak ditemukan";
                        Log::warning("Bar printer not found: {$barPrinterName}");
                    } else {
                        // Print langsung
                        \Native\Laravel\Facades\System::print($barHtml, $printer, [
                            'silent'   => false,
                            'pageSize' => [
                                'width'  => 80000,
                                'height' => 297000,
                            ],
                            'margins'  => [
                                'top'    => 0,
                                'bottom' => 0,
                                'left'   => 0,
                                'right'  => 0,
                            ],
                        ]);

                        $successPrinters[] = "Bar ({$barPrinterName})";
                        Log::info("Bar print success: {$barPrinterName}");
                    }
                } catch (\Exception $e) {
                    $failedPrinters[] = "Bar ({$barPrinterName}) - {$e->getMessage()}";
                    Log::error("Bar print failed: " . $e->getMessage());
                }
            }

            // ============================================
            // RESPONSE BERDASARKAN HASIL
            // ============================================
            if (! empty($successPrinters)) {
                $message = 'Struk berhasil dicetak ke: ' . implode(' & ', $successPrinters);

                // Tambahkan warning jika ada yang gagal
                if (! empty($failedPrinters)) {
                    $message .= '. Gagal: ' . implode(', ', $failedPrinters);
                }

                return response()->json([
                    'success' => true,
                    'message' => $message,
                ]);
            }

            // Semua printer gagal
            return response()->json([
                'success' => false,
                'message' => 'Semua printer gagal. Detail: ' . implode(', ', $failedPrinters),
            ], 500);

        } catch (\Exception $e) {
            Log::error('smartPrintAfterPayment failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses pencetakan: ' . $e->getMessage(),
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

        $settings   = Setting::pluck('value', 'key')->toArray();
        $logoBase64 = null;
        $logoPath   = $settings['store_logo'] ?? null;
        if ($logoPath && \Storage::disk('public')->exists($logoPath)) {
            try {
                $imageContents = \Storage::disk('public')->get($logoPath);
                $mimeType      = \Storage::disk('public')->mimeType($logoPath);
                $logoBase64    = 'data:' . $mimeType . ';base64,' . base64_encode($imageContents);
            } catch (\Exception $e) {
                // Biarkan logoBase64 null jika gagal
            }
        }

        $printerName = $settings['printer_kasir'] ?? null;

        try {
            // Kirim logoBase64 ke view
            $html = view('cashier._print_cust', compact('sale', 'settings', 'logoBase64'))->render();
            // $html = '<html><body style="margin:20px;"><h1>TEST PRINT</h1><p>Ini adalah test print</p></body></html>';

            $isVirtualPrinter = in_array($printerName, ['Nitro PDF Creator', 'Microsoft Print to PDF']);

            if ($printerName && ! $isVirtualPrinter) {
                $allPrinters = \Native\Laravel\Facades\System::printers();
                $printerObj = collect($allPrinters)->first(function ($printer) use ($printerName) {
                    return $printer->name === $printerName;
                });

                if ($printerObj) {
                    \Native\Laravel\Facades\System::print($html, $printerObj, [
                        'silent'            => true,
                        'printBackground'   => true,
                        'preferCSSPageSize' => true,
                    ]);
                    return response()->json(['success' => true, 'message' => "Struk dikirim ke printer: {$printerName}"]);
                } else {
                    // Printer yang diset di settings tidak ditemukan di sistem
                    return response()->json(['success' => false, 'message' => "Printer '{$printerName}' tidak ditemukan di daftar printer sistem."], 404);
                }
            } else {
                // Fallback jika tidak ada printer atau jika itu virtual printer (tampilkan di view)
                return view('cashier._print_cust', compact('sale', 'settings', 'logoBase64'));
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
            'items.menuItem.menuCategory', // Ubah relasi ke menuCategory sesuai model MenuItem
            'items.selectedModifiers.modifier',
        ])->findOrFail($id);

        $settings           = Setting::pluck('value', 'key')->toArray();
        $kitchenPrinterName = $settings['printer_dapur'] ?? null;
        $barPrinterName     = $settings['printer_bar'] ?? null;

        $virtualPrinters = [
            'Nitro PDF Creator',
            'Microsoft Print to PDF',
            'Save as PDF',
        ];

        $isKitchenPrinterValid = $kitchenPrinterName && ! in_array($kitchenPrinterName, $virtualPrinters);
        $isBarPrinterValid     = $barPrinterName && ! in_array($barPrinterName, $virtualPrinters);

        // Pemisahan item: berdasarkan kategori dari relasi menuCategory
        $kitchenItems = collect();
        $barItems     = collect();

        foreach ($sale->items as $item) {
            // Pastikan properti menuCategory() sesuai dengan relasi di model MenuItem
            if ($item->menuItem && $item->menuItem->menuCategory && $item->menuItem->menuCategory->name === 'Minuman') {
                $barItems->push($item);
            } else {
                $kitchenItems->push($item);
            }
        }

        try {
            $dispatchedPrinters = [];

            // Jika salah satu printer tidak valid, tampilkan preview gabungan
            if (! $isKitchenPrinterValid || ! $isBarPrinterValid) {
                if (! View::exists('cashier._print_kitchen')) {
                    throw new \Exception("View 'cashier._print_kitchen' tidak ditemukan.");
                }
                return view('cashier._print_kitchen', [
                    'sale'         => $sale,
                    'itemsToPrint' => $sale->items,
                    'settings'     => $settings,
                ]);
            }

            // Jika kedua printer valid, proses split printing
            if ($kitchenItems->isNotEmpty()) {
                if (! View::exists('cashier._print_kitchen')) {
                    throw new \Exception("View 'cashier._print_kitchen' tidak ditemukan.");
                }
                $kitchenHtml = view('cashier._print_kitchen', [
                    'sale'         => $sale,
                    'itemsToPrint' => $kitchenItems,
                    'settings'     => $settings,
                ])->render();

                PrintReceipt::dispatch($kitchenHtml, $kitchenPrinterName);
                $dispatchedPrinters[] = "Dapur ({$kitchenPrinterName})";
            }

            if ($barItems->isNotEmpty()) {
                // Bisa menggunakan view yang sama, atau jika tersedia, gunakan _print_bar
                $barViewName = View::exists('cashier._print_bar')
                    ? 'cashier._print_bar'
                    : 'cashier._print_kitchen';

                if (! View::exists($barViewName)) {
                    throw new \Exception("View '{$barViewName}' (untuk bar) tidak ditemukan.");
                }

                $barHtml = view($barViewName, [
                    'sale'         => $sale,
                    'itemsToPrint' => $barItems,
                    'settings'     => $settings,
                ])->render();

                PrintReceipt::dispatch($barHtml, $barPrinterName);
                $dispatchedPrinters[] = "Bar ({$barPrinterName})";
            }

            if (empty($dispatchedPrinters)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tidak ada item yang perlu dicetak atau printer tidak valid.',
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'Struk sedang dikirim ke printer: ' . implode(' & ', $dispatchedPrinters),
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses pencetakan: ' . $e->getMessage(),
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

        $activeReservations = Reservation::whereIn('status', ['confirmed'])
            ->whereNull('sale_id')
            ->where('deposit_amount', '>', 0)
            ->orderBy('reservation_time', 'desc')
            ->get(['id', 'customer_name', 'table_number', 'deposit_amount']);

        return view('cashier.payment', [
            'sale'               => $sale,
            'taxPercentage'      => $taxPercentage,
            'taxAmount'          => $taxAmount,
            'activeReservations' => $activeReservations,
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

    public function storeReservation(Request $request)
    {
        $validated = $request->validate([
            'customer_name'    => 'required|string|max:255',
            'table_number'     => 'nullable|string|max:50',
            'pax'              => 'required|integer|min:1',
            'reservation_time' => 'required|date|after_or_equal:now',
            'deposit_amount'   => 'required|numeric|min:0',
            'contact_number'   => 'nullable|string|max:20',
            'notes'            => 'nullable|string|max:1000',
        ], [
            'reservation_time.after_or_equal' => 'Waktu reservasi tidak boleh di masa lalu.',
        ]);

        try {
            $validated['user_id'] = Auth::id();
            $validated['status']  = 'confirmed';

            $reservation = Reservation::create($validated);

            return response()->json([
                'status'  => 'success',
                'message' => 'Reservasi untuk ' . $validated['customer_name'] . ' berhasil disimpan.',
                'data'    => $reservation,
            ]);

        } catch (\Exception $e) {
            Log::error('Gagal menyimpan reservasi: ' . $e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan saat menyimpan reservasi. Silakan coba lagi.',
            ], 500);
        }
    }
}
