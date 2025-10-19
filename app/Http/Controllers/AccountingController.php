<?php
namespace App\Http\Controllers;

use App\Exports\SalesReportExport;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:20',
            'address'        => 'nullable|string',
            'credit_limit'   => 'required|numeric|min:0',
        ]);

        Supplier::updateOrCreate(['id' => $request->id], $validated);

        $message = $request->id ? 'Data supplier berhasil diperbarui.' : 'Supplier baru berhasil ditambahkan.';
        return response()->json(['status' => 'success', 'message' => $message]);
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

}
