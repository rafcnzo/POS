<?php
namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\StoreRequest;
use App\Models\Supplier;
use Illuminate\Http\Request;

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

}
