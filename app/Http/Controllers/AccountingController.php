<?php
namespace App\Http\Controllers;

use App\Exports\SalesReportExport;
use App\Exports\StockMovementExport;
use App\Models\Ffne;
use App\Models\Ingredient;
use App\Models\Karyawan;
use App\Models\Payroll;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

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
            $validated['jatuh_tempo2'] = $validated['jatuh_tempo2'] ?? null;
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
        } catch (\Exception $e) {
            \Log::error("Error saving supplier: " . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan saat menyimpan data supplier.',
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
                    throw new \Exception("PO #{$po->po_number} bukan merupakan PO Tempo.");
                }

                if ($po->payment_status === PurchaseOrder::STATUS_LUNAS) {
                    throw new \Exception("PO #{$po->po_number} sudah lunas.");
                }

                // Ambil total pembayaran yang sudah masuk di DB (dari tabel supplier_payments, bukan hanya field di PO)
                $alreadyPaid = SupplierPayment::where('purchase_order_id', $po->id)->sum('amount');
                $payAmount   = (float) $validated['amount'];
                $totalPaid   = $alreadyPaid + $payAmount;
                $outstanding = (float) max($po->total_amount - $alreadyPaid, 0);

                if ($payAmount > $outstanding) {
                    throw new \Exception("Jumlah bayar melebihi sisa tagihan.");
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
                    \Log::warning("Supplier tidak ditemukan saat mencoba mengembalikan limit kredit untuk PO ID: {$po->id}");
                }
            });

            return response()->json([
                'status'  => 'success',
                'message' => 'Pembayaran supplier berhasil dicatat.',
            ]);
        } catch (\Exception $e) {
            \Log::error("Error saving supplier payment: " . $e->getMessage());
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
                'No Transaksi'     => $sale->transaction_code,
                'Tanggal'          => $sale->created_at ? \Carbon\Carbon::parse($sale->created_at)->format('Y-m-d H:i:s') : null,
                'Nama Kasir'       => optional($sale->user)->name,
                'Sub Total'        => (float) $sale->subtotal,
                'Diskon'           => (float) $sale->discount_amount,
                'Pajak'            => (float) $sale->tax_amount,
                'Total'            => (float) $sale->total_amount,
                'Status'           => $sale->status,
                'Nama Customer'    => $sale->customer_name,
                'Order Type'       => $sale->order_type,
                'Tipe'             => $sale->type,
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

        // Gabungkan ingredients dan ffnes untuk dropdown
        $allItems = collect();

        $ingredients = Ingredient::orderBy('name')->get()->map(function ($item) {
            return [
                'id'           => $item->id,
                'name'         => $item->name,
                'type'         => 'ingredient',
                'display_name' => $item->name . ' (Bahan Baku)',
            ];
        });

        $ffnes = Ffne::orderBy('nama_ffne')->get()->map(function ($item) {
            return [
                'id'           => $item->id,
                'name'         => $item->nama_ffne,
                'type'         => 'ffne',
                'display_name' => $item->nama_ffne . ' (FFNE)',
            ];
        });

        $allItems = $ingredients->concat($ffnes)->sortBy('name');

        return view('accounting.laporan.stok.mutasi', [
            'movements'   => $movements,
            'filters'     => $filters,
            'reportTitle' => $reportTitle,
            'allItems'    => $allItems,
        ]);
    }

    public function stockMovementExport(Request $request, $type)
    {
        if (! in_array($type, ['excel', 'pdf'])) {
            abort(404, "Tipe ekspor tidak valid.");
        }

        [$query, $filters, $reportTitle] = $this->buildStockMovementQuery($request);
        $movements                       = $query->get(); // Ambil semua data
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
            // Ambil satuan sesuai tipe item
            $satuan = null;
            if ($move->item_type === 'ingredient') {
                $satuan = property_exists($move, 'unit') || isset($move->unit) ? $move->unit : null;
            } elseif ($move->item_type === 'ffne') {
                $satuan = property_exists($move, 'satuan_ffne') || isset($move->satuan_ffne) ? $move->satuan_ffne : null;
            }
            return [
                'Referensi' => $move->reference,
                'Tanggal'   => Carbon::parse($move->movement_date)->format('Y-m-d H:i:s'),
                'ID Item'   => $move->item_id,
                'Tipe Item' => $move->item_type,
                'Nama Item' => $move->name,
                'Arah'      => $move->movement_direction,
                'Jumlah'    => (float) $move->quantity, // Pastikan jadi angka
                'Satuan'    => $satuan,
                'Deskripsi' => $move->description,
            ];
        });

        return response()->json([
            'status'      => 'success',
            'fileName'    => $fileName . '.xlsx', // Pastikan ekstensi .xlsx
            'reportTitle' => $reportTitle,
            'salesData'   => $dataForExport, // Ganti nama key agar konsisten
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

        // Query Barang Masuk (Ingredient only) - tambahkan kolom unit
        $stockIn = DB::table('goods_receipt_items')
            ->join('goods_receipts', 'goods_receipts.id', '=', 'goods_receipt_items.goods_receipt_id')
            ->join('purchase_order_items', 'purchase_order_items.id', '=', 'goods_receipt_items.purchase_order_item_id')
            ->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_items.purchase_order_id')
            ->join('suppliers', 'suppliers.id', '=', 'purchase_orders.supplier_id')
            ->join('ingredients', 'ingredients.id', '=', 'purchase_order_items.ingredient_id')
            ->select(
                'goods_receipts.receipt_number as reference',
                'goods_receipts.created_at as movement_date',
                'ingredients.id as item_id',
                DB::raw('"ingredient" as item_type'),
                'ingredients.name',
                DB::raw('"in" as movement_direction'),
                'goods_receipt_items.quantity_received as quantity',
                DB::raw('ingredients.unit as unit'), // Tambahkan kolom unit dari ingredients
                DB::raw('NULL as satuan_ffne'), // Kolom satuan_ffne dikosongkan
                DB::raw('"Dikirim Dari " || suppliers.name as description')
            )
            ->whereBetween(DB::raw('DATE(goods_receipts.created_at)'), [$startDate, $endDate]);

        // Query Barang Keluar - Penjualan (Ingredient only) - tambahkan kolom unit
        $stockOutSales = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('ingredient_menu_item', 'ingredient_menu_item.menu_item_id', '=', 'sale_items.menu_item_id')
            ->join('ingredients', 'ingredients.id', '=', 'ingredient_menu_item.ingredient_id')
            ->select(
                'sales.transaction_code as reference',
                'sales.created_at as movement_date',
                'ingredients.id as item_id',
                DB::raw('"ingredient" as item_type'),
                'ingredients.name',
                DB::raw('"out" as movement_direction'),
                DB::raw('sale_items.quantity * ingredient_menu_item.quantity as quantity'),
                DB::raw('ingredients.unit as unit'), // Tambahkan kolom unit dari ingredients
                DB::raw('NULL as satuan_ffne'), // Kolom satuan_ffne dikosongkan
                DB::raw('"Pemakaian Penjualan" as description')
            )
            ->where('sales.status', 'completed')
            ->whereBetween(DB::raw('DATE(sales.created_at)'), [$startDate, $endDate]);

        // Query Barang Keluar - Rusak (FFNE only) - tambahkan kolom satuan_ffne
        $stockOutDamage = DB::table('ffnes')
            ->select(
                DB::raw('"FFNE-R-" || ffnes.id as reference'),
                'ffnes.updated_at as movement_date',
                'ffnes.id as item_id',
                DB::raw('"ffne" as item_type'),
                'ffnes.nama_ffne as name',
                DB::raw('"out" as movement_direction'),
                DB::raw('1 as quantity'),
                DB::raw('NULL as unit'), // Kolom unit dikosongkan
                'ffnes.satuan_ffne as satuan_ffne', // Tambahkan kolom satuan_ffne dari ffnes
                DB::raw('"Barang Rusak" as description')
            )
            ->where('ffnes.kondisi_ffne', 1)
            ->whereBetween(DB::raw('DATE(ffnes.updated_at)'), [$startDate, $endDate]);

        // Filter berdasarkan item_id dan item_type
        if ($itemId && $itemType) {
            if ($itemType === 'ingredient') {
                $stockIn->where('ingredients.id', $itemId);
                $stockOutSales->where('ingredients.id', $itemId);
                $stockOutDamage->whereRaw('1 = 0');
            } elseif ($itemType === 'ffne') {
                $stockIn->whereRaw('1 = 0');
                $stockOutSales->whereRaw('1 = 0');
                $stockOutDamage->where('ffnes.id', $itemId);
            }
        }

        // Gabungkan query berdasarkan movement_type
        if ($movementType === 'in') {
            $query = $stockIn;
        } elseif ($movementType === 'out') {
            $query = $stockOutSales->unionAll($stockOutDamage);
        } else {
            $query = $stockIn->unionAll($stockOutSales)->unionAll($stockOutDamage);
        }

        // Wrap final query untuk ordering
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
            abort(404, 'File tidak dapat diakses.');
        }
    }
}
