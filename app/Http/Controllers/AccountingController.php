<?php
namespace App\Http\Controllers;

use App\Exports\StockMovementExport;
use App\Models\EnergyCost;
use App\Models\Extra;
use App\Models\Ffne;
use App\Models\FfneStockAdj;
use App\Models\Ingredient;
use App\Models\IngredientStockAdjustment;
use App\Models\Karyawan;
use App\Models\Payroll;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Log;
use Throwable;

class AccountingController extends Controller
{
    public function suppliersIndex()
    {
        $suppliers = Supplier::latest()->get();
        return view('accounting.suppliers.index', compact('suppliers'));
    }

    public function suppliersSubmit(Request $request)
    {
        $supplierId = $request->input('id'); // Ambil ID jika ada (untuk update)

        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:100',
            'phone'          => 'nullable|string|max:20',
            'address'        => 'nullable|string',
            'type'           => [
                'required',
                Rule::in([Supplier::TYPE_TEMPO, Supplier::TYPE_PETTY_CASH]),
            ],
            'credit_limit'   => [
                'nullable',
                'required_if:type,' . Supplier::TYPE_TEMPO,
                'numeric',
                'min:0',
            ],
            'jatuh_tempo1'   => 'nullable|required_if:type,' . Supplier::TYPE_TEMPO . '|date', // Wajib jika Tempo
            'jatuh_tempo2'   => 'nullable|date|after_or_equal:jatuh_tempo1',                   // Opsional, harus setelah tempo1 jika diisi
        ], [
            'credit_limit.required_if'    => 'Limit kredit wajib diisi untuk supplier tipe Tempo.',
            'jatuh_tempo1.required_if'    => 'Tanggal Jatuh Tempo 1 wajib diisi untuk supplier tipe Tempo.',
            'jatuh_tempo2.after_or_equal' => 'Tanggal Jatuh Tempo 2 harus sama atau setelah Tanggal Jatuh Tempo 1.',
        ]);

        if ($validated['type'] === Supplier::TYPE_PETTY_CASH) {
            $validated['credit_limit'] = 0;
            $validated['jatuh_tempo1'] = null;
            $validated['jatuh_tempo2'] = null;
        } else {
            $validated['credit_limit'] = $validated['credit_limit'] ?? 0;
            $validated['jatuh_tempo1'] = $validated['jatuh_tempo1'] ?: null;
            $validated['jatuh_tempo2'] = $validated['jatuh_tempo2'] ?: null;
        }        

        try {
            if ($supplierId) {
                // Update
                $supplier = Supplier::findOrFail($supplierId);
                $supplier->update($validated);
                $message = 'Data supplier berhasil diperbarui.';
            } else {
                $supplier = Supplier::create($validated);
                $message  = 'Supplier baru berhasil ditambahkan.';
            }

            return response()->json([
                'status'  => 'success',
                'message' => $message,
                'data'    => $supplier,
            ]);
        } catch (Exception $e) {
            Log::error("Error saving supplier: " . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function suppliersDestroy(Supplier $supplier)
    {
        $supplier->delete();
        return response()->json(['status' => 'success', 'message' => 'Supplier berhasil dihapus.']);
    }

    public function suppPaymentIndex()
    {
        $suppliers = Supplier::where('type', Supplier::TYPE_TEMPO)
            ->orderBy('name')
            ->get(['id', 'name', 'jatuh_tempo1', 'jatuh_tempo2']);

        $outstandingPOs = PurchaseOrder::with('supplier:id,name')
            ->where('payment_type', PurchaseOrder::PAYMENT_TEMPO)
            ->where('payment_status', '!=', PurchaseOrder::STATUS_LUNAS)
            ->orderBy('order_date', 'asc')
            ->get();

        $groupedPOs = $outstandingPOs->groupBy('supplier_id');

        return view('accounting.supplier_payments.index', compact('suppliers', 'groupedPOs'));
    }

    public function suppPaymentStore(Request $request)
    {
        $validated = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'payment_date'      => 'required|date',
            'amount'            => 'required|numeric|min:0.01',
            'payment_method'    => 'required|string|max:50',
            'reference_number'  => 'nullable|string|max:100',
            'notes'             => 'nullable|string|max:1000',
            'proof_file'        => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        try {
            DB::transaction(function () use ($request, $validated) {
                $po = PurchaseOrder::with('supplier')->findOrFail($validated['purchase_order_id']);

                if ($po->payment_type !== PurchaseOrder::PAYMENT_TEMPO) {
                    throw new Exception("PO #{$po->po_number} bukan merupakan PO Tempo.");
                }

                if ($po->payment_status === PurchaseOrder::STATUS_LUNAS) {
                    throw new Exception("PO #{$po->po_number} sudah lunas.");
                }

                // Ambil total pembayaran yang sudah masuk di DB (dari tabel supplier_payments, bukan hanya field di PO)
                $alreadyPaid = SupplierPayment::where('purchase_order_id', $po->id)->sum('amount');
                $payAmount   = (float) $validated['amount'];
                $totalPaid   = $alreadyPaid + $payAmount;
                $outstanding = (float) max($po->total_amount - $alreadyPaid, 0);

                if ($payAmount > $outstanding) {
                    throw new Exception("Jumlah bayar melebihi sisa tagihan.");
                }

                $filePath = null;
                if ($request->hasFile('proof_file')) {
                    $filePath = $request->file('proof_file')->store('supplier_payments_proof', 'public');
                }

                SupplierPayment::create([
                    'purchase_order_id' => $po->id,
                    'supplier_id'       => $po->supplier_id,
                    'payment_date'      => $validated['payment_date'],
                    'amount'            => $payAmount,
                    'payment_method'    => $validated['payment_method'],
                    'reference_number'  => $validated['reference_number'] ?? null,
                    'proof_file'        => $filePath,
                    'notes'             => $validated['notes'] ?? null,
                    'user_id'           => auth()->id(),
                ]);

                $isLunas   = $totalPaid >= round($po->total_amount - 0.001, 2);
                $newStatus = $isLunas ? PurchaseOrder::STATUS_LUNAS : PurchaseOrder::STATUS_SEBAGIAN;

                $po->update([
                    'paid_amount'    => $totalPaid,
                    'payment_status' => $newStatus,
                ]);

                if ($po->supplier) {
                    Supplier::where('id', $po->supplier_id)->increment('credit_limit', $payAmount);
                } else {
                    Log::warning("Supplier tidak ditemukan saat mencoba mengembalikan limit kredit untuk PO ID: {$po->id}");
                }
            });

            return response()->json([
                'status'  => 'success',
                'message' => 'Pembayaran supplier berhasil dicatat.',
            ]);
        } catch (Exception $e) {
            Log::error("Error saving supplier payment: " . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mencatat pembayaran: ' . $e->getMessage(),
            ], 422);
        }
    }

    public function creditLimitMonitoring()
    {
        $suppliers = Supplier::where('credit_limit', '>', 0)
            ->with(['purchaseOrders' => function ($query) {
                $query->where('payment_type', Supplier::TYPE_TEMPO)
                    ->where('status', 'diterima');
            }])
            ->get();

        $suppliersData = $suppliers->map(function ($supplier) {
            // Total utang supplier = total_amount semua PO pembayaran tempo status 'diterima', dikurangi paid_amount
            $currentDebt = $supplier->purchaseOrders->sum(function ($order) {
                return max($order->total_amount - $order->paid_amount, 0);
            });

            $remainingCredit = $supplier->credit_limit - $currentDebt;

            $usagePercentage = ($supplier->credit_limit > 0)
                ? ($currentDebt / $supplier->credit_limit) * 100
                : 0;

            return (object) [
                'id'               => $supplier->id,
                'name'             => $supplier->name,
                'credit_limit'     => $supplier->credit_limit,
                'current_debt'     => $currentDebt,
                'remaining_credit' => $remainingCredit,
                'usage_percentage' => round($usagePercentage, 2),
            ];
        });

        return view('accounting.suppliers.credit_limit', [
            'suppliersData' => $suppliersData,
        ]);
    }

    public function suppCreditHistory(Supplier $supplier)
    {

        if (trim($supplier->type) !== Supplier::TYPE_TEMPO) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Riwayat kredit hanya tersedia untuk supplier tipe Tempo.',
            ], 400);
        }

        try {
            $purchaseOrders = PurchaseOrder::where('supplier_id', $supplier->id)
                ->where('payment_type', PurchaseOrder::PAYMENT_TEMPO) // Hanya PO Tempo
                ->orderBy('order_date', 'desc')
                ->get([
                    'id',
                    'order_date',
                    'po_number',
                    'notes',
                    'total_amount',
                    'paid_amount',
                ]);

            $poHistory = $purchaseOrders->map(function ($po) {
                return [
                    'order_date'         => $po->order_date ? $po->order_date->format('d M Y') : '-',
                    'po_number'          => $po->po_number,
                    'description'        => $po->notes ?? '-', // Gunakan notes sebagai deskripsi
                    'total_amount'       => (float) $po->total_amount,
                    'paid_amount'        => (float) $po->paid_amount,
                    'outstanding_amount' => $po->outstanding_amount,
                ];
            });

            $supplierPayments = SupplierPayment::where('supplier_id', $supplier->id)
                ->orderBy('payment_date', 'desc')
                ->get([
                    'id',
                    'payment_date',
                    'reference_number',
                    'payment_method',
                    'amount',
                    'notes',
                ]);

            $paymentHistory = $supplierPayments->map(function ($payment) {
                return [
                    'payment_date'     => $payment->payment_date ? $payment->payment_date->format('d M Y') : '-',
                    'reference_number' => $payment->reference_number,
                    'payment_method'   => $payment->payment_method,
                    'amount'           => (float) $payment->amount,
                    'notes'            => $payment->notes,
                ];
            });

            return response()->json([
                'status'          => 'success',
                'po_history'      => $poHistory,
                'payment_history' => $paymentHistory,
            ]);

        } catch (Throwable $e) {
            Log::error("Error fetching credit history for supplier {$supplier->id}: " . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengambil data riwayat.',
            ], 500); // Internal Server Error
        }
    }

    public function salesReport(Request $request)
    {
        [$query, $reportTitle, $filters] = $this->buildSalesReportQuery($request);

        $summary = (clone $query)->selectRaw("
            COUNT(id) as total_transactions,
            SUM(subtotal) as total_subtotal,
            SUM(discount_amount) as total_discount,
            SUM(tax_amount) as total_tax,
            SUM(total_amount) as total_revenue,
            AVG(total_amount) as average_sale
        ")->first();

        $availableYears = Sale::selectRaw("strftime('%Y', created_at) as year")
            ->where('status', 'completed')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        $sales = $query->paginate(10)->withQueryString();

        return view('accounting.laporan.penjualan.index', [
            'sales'          => $sales,
            'summary'        => $summary,
            'reportTitle'    => $reportTitle,
            'availableYears' => $availableYears,
            'filters'        => $filters,
        ]);
    }

    public function salesReportXls(Request $request)
    {
        [$query, $reportTitle, $filters] = $this->buildSalesReportQuery($request);

        $sales = $query->with('user')->get();

        $fileName = 'laporan-penjualan-' . Str::slug($reportTitle);

        $dataForExport = $sales->map(function ($sale) {
            return [
                'No Transaksi'  => $sale->transaction_code,
                'Tanggal'       => $sale->created_at ? Carbon::parse($sale->created_at)->format('Y-m-d H:i:s') : null,
                'Nama Kasir'    => optional($sale->user)->name,
                'Sub Total'     => (float) $sale->subtotal,
                'Diskon'        => (float) $sale->discount_amount,
                'Pajak'         => (float) $sale->tax_amount,
                'Total'         => (float) $sale->total_amount,
                'Status'        => $sale->status,
                'Nama Customer' => $sale->customer_name,
                'Order Type'    => $sale->order_type,
                'Tipe'          => $sale->type,
            ];
        });

        return response()->json([
            'status'      => 'success',
            'fileName'    => $fileName . '.xlsx', // Pastikan ekstensi .xlsx
            'reportTitle' => $reportTitle,
            'salesData'   => $dataForExport,
        ]);
    }

    public function salesReportPdf(Request $request)
    {
        [$query, $reportTitle, $filters] = $this->buildSalesReportQuery($request);

        $summary = (clone $query)->selectRaw("
            SUM(subtotal) as total_subtotal,
            SUM(discount_amount) as total_discount,
            SUM(tax_amount) as total_tax,
            SUM(total_amount) as total_revenue,
            COUNT(id) as total_transactions
        ")->first();

        $sales = $query->with([
            'user',
            'items.menuItem',
            'items.selectedModifiers.modifier',
        ])->get();

        $settings = Setting::pluck('value', 'key')->toArray();
        $fileName = 'laporan-penjualan-' . Str::slug($reportTitle) . '.pdf';

        $pdf = Pdf::loadView(
            'accounting.laporan.penjualan._print',
            compact('sales', 'reportTitle', 'summary', 'settings')
        )->setPaper('a4', 'portrait');

        return $pdf->download($fileName);
    }

    private function buildSalesReportQuery(Request $request)
    {
        $request->validate([
            'filter_date'  => 'nullable|date',
            'filter_month' => 'nullable|integer|between:1,12',
            'filter_year'  => 'nullable|integer|min:2020|max:' . (date('Y') + 1),
            'search'       => 'nullable|string',
        ]);

        $filterDate  = $request->input('filter_date');
        $filterMonth = $request->input('filter_month');
        $filterYear  = $request->input('filter_year');

        if (empty($filterDate) && empty($filterMonth) && empty($filterYear)) {
            $filterYear  = now()->year;
            $filterMonth = (int) now()->month;
        }

        $query = Sale::query()
            ->latest();

        $reportTitle = "Laporan Penjualan Harian";

        if ($filterDate) {
            $query->whereDate('created_at', $filterDate);
            $reportTitle = "Laporan Penjualan " . Carbon::parse($filterDate)->translatedFormat('d F Y');
        } elseif ($filterMonth && $filterYear) {
            $query->whereYear('created_at', $filterYear)
                ->whereMonth('created_at', (int) $filterMonth);
            $reportTitle = "Laporan Penjualan " . Carbon::create()->month((int) $filterMonth)->translatedFormat('F') . " " . $filterYear;
        } elseif ($filterYear) {
            $query->whereYear('created_at', $filterYear);
            $reportTitle = "Laporan Penjualan Tahun " . $filterYear;
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('transaction_code', 'like', '%' . $search . '%')
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        $filters = [
            'date'   => $filterDate,
            'month'  => $filterMonth,
            'year'   => $filterYear,
            'search' => $request->input('search'),
        ];

        return [$query, $reportTitle, $filters];
    }

    public function stockMovementReport(Request $request)
    {
        [$query, $filters, $reportTitle] = $this->buildStockMovementQuery($request);

        $movements = $query->paginate(25)->withQueryString();

        $ingredients = Ingredient::orderBy('name')->get()->map(function ($item) {
            return [
                'id'           => $item->id,
                'name'         => $item->name,
                'type'         => 'ingredient',
                'display_name' => $item->name . ' (Bahan Baku)',
                'stock'        => $item->stock ?? null,
                'unit'         => $item->unit ?? null,
                'qty'          => $item->stock ?? null, // qty = stock untuk ingredient
            ];
        });
        $ffnes = Ffne::orderBy('nama_ffne')->get()->map(function ($item) {
            return [
                'id'           => $item->id,
                'name'         => $item->nama_ffne,
                'type'         => 'ffne',
                'display_name' => $item->nama_ffne . ' (FFNE)',
                'stock'        => $item->stock ?? null,
                'unit'         => $item->satuan_ffne ?? null, // unit = satuan_ffne untuk ffne
                'qty'          => $item->stock ?? null,       // qty = stock untuk ffne juga
            ];
        });
        $allItems     = $ingredients->concat($ffnes)->sortBy('name');
        $todayOpnames = $this->getTodayOpnamesQuery()->get();

        return view('accounting.laporan.stok.mutasi', [
            'movements'    => $movements,
            'filters'      => $filters,
            'reportTitle'  => $reportTitle,
            'allItems'     => $allItems,
            'todayOpnames' => $todayOpnames,
        ]);
    }

    public function stockMovementExport(Request $request, $type)
    {
        if (! in_array($type, ['excel', 'pdf'])) { // 'excel' akan dihandle sbg JSON
            abort(404, "Tipe ekspor tidak valid.");
        }

        [$query, $filters, $reportTitle] = $this->buildStockMovementQuery($request);
        $movements                       = $query->get();
        $settings                        = Setting::pluck('value', 'key')->toArray();
        $fileName                        = 'laporan-mutasi-stok-' . Str::slug($reportTitle);

        if ($type === 'pdf') {
            $pdf = Pdf::loadView(
                'accounting.laporan.stok._print_mutasi',
                compact('movements', 'reportTitle', 'settings', 'filters')
            )->setPaper('a4', 'landscape');
            return $pdf->download($fileName . '.pdf');
        }

        $dataForExport = $movements->map(function ($move) {
            return [
                'Referensi' => $move->reference,
                'Tanggal'   => Carbon::parse($move->movement_date)->format('Y-m-d H:i:s'),
                'ID Item'   => $move->item_id,
                'Tipe Item' => $move->item_type,
                'Nama Item' => $move->name,
                'Arah'      => $move->movement_direction,
                'Jumlah'    => (float) $move->quantity,
                'Satuan'    => $move->unit, // <-- AMBIL DARI KOLOM 'unit' YANG BARU
                'Deskripsi' => $move->description,
            ];
        });

        return response()->json([
            'status'      => 'success',
            'fileName'    => $fileName . '.xlsx',
            'reportTitle' => $reportTitle,
            'salesData'   => $dataForExport, // Key konsisten dengan JS Laba Rugi
        ]);
    }

    private function buildStockMovementQuery(Request $request)
    {
        $request->validate([
            'start_date'    => 'nullable|date',
            'end_date'      => 'nullable|date|after_or_equal:start_date',
            'item_id'       => 'nullable|integer',
            'item_type'     => 'nullable|string|in:ingredient,ffne',
            'movement_type' => 'nullable|string|in:in,out',
        ]);

        $startDate    = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate      = $request->input('end_date', now()->endOfMonth()->toDateString());
        $movementType = $request->input('movement_type');
        $itemId       = $request->input('item_id');
        $itemType     = $request->input('item_type');

        $stockAdjIngredients = DB::table('ingredient_stock_adjustments as adj')
            ->join('ingredients', 'ingredients.id', '=', 'adj.ingredient_id')
            ->join('users', 'users.id', '=', 'adj.user_id')
            ->select(
                DB::raw('"ADJ-ING-" || adj.id as reference'),
                'adj.created_at as movement_date',
                'ingredients.id as item_id',
                DB::raw('"ingredient" as item_type'),
                'ingredients.name as name',
                DB::raw('CASE WHEN adj.quantity >= 0 THEN "in" ELSE "out" END as movement_direction'),
                DB::raw('ABS(adj.quantity) as quantity'), // Tampilkan nilai absolut
                'ingredients.unit as unit',
                DB::raw('"[ADJ] " || adj.type || ": " || COALESCE(adj.notes, "") as description')
            )
            ->whereBetween(DB::raw('DATE(adj.created_at)'), [$startDate, $endDate]);

        $stockAdjFfne = DB::table('ffne_stock_adjs as adj')
            ->join('ffnes', 'ffnes.id', '=', 'adj.ffne_id')
            ->join('users', 'users.id', '=', 'adj.user_id')
            ->select(
                DB::raw('"ADJ-FFNE-" || adj.id as reference'),
                'adj.created_at as movement_date',
                'ffnes.id as item_id',
                DB::raw('"ffne" as item_type'),
                'ffnes.nama_ffne as name',
                DB::raw('CASE WHEN adj.quantity >= 0 THEN "in" ELSE "out" END as movement_direction'),
                DB::raw('ABS(adj.quantity) as quantity'),
                'ffnes.satuan_ffne as unit',
                DB::raw('"[ADJ] " || adj.type || ": " || COALESCE(adj.notes, "") as description')
            )
            ->whereBetween(DB::raw('DATE(adj.created_at)'), [$startDate, $endDate]);

        if ($itemType === 'ingredient') {
            $stockAdjFfne->whereRaw('1 = 0'); // Hanya ambil Ingredient
        } elseif ($itemType === 'ffne') {
            $stockAdjIngredients->whereRaw('1 = 0'); // Hanya ambil FFNE
        }

        if ($itemId) {
            $stockAdjIngredients->where('ingredients.id', $itemId);
            $stockAdjFfne->where('ffnes.id', $itemId);
        }

        $query = $stockAdjIngredients->unionAll($stockAdjFfne);

        if ($movementType === 'in') {
            $query->where('adj.quantity', '>=', 0);
        } elseif ($movementType === 'out') {
            $query->where('adj.quantity', '<', 0);
        }
        $query = DB::table(DB::raw("({$query->toSql()}) as movements"))
            ->mergeBindings($query)
            ->orderBy('movement_date', 'desc');

        $reportTitle = "Laporan Mutasi Stok " . Carbon::parse($startDate)->format('d M Y') . " - " . Carbon::parse($endDate)->format('d M Y');

        $filters = [
            'start_date'    => $startDate,
            'end_date'      => $endDate,
            'item_id'       => $itemId,
            'item_type'     => $itemType,
            'movement_type' => $movementType,
        ];

        return [$query, $filters, $reportTitle];
    }

    public function submitStockOpname(Request $request)
    {
        $validated = $request->validate([
            'item_id'      => 'required|integer',
            'item_type'    => ['required', Rule::in(['ingredient', 'ffne'])],
            'actual_stock' => 'required|numeric|min:0', // Stok fisik hasil hitungan
            'notes'        => 'nullable|string|max:255',
        ]);

        $itemModelClass = null;
        $logModelClass  = null;
        $foreignKey     = '';

        if ($validated['item_type'] === 'ingredient') {
            $itemModelClass = Ingredient::class;
            $logModelClass  = IngredientStockAdjustment::class;
            $foreignKey     = 'ingredient_id';
        } else { // 'ffne'
            $itemModelClass = Ffne::class;
            $logModelClass  = \App\Models\FfneStockAdj::class; // Pastikan namespace FfneStockAdj benar
            $foreignKey     = 'ffne_id';
        }

        try {
            DB::transaction(function () use ($itemModelClass, $logModelClass, $foreignKey, $validated) {
                $item = $itemModelClass::findOrFail($validated['item_id']);

                $stockBefore   = $item->stock;
                $stockAfter    = (float) $validated['actual_stock'];
                $adjustmentQty = $stockAfter - $stockBefore; // Ini bisa positif atau negatif

                $item->stock = $stockAfter;
                $item->save();

                $logModelClass::create([
                    $foreignKey    => $item->id,
                    'user_id'      => Auth::id(),
                    'type'         => 'opname',
                    'quantity'     => $adjustmentQty, // Catat selisihnya
                    'stock_before' => $stockBefore,
                    'stock_after'  => $stockAfter,
                    'notes'        => $validated['notes'] ?? 'Stock Opname',
                ]);
            }); // Akhir Transaksi

            return response()->json(['status' => 'success', 'message' => 'Stock opname berhasil disimpan.']);

        } catch (Throwable $e) {
            Log::error("Gagal Stock Opname: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Gagal menyimpan opname: ' . $e->getMessage()], 500);
        }
    }

    private function getTodayOpnamesQuery()
    {
        $today = now()->toDateString();

        $opnameIngredients = DB::table('ingredient_stock_adjustments as adj')
            ->join('ingredients', 'ingredients.id', '=', 'adj.ingredient_id')
            ->join('users', 'users.id', '=', 'adj.user_id')
            ->select(
                'adj.created_at as timestamp',
                'ingredients.name as item_name',
                DB::raw('"Ingredient" as item_type'),
                'adj.stock_before',
                'adj.stock_after',
                'adj.quantity as adjustment_qty', // <-- 'quantity'
                'users.name as user_name',
                'adj.notes'
            )
            ->where('adj.type', 'opname')
            ->whereDate('adj.created_at', $today);

        $opnameFfne = DB::table('ffne_stock_adjs as adj')
            ->join('ffnes', 'ffnes.id', '=', 'adj.ffne_id')
            ->join('users', 'users.id', '=', 'adj.user_id')
            ->select(
                'adj.created_at as timestamp',
                'ffnes.nama_ffne as item_name',
                DB::raw('"FFNE" as item_type'),
                'adj.stock_before',               // <-- Sekarang sudah ada
                'adj.stock_after',                // <-- Sekarang sudah ada
                'adj.quantity as adjustment_qty', // <-- GANTI 'qty'
                'users.name as user_name',
                'adj.notes'
            )
            ->where('adj.type', 'opname')
            ->whereDate('adj.created_at', $today);

        return $opnameIngredients->unionAll($opnameFfne)
            ->orderBy('timestamp', 'desc');
    }

    public function stockOpnameTodayPdf(Request $request)
    {
        $todayOpnames = $this->getTodayOpnamesQuery()->get();
        $reportTitle  = "Laporan Detail Stock Opname - " . now()->translatedFormat('d F Y');
        $settings     = Setting::pluck('value', 'key')->toArray();
        $fileName     = 'laporan-opname-' . now()->format('Y-m-d') . '.pdf';

        // Buat view baru untuk print PDF
        $pdf = Pdf::loadView(
            'accounting.laporan.stok._print_opname_today', // <-- View baru
            compact('todayOpnames', 'reportTitle', 'settings')
        )->setPaper('a4', 'landscape'); // Landscape agar muat

        return $pdf->download($fileName);
    }

    public function stockOpnameTodayExcel(Request $request)
    {
        $todayOpnames = $this->getTodayOpnamesQuery()->get();
        $reportTitle  = "Laporan Detail Stock Opname - " . now()->translatedFormat('d F Y');
        $fileName     = 'laporan-opname-' . now()->format('Y-m-d') . '.xlsx';

        $dataForExport = $todayOpnames->map(function ($item) {
            return [
                'Waktu'         => Carbon::parse($item->timestamp)->format('H:i:s'),
                'Nama Item'     => $item->item_name,
                'Tipe'          => $item->item_type,
                'Stok Sistem'   => (float) $item->stock_before,
                'Stok Fisik'    => (float) $item->stock_after,
                'Selisih (Qty)' => (float) $item->adjustment_qty,
                'User Opname'   => $item->user_name,
                'Catatan'       => $item->notes,
            ];
        });

        return response()->json([
            'status'      => 'success',
            'fileName'    => $fileName,
            'reportTitle' => $reportTitle,
            'salesData'   => $dataForExport, // Pakai key 'salesData' agar JS konsisten
        ]);
    }

    public function payrollIndex(Request $request)
    {
        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));

        $karyawans = Karyawan::with(['payroll' => function ($query) use ($bulan, $tahun) {
            $query->where('bulan', $bulan)->where('tahun', $tahun);
        }])
            ->orderBy('nama', 'asc')
            ->get();

        return view('admin.payrolls.index', compact('karyawans', 'bulan', 'tahun'));
    }

    public function payrollStore(Request $request)
    {
        $validated = $request->validate([
            'id'                 => 'nullable|exists:payroll,id',
            'karyawan_id'        => 'required|exists:karyawans,id',
            'bulan'              => 'required|integer|min:1|max:12',
            'tahun'              => 'required|integer|min:2000',
            'jumlah_absensi'     => 'required|integer|min:0',
            'nominal_gaji'       => 'required|numeric|min:0',
            'status_pembayaran'  => 'required|in:pending,dibayar',
            'tanggal_pembayaran' => 'nullable|required_if:status_pembayaran,dibayar|date',
            'file_bukti'         => 'nullable|required_if:status_pembayaran,dibayar|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        unset($validated['id']);

        try {
            $payroll = null; // Inisialisasi
            $message = '';

            if ($request->hasFile('file_bukti')) {
                $path                    = $request->file('file_bukti')->store('public/bukti_pembayaran');
                $validated['file_bukti'] = $path;
            }

            // Logika Create (INSERT)
            if (! $request->filled('id')) {
                $request->validate([
                    'karyawan_id' => Rule::unique('payroll')->where(function ($query) use ($request) {
                        return $query->where('bulan', $request->bulan)
                            ->where('tahun', $request->tahun);
                    }),
                ], ['karyawan_id.unique' => 'Gaji karyawan ini di periode ini sudah ada.']);

                $payroll = Payroll::create($validated);
                $message = 'Data gaji berhasil diinput.';
            }
            // Logika Update (UPDATE)
            else {
                // GUNAKAN findOrFail UNTUK MENCEGAH NULL
                $payroll = Payroll::findOrFail($request->id);

                if ($validated['status_pembayaran'] == 'pending') {
                    if ($payroll->file_bukti) { // <-- Aman karena $payroll tidak null
                        Storage::delete($payroll->file_bukti);
                    }
                    $validated['file_bukti']         = null;
                    $validated['tanggal_pembayaran'] = null;
                } else {
                    if ($request->hasFile('file_bukti') && $payroll->file_bukti) { // <-- Aman
                        Storage::delete($payroll->file_bukti);
                    }
                    if (! $request->hasFile('file_bukti') && $validated['status_pembayaran'] == 'dibayar') {
                        $validated['file_bukti'] = $payroll->file_bukti; // <-- Aman
                    }
                }

                $payroll->update($validated);
                $message = 'Data gaji berhasil diperbarui.';
            }

            return response()->json(['status' => 'success', 'message' => $message]);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function payrollDestroy($id)
    {
        try {
            $payroll = Payroll::findOrFail($id);

            if ($payroll->file_bukti) { // <-- Aman karena $payroll tidak null
                Storage::delete($payroll->file_bukti);
            }

            $payroll->delete();

            return response()->json(['status' => 'success', 'message' => 'Data gaji berhasil dihapus.']);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function downloadBukti($id)
    {
        try {
            $payroll = Payroll::findOrFail($id);

            if (! $payroll->file_bukti) {
                abort(404, 'File bukti tidak ditemukan di database.');
            }

            if (! Storage::exists($payroll->file_bukti)) {
                abort(404, 'File bukti tidak ada di storage.');
            }

            return Storage::download($payroll->file_bukti);
        } catch (Exception $e) {
            abort(404, 'File tidak dapat diakses.');
        }
    }

    private function getProfitAndLossData(string $startDate, string $endDate)
    {
        $alert        = [];
        $hppBreakdown = [];

        // ==================== REVENUE ====================
        $totalRevenue = Sale::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->where('type', Sale::TYPE_REGULAR)
            ->sum('total_amount');

        // Validasi: Check transaksi dengan nilai 0
        $zeroSales = Sale::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->where('type', Sale::TYPE_REGULAR)
            ->where('total_amount', 0)
            ->count();

        if ($zeroSales > 0) {
            $alert[] = "⚠️ Terdapat {$zeroSales} transaksi dengan nilai Rp 0";
        }

        // ==================== HPP REGULAR SALES ====================
        $saleItems = SaleItem::select('id', 'menu_item_id', 'quantity')
            ->whereHas('sale', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate])
                    ->where('status', 'completed')
                    ->where('type', Sale::TYPE_REGULAR);
            })
            ->with([
                'menuItem' => function ($q) {
                    $q->select('id', 'name', 'menu_category_id')
                        ->with([
                            'menuCategory:id,name',
                            'ingredients:id,name,cost_price',
                        ]);
                },
            ])
            ->get();

        $totalHpp = $saleItems->sum(function ($saleItem) use (&$alert, &$hppBreakdown) {
            // Validasi: Menu Item tidak ada
            if (! $saleItem->menuItem) {
                $alert[] = "❌ Sale Item ID {$saleItem->id} tidak memiliki menu (menuItem) terkait";
                return 0;
            }

            // Validasi: Quantity invalid
            if ($saleItem->quantity <= 0) {
                $alert[] = "❌ Sale Item ID {$saleItem->id} memiliki quantity invalid: {$saleItem->quantity}";
                return 0;
            }

            // Validasi: Ingredients kosong
            if ($saleItem->menuItem->ingredients->isEmpty()) {
                $alert[] = "⚠️ Menu '{$saleItem->menuItem->name}' tidak memiliki resep (ingredients)";
                return 0;
            }

            // Hitung HPP per menu item
            $costPerMenuItem = $saleItem->menuItem->ingredients->sum(function ($ingredient) use (&$alert, $saleItem) {
                $averageCost = $ingredient->getAverageCost();
                if ($averageCost == 0) {
                    $alert[] = "💰 Bahan '{$ingredient->name}' pada menu '{$saleItem->menuItem->name}' belum ada harga (cost_price)";
                }

                $quantityUsed = $ingredient->pivot->quantity ?? 0;

                if ($quantityUsed <= 0) {
                    $alert[] = "⚠️ Bahan '{$ingredient->name}' pada menu '{$saleItem->menuItem->name}' memiliki quantity invalid dalam resep";
                }

                return $averageCost * $quantityUsed;
            });

            $totalCost = $costPerMenuItem * $saleItem->quantity;

            // Breakdown HPP per category
            $categoryName = $saleItem->menuItem->menuCategory->name ?? 'Tanpa Kategori';

            if (! isset($hppBreakdown[$categoryName])) {
                $hppBreakdown[$categoryName] = [
                    'category'       => $categoryName,
                    'total_hpp'      => 0,
                    'items_count'    => 0,
                    'total_quantity' => 0,
                ];
            }

            $hppBreakdown[$categoryName]['total_hpp'] += $totalCost;
            $hppBreakdown[$categoryName]['items_count'] += 1;
            $hppBreakdown[$categoryName]['total_quantity'] += $saleItem->quantity;

            return $costPerMenuItem * $saleItem->quantity;
        });

        // ==================== EMPLOYEE MEAL COST (FIXED BUG) ====================
        $employeeSaleItems = SaleItem::select('id', 'menu_item_id', 'quantity')
            ->whereHas('sale', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate])
                    ->where('status', 'completed')
                    ->where('type', Sale::TYPE_EMPLOYEE);
            })
            ->with([
                'menuItem' => function ($q) {
                    $q->select('id', 'name')
                        ->with(['ingredients:id,name,cost_price']);
                },
            ])
            ->get();

        $totalEmployeeMealCost = $employeeSaleItems->sum(function ($saleItem) use (&$alert) {
            // ✅ FIX: Gunakan menuItem bukan itemable
            if (! $saleItem->menuItem) {
                $alert[] = "❌ Employee meal: Sale Item ID {$saleItem->id} tidak memiliki menu terkait";
                return 0;
            }

            // Validasi: Quantity invalid
            if ($saleItem->quantity <= 0) {
                $alert[] = "❌ Employee meal: Sale Item ID {$saleItem->id} memiliki quantity invalid";
                return 0;
            }

            if ($saleItem->menuItem->ingredients->isEmpty()) {
                $alert[] = "⚠️ Employee meal: Menu '{$saleItem->menuItem->name}' tidak memiliki resep";
                return 0;
            }

            $costPerMenuItem = $saleItem->menuItem->ingredients->sum(function ($ingredient) use (&$alert, $saleItem) {
                $averageCost = $ingredient->getAverageCost(); // <-- Gunakan HPP rata-rata
                $quantityUsed = $ingredient->pivot->quantity;
                return $averageCost * $quantityUsed;
            });

            return $costPerMenuItem * $saleItem->quantity;
        });

        // ==================== EXPENSE DETAILS ====================
        $expenseDetails = [];

        // 1. Payroll
        $startMonth   = date('Y-m', strtotime($startDate));
        $endMonth     = date('Y-m', strtotime($endDate));
        $totalPayroll = Payroll::whereRaw("printf('%04d-%02d', tahun, bulan) BETWEEN ? AND ?", [$startMonth, $endMonth])
            ->sum('nominal_gaji');

        if ($totalPayroll > 0) {
            $expenseDetails[] = [
                'label'   => 'Beban Gaji (Payroll)',
                'value'   => $totalPayroll,
                'details' => [],
                'icon'    => 'bi-people-fill',
                'color'   => 'primary',
            ];
        }

        // 2. Employee Meal Cost
        if ($totalEmployeeMealCost > 0) {
            $expenseDetails[] = [
                'label'   => 'Beban Makan Karyawan (HPP)',
                'value'   => $totalEmployeeMealCost,
                'details' => [],
                'icon'    => 'bi-egg-fried',
                'color'   => 'warning',
            ];
        }

        // 3. FFNE Adjustments
        $ffneAdjustments = FfneStockAdj::whereIn('type', ['waste', 'usage', 'adjustment'])
            ->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])
            ->with('ffne:id,nama_ffne,harga')
            ->get();

        $totalFfneAdjustment = 0;
        $ffneAdjDetails      = [];

        foreach ($ffneAdjustments as $adj) {
            if ($adj->ffne && $adj->qty != 0) {
                $cost = abs($adj->qty * $adj->ffne->harga);
                $totalFfneAdjustment += $cost;

                $ffneAdjDetails[] = [
                    'Nama Barang'  => $adj->ffne->nama_ffne,
                    'Tipe'         => ucfirst($adj->type),
                    'Qty'          => $adj->qty,
                    'Harga Satuan' => (float) $adj->ffne->harga,
                    'Total Beban'  => $cost,
                ];
            }
        }

        if ($totalFfneAdjustment > 0) {
            $expenseDetails[] = [
                'label'   => 'Beban Stok FFNE (Rusak/Dipakai/Opname)',
                'value'   => $totalFfneAdjustment,
                'details' => $ffneAdjDetails,
                'icon'    => 'bi-box-seam',
                'color'   => 'danger',
            ];
        }

        // 4. Energy Cost
        $totalEnergyCost = EnergyCost::whereBetween('period', [$startDate, $endDate])->sum('cost');

        if ($totalEnergyCost > 0) {
            $expenseDetails[] = [
                'label'   => 'Beban Energi',
                'value'   => $totalEnergyCost,
                'details' => [],
                'icon'    => 'bi-lightning-charge-fill',
                'color'   => 'info',
            ];
        }

        // 5. Extras (Service & Assets)
        $extras = Extra::whereBetween('tanggal', [$startDate, $endDate])
            ->with('ffne:id,nama_ffne')
            ->get();

        $totalExtrasCost = $extras->sum('harga');
        $extrasDetails   = [];

        if ($totalExtrasCost > 0) {
            $extrasDetails = $extras->map(function ($extra) {
                return [
                    'Nama Aset'   => $extra->ffne->nama_ffne ?? 'N/A',
                    'Nama Servis' => $extra->nama,
                    'Tanggal'     => $extra->tanggal->format('d/m/Y'),
                    'Keterangan'  => $extra->keterangan,
                    'Biaya'       => (float) $extra->harga,
                ];
            })->toArray();

            $expenseDetails[] = [
                'label'   => 'Beban Servis & Extra (Aset)',
                'value'   => $totalExtrasCost,
                'details' => $extrasDetails,
                'icon'    => 'bi-tools',
                'color'   => 'secondary',
            ];
        }

        // ==================== CALCULATIONS ====================
        $grossProfit              = $totalRevenue - $totalHpp;
        $totalOperationalExpenses = array_sum(array_column($expenseDetails, 'value'));
        $netProfit                = $grossProfit - $totalOperationalExpenses;

        // ==================== METRICS ====================
        $metrics = [
            'gross_margin_percentage'  => $totalRevenue > 0
                ? round(($grossProfit / $totalRevenue) * 100, 2)
                : 0,
            'net_margin_percentage'    => $totalRevenue > 0
                ? round(($netProfit / $totalRevenue) * 100, 2)
                : 0,
            'expense_to_revenue_ratio' => $totalRevenue > 0
                ? round(($totalOperationalExpenses / $totalRevenue) * 100, 2)
                : 0,
            'hpp_to_revenue_ratio'     => $totalRevenue > 0
                ? round(($totalHpp / $totalRevenue) * 100, 2)
                : 0,
        ];

        // ==================== RETURN DATA ====================
        return [
            'revenue'       => [
                'label' => 'Total Pendapatan (Regular)',
                'value' => (float) $totalRevenue,
            ],
            'hpp'           => [
                'label'     => 'Total HPP (Regular)',
                'value'     => (float) $totalHpp,
                'has_alert' => count($alert) > 0,
                'breakdown' => array_values($hppBreakdown), // HPP Breakdown per category
            ],
            'alert'         => array_unique($alert),
            'alert_summary' => [
                'total'    => count(array_unique($alert)),
                'critical' => count(array_filter($alert, fn($a) => str_contains($a, '❌'))),
                'warning'  => count(array_filter($alert, fn($a) => str_contains($a, '⚠️'))),
                'info'     => count(array_filter($alert, fn($a) => str_contains($a, '💰'))),
            ],
            'gross_profit'  => [
                'label' => 'Laba Kotor (Pendapatan - HPP)',
                'value' => (float) $grossProfit,
            ],
            'expenses'      => [
                'label'   => 'Total Beban Operasional',
                'value'   => (float) $totalOperationalExpenses,
                'details' => $expenseDetails,
            ],
            'net_profit'    => [
                'label' => 'Laba Bersih (Laba Kotor - Beban)',
                'value' => (float) $netProfit,
            ],
            'metrics'       => $metrics,
        ];
    }

    public function profitAndLossReport(Request $request)
    {
        // Validasi dan ambil tanggal
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = $validated['start_date'] ?? now()->startOfMonth()->toDateString();
        $endDate   = $validated['end_date'] ?? now()->endOfMonth()->toDateString();

        $reportTitle = "Laporan Laba Rugi Periode " . Carbon::parse($startDate)->translatedFormat('d F Y') . " - " . Carbon::parse($endDate)->translatedFormat('d F Y');

        // Panggil fungsi private untuk mendapatkan data yang SUDAH DIFORMAT
        $summary = $this->getProfitAndLossData($startDate, $endDate);

        $filters = ['start_date' => $startDate, 'end_date' => $endDate];

        // Langsung kirim ke view, tidak perlu format ulang
        return view('accounting.laporan.laba_rugi.index', compact('summary', 'reportTitle', 'filters'));
    }

    public function profitAndLossDownloadExcel(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = $validated['start_date'];
        $endDate   = $validated['end_date'];

        $summary     = $this->getProfitAndLossData($startDate, $endDate);
        $reportTitle = "Laporan Laba Rugi Periode " . Carbon::parse($startDate)->translatedFormat('d F Y') . " - " . Carbon::parse($endDate)->translatedFormat('d F Y');
        $fileName    = 'laporan-laba-rugi-' . $startDate . '-sampai-' . $endDate . '.xlsx';

        $dataForExport = [];

        // ==================== 1. PENDAPATAN ====================
        $dataForExport[] = ['Akun' => '═══════════════════════════════════════', 'Deskripsi' => '', 'Debit' => '', 'Kredit' => ''];
        $dataForExport[] = ['Akun' => 'PENDAPATAN', 'Deskripsi' => '', 'Debit' => '', 'Kredit' => ''];
        $dataForExport[] = ['Akun' => '═══════════════════════════════════════', 'Deskripsi' => '', 'Debit' => '', 'Kredit' => ''];
        $dataForExport[] = [
            'Akun'      => '  ' . $summary['revenue']['label'],
            'Deskripsi' => 'Penjualan regular periode ' . Carbon::parse($startDate)->format('d/m/Y') . ' - ' . Carbon::parse($endDate)->format('d/m/Y'),
            'Debit'     => '',
            'Kredit'    => $summary['revenue']['value'],
        ];
        $dataForExport[] = ['Akun' => '', 'Deskripsi' => '', 'Debit' => '', 'Kredit' => ''];
        $dataForExport[] = [
            'Akun'      => 'TOTAL PENDAPATAN',
            'Deskripsi' => '',
            'Debit'     => '',
            'Kredit'    => $summary['revenue']['value'],
        ];
        $dataForExport[] = ['Akun' => '', 'Deskripsi' => '', 'Debit' => '', 'Kredit' => ''];

        // ==================== 2. HPP ====================
        $dataForExport[] = ['Akun' => '═══════════════════════════════════════', 'Deskripsi' => '', 'Debit' => '', 'Kredit' => ''];
        $dataForExport[] = ['Akun' => 'HARGA POKOK PENJUALAN (HPP)', 'Deskripsi' => '', 'Debit' => '', 'Kredit' => ''];
        $dataForExport[] = ['Akun' => '═══════════════════════════════════════', 'Deskripsi' => '', 'Debit' => '', 'Kredit' => ''];

        // HPP Breakdown per Category
        if (! empty($summary['hpp']['breakdown'])) {
            foreach ($summary['hpp']['breakdown'] as $category) {
                $dataForExport[] = [
                    'Akun'      => '  HPP - ' . $category['category'],
                    'Deskripsi' => $category['items_count'] . ' item menu, ' . $category['total_quantity'] . ' porsi terjual',
                    'Debit'     => $category['total_hpp'],
                    'Kredit'    => '',
                ];
            }
            $dataForExport[] = ['Akun' => '', 'Deskripsi' => '', 'Debit' => '', 'Kredit' => ''];
        }

        $dataForExport[] = [
            'Akun'      => 'TOTAL HPP',
            'Deskripsi' => 'Rasio HPP: ' . $summary['metrics']['hpp_to_revenue_ratio'] . '%',
            'Debit'     => $summary['hpp']['value'],
            'Kredit'    => '',
        ];
        $dataForExport[] = ['Akun' => '', 'Deskripsi' => '', 'Debit' => '', 'Kredit' => ''];

        // ==================== 3. LABA KOTOR ====================
        $dataForExport[] = ['Akun' => '───────────────────────────────────────', 'Deskripsi' => '', 'Debit' => '', 'Kredit' => ''];
        $dataForExport[] = [
            'Akun'      => 'LABA KOTOR',
            'Deskripsi' => 'Margin Kotor: ' . $summary['metrics']['gross_margin_percentage'] . '%',
            'Debit'     => '',
            'Kredit'    => $summary['gross_profit']['value'],
        ];
        $dataForExport[] = ['Akun' => '───────────────────────────────────────', 'Deskripsi' => '', 'Debit' => '', 'Kredit' => ''];
        $dataForExport[] = ['Akun' => '', 'Deskripsi' => '', 'Debit' => '', 'Kredit' => ''];

        // ==================== 4. BEBAN OPERASIONAL ====================
        $dataForExport[] = ['Akun' => '═══════════════════════════════════════', 'Deskripsi' => '', 'Debit' => '', 'Kredit' => ''];
        $dataForExport[] = ['Akun' => 'BEBAN OPERASIONAL', 'Deskripsi' => '', 'Debit' => '', 'Kredit' => ''];
        $dataForExport[] = ['Akun' => '═══════════════════════════════════════', 'Deskripsi' => '', 'Debit' => '', 'Kredit' => ''];

        foreach ($summary['expenses']['details'] as $expense) {
            $dataForExport[] = [
                'Akun'      => '  ' . $expense['label'],
                'Deskripsi' => '',
                'Debit'     => $expense['value'],
                'Kredit'    => '',
            ];

            // Detail breakdown untuk setiap beban
            if (! empty($expense['details'])) {
                foreach ($expense['details'] as $detail) {
                    // Format detail berdasarkan tipe beban
                    if (isset($detail['Nama Barang'])) {
                        // FFNE Adjustments
                        $desc = sprintf(
                            "%s | %s | Qty: %s | @Rp %s",
                            $detail['Nama Barang'],
                            $detail['Tipe'],
                            $detail['Qty'],
                            number_format($detail['Harga Satuan'], 0, ',', '.')
                        );
                        $amount = $detail['Total Beban'];
                    } elseif (isset($detail['Nama Aset'])) {
                        // Extras (Service & Assets)
                        $desc = sprintf(
                            "%s - %s | %s | %s",
                            $detail['Nama Aset'],
                            $detail['Nama Servis'],
                            $detail['Tanggal'],
                            $detail['Keterangan']
                        );
                        $amount = $detail['Biaya'];
                    } else {
                        // Fallback untuk format lain
                        $desc = collect($detail)
                            ->map(fn($val, $key) => "$key: " . (is_numeric($val) ? number_format($val, 0, ',', '.') : $val))
                            ->join(' | ');
                        $amount = '';
                    }

                    $dataForExport[] = [
                        'Akun'      => '',
                        'Deskripsi' => '    • ' . $desc,
                        'Debit'     => $amount,
                        'Kredit'    => '',
                    ];
                }
            }
            $dataForExport[] = ['Akun' => '', 'Deskripsi' => '', 'Debit' => '', 'Kredit' => ''];
        }

        $dataForExport[] = [
            'Akun'      => 'TOTAL BEBAN OPERASIONAL',
            'Deskripsi' => 'Rasio Beban: ' . $summary['metrics']['expense_to_revenue_ratio'] . '%',
            'Debit'     => $summary['expenses']['value'],
            'Kredit'    => '',
        ];
        $dataForExport[] = ['Akun' => '', 'Deskripsi' => '', 'Debit' => '', 'Kredit' => ''];

        // ==================== 5. LABA BERSIH ====================
        $dataForExport[] = ['Akun' => '═══════════════════════════════════════', 'Deskripsi' => '', 'Debit' => '', 'Kredit' => ''];
        $dataForExport[] = [
            'Akun'      => 'LABA BERSIH',
            'Deskripsi' => 'Margin Bersih: ' . $summary['metrics']['net_margin_percentage'] . '%',
            'Debit'     => '',
            'Kredit'    => $summary['net_profit']['value'],
        ];
        $dataForExport[] = ['Akun' => '═══════════════════════════════════════', 'Deskripsi' => '', 'Debit' => '', 'Kredit' => ''];
        $dataForExport[] = ['Akun' => '', 'Deskripsi' => '', 'Debit' => '', 'Kredit' => ''];

        // ==================== 6. RINGKASAN METRICS ====================
        $dataForExport[] = ['Akun' => '', 'Deskripsi' => '', 'Debit' => '', 'Kredit' => ''];
        $dataForExport[] = ['Akun' => 'RINGKASAN ANALISIS KEUANGAN', 'Deskripsi' => '', 'Debit' => '', 'Kredit' => ''];
        $dataForExport[] = ['Akun' => '───────────────────────────────────────', 'Deskripsi' => '', 'Debit' => '', 'Kredit' => ''];
        $dataForExport[] = [
            'Akun'      => '  Gross Margin',
            'Deskripsi' => 'Persentase laba kotor terhadap pendapatan',
            'Debit'     => '',
            'Kredit'    => $summary['metrics']['gross_margin_percentage'] . '%',
        ];
        $dataForExport[] = [
            'Akun'      => '  Net Margin',
            'Deskripsi' => 'Persentase laba bersih terhadap pendapatan',
            'Debit'     => '',
            'Kredit'    => $summary['metrics']['net_margin_percentage'] . '%',
        ];
        $dataForExport[] = [
            'Akun'      => '  Expense Ratio',
            'Deskripsi' => 'Persentase beban operasional terhadap pendapatan',
            'Debit'     => '',
            'Kredit'    => $summary['metrics']['expense_to_revenue_ratio'] . '%',
        ];
        $dataForExport[] = [
            'Akun'      => '  HPP Ratio',
            'Deskripsi' => 'Persentase HPP terhadap pendapatan',
            'Debit'     => '',
            'Kredit'    => $summary['metrics']['hpp_to_revenue_ratio'] . '%',
        ];

        // ==================== 7. ALERT & WARNINGS ====================
        if (! empty($summary['alert'])) {
            $dataForExport[] = ['Akun' => '', 'Deskripsi' => '', 'Debit' => '', 'Kredit' => ''];
            $dataForExport[] = ['Akun' => '', 'Deskripsi' => '', 'Debit' => '', 'Kredit' => ''];
            $dataForExport[] = ['Akun' => 'PERINGATAN & CATATAN', 'Deskripsi' => '', 'Debit' => '', 'Kredit' => ''];
            $dataForExport[] = ['Akun' => '───────────────────────────────────────', 'Deskripsi' => '', 'Debit' => '', 'Kredit' => ''];
            $dataForExport[] = [
                'Akun'      => 'Summary',
                'Deskripsi' => sprintf(
                    'Total: %d peringatan (Critical: %d, Warning: %d, Info: %d)',
                    $summary['alert_summary']['total'],
                    $summary['alert_summary']['critical'],
                    $summary['alert_summary']['warning'],
                    $summary['alert_summary']['info']
                ),
                'Debit'     => '',
                'Kredit'    => '',
            ];
            $dataForExport[] = ['Akun' => '', 'Deskripsi' => '', 'Debit' => '', 'Kredit' => ''];

            foreach ($summary['alert'] as $index => $alert) {
                $dataForExport[] = [
                    'Akun'      => ($index + 1),
                    'Deskripsi' => $alert,
                    'Debit'     => '',
                    'Kredit'    => '',
                ];
            }
        }

        // ==================== 8. FOOTER ====================
        $dataForExport[] = ['Akun' => '', 'Deskripsi' => '', 'Debit' => '', 'Kredit' => ''];
        $dataForExport[] = ['Akun' => '', 'Deskripsi' => '', 'Debit' => '', 'Kredit' => ''];
        $dataForExport[] = [
            'Akun'      => '--- End of Report ---',
            'Deskripsi' => 'Generated by Restaurant POS System',
            'Debit'     => '',
            'Kredit'    => '',
        ];

        // ==================== 9. RESPONSE JSON ====================
        return response()->json([
            'status'      => 'success',
            'fileName'    => $fileName,
            'reportTitle' => $reportTitle,
            'salesData'   => $dataForExport,
            'metadata'    => [
                'period_start'   => Carbon::parse($startDate)->format('Y-m-d'),
                'period_end'     => Carbon::parse($endDate)->format('Y-m-d'),
                'generated_at'   => now()->toIso8601String(),
                'total_revenue'  => $summary['revenue']['value'],
                'total_hpp'      => $summary['hpp']['value'],
                'gross_profit'   => $summary['gross_profit']['value'],
                'total_expenses' => $summary['expenses']['value'],
                'net_profit'     => $summary['net_profit']['value'],
                'metrics'        => $summary['metrics'],
            ],
        ]);
    }
}
