<?php
namespace App\Http\Controllers;

use App\Exports\SalesReportExport;
use App\Models\Karyawan;
use App\Models\Payroll;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
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
            'jatuh_tempo1'   => 'nullable|required_if:type,' . Supplier::TYPE_TEMPO . '|date', // Wajib jika Tempo
            'jatuh_tempo2'   => 'nullable|date|after_or_equal:jatuh_tempo1',                   // Opsional, harus setelah tempo1 jika diisi
        ], [
            // Pesan error custom
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

    public function creditLimitMonitoring()
    {
        $suppliers = Supplier::where('credit_limit', '>', 0)
            ->with(['purchaseOrders' => function ($query) {
                $query->whereIn('status', ['unpaid', 'partially_paid']);
            }])
            ->get();

        $suppliersData = $suppliers->map(function ($supplier) {
            $currentDebt = $supplier->purchaseOrders->sum(function ($order) {
                return $order->total_amount - $order->paid_amount;
            });

            $remainingCredit = $supplier->credit_limit - $currentDebt;

            $usagePercentage = ($supplier->credit_limit > 0) ? ($currentDebt / $supplier->credit_limit) * 100 : 0;

            return (object) [
                'name'             => $supplier->name,
                'credit_limit'     => $supplier->credit_limit,
                'current_debt'     => $currentDebt,
                'remaining_credit' => $remainingCredit,
                'usage_percentage' => round($usagePercentage, 2),
            ];
        });

        return view('accounting.suppliers.credit_limit', ['suppliersData' => $suppliersData]);
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

        $fileName = 'laporan-penjualan-' . Str::slug($reportTitle) . '.xlsx';
        return Excel::download(new SalesReportExport($sales), $fileName);
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
            ->where('status', 'completed')
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
