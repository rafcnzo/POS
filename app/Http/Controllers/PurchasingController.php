<?php
namespace App\Http\Controllers;

use App\Models\GoodsReceipt;
use App\Models\Ingredient;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Setting;
use App\Models\StoreRequest;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PurchasingController extends Controller
{
    public function purchaseOrderIndex()
    {
        $purchaseOrders = PurchaseOrder::with(['supplier', 'storeRequest'])->latest()->get();
        $suppliers      = Supplier::orderBy('name')->get();
        $storeRequests  = StoreRequest::where('status', 'proses')->get();
        $ingredients    = Ingredient::orderBy('name')->get(['id', 'name', 'unit']);

        return view('purchasing.purchase_orders.index', compact('purchaseOrders', 'suppliers', 'storeRequests', 'ingredients'));
    }

    public function purchaseOrderSubmit(Request $request)
    {
        $validated = $request->validate([
            'supplier_id'           => 'required|exists:suppliers,id',
            'store_request_id'      => 'nullable|exists:store_requests,id',
            'notes'                 => 'nullable|string',
            'payment_type'          => 'required|in:cash,tempo',
            'items'                 => 'required|array|min:1',
            'items.*.ingredient_id' => 'required|exists:ingredients,id',
            'items.*.quantity'      => 'required|numeric|min:0.01',
            'items.*.price'         => 'required|numeric|min:0',
        ], [
            'items.required'     => 'Minimal harus ada 1 item barang dalam PO.',
            'items.*.*.required' => 'Semua detail item (bahan, qty, harga) wajib diisi.',
        ]);

        $supplier      = Supplier::findOrFail($validated['supplier_id']);
        $paymentMethod = $validated['payment_type'];

        $totalAmount = 0;
        foreach ($validated['items'] as $item) {
            if (! is_numeric($item['quantity']) || ! is_numeric($item['price'])) {
                throw ValidationException::withMessages(['items' => 'Qty atau Harga item tidak valid.']);
            }
            $totalAmount += $item['quantity'] * $item['price'];
        }

        if ($paymentMethod === Supplier::TYPE_TEMPO) { // Gunakan constant dari model Supplier
                                                           // Cek Tipe Supplier
            if ($supplier->type !== Supplier::TYPE_TEMPO) {
                throw ValidationException::withMessages(['supplier_id' => 'Supplier ini tidak mendukung pembayaran tempo.']);
            }
            $currentCreditLimit = Supplier::find($supplier->id)->credit_limit;
            if ($totalAmount > $currentCreditLimit) {
                throw ValidationException::withMessages(['payment_type' => "Total PO (" . number_format($totalAmount, 0, ',', '.') . ") melebihi sisa limit kredit (" . number_format($currentCreditLimit, 0, ',', '.') . ")."]);
            }
        }

        try {
            $result = DB::transaction(function () use ($validated, $supplier, $paymentMethod, $totalAmount) {
                $date       = Carbon::now()->format('Ymd');
                $latestPo   = PurchaseOrder::where('po_number', 'like', "PO-{$date}-%")->lockForUpdate()->latest('id')->first(); // Lock for update
                $nextNumber = $latestPo ? intval(substr($latestPo->po_number, -4)) + 1 : 1;
                $poNumber   = "PO-{$date}-" . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

                $purchaseOrder = PurchaseOrder::create([
                    'po_number'              => $poNumber,
                    'supplier_id'            => $validated['supplier_id'],
                    'store_request_id'       => $validated['store_request_id'] ?? null,
                    'user_id'                => Auth::id(),
                    'order_date'             => Carbon::now()->toDateString(), // <-- Tanggal order saat ini
                    'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
                    'payment_type'           => $paymentMethod, // <-- Simpan payment method
                    'total_amount'           => $totalAmount,   // <-- Total dari backend
                    'status'                 => 'diproses',
                    'notes'                  => $validated['notes'] ?? null,
                ]);

                foreach ($validated['items'] as $item) {
                    $purchaseOrder->items()->create([
                        'ingredient_id' => $item['ingredient_id'],
                        'quantity'      => $item['quantity'],
                        'price'         => $item['price'],
                        'subtotal'      => $item['quantity'] * $item['price'],
                    ]);
                }

                if ($purchaseOrder->store_request_id) {
                    $storeRequest = StoreRequest::find($purchaseOrder->store_request_id);
                    if ($storeRequest && $storeRequest->status === 'proses') {
                        $storeRequest->status = 'po'; // Status 'po'
                        $storeRequest->save();
                    }
                }

                return $purchaseOrder;
            });

            return response()->json([
                'status'            => 'success',
                'message'           => 'Purchase Order ' . $result->po_number . ' berhasil dibuat.',
                'po_number'         => $result->po_number,
                'purchase_order_id' => $result->id,
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(), // Pesan error utama
                'errors'  => $e->errors(),     // Detail error per field
            ], 422);                       // Status 422 Unprocessable Entity
        } catch (Throwable $e) {
            \Log::error("Error creating PO: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal membuat Purchase Order: Terjadi kesalahan internal server.',
            ], 500);
        }
    }

    public function purchaseOrderDestroy(PurchaseOrder $purchaseOrder)
    {
        try {
            DB::transaction(function () use ($purchaseOrder) {
                $supplier = $purchaseOrder->supplier; // Ambil supplier sebelum PO dihapus

                // Kembalikan status Store Request jika ada
                if ($purchaseOrder->store_request_id) {
                    $storeRequest = StoreRequest::find($purchaseOrder->store_request_id);
                    // Hanya kembalikan jika SR masih berstatus 'po' (belum diproses lebih lanjut)
                    if ($storeRequest && $storeRequest->status === 'po') {
                        $storeRequest->status = 'proses';
                        $storeRequest->save();
                    }
                }
                $purchaseOrder->delete();
            });

            return response()->json([
                'status'  => 'success',
                'message' => 'Purchase Order berhasil dihapus.',
            ]);

        } catch (Throwable $e) {
            \Log::error("Error deleting PO: " . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menghapus Purchase Order.',
            ], 500);
        }
    }

    public function getSupplierDetails(Supplier $supplier) // Gunakan Route Model Binding
    {
        return response()->json([
            'id'           => $supplier->id,
            'name'         => $supplier->name,
            'type'         => $supplier->type,
            'credit_limit' => (float) $supplier->credit_limit,                                           // Kirim sebagai float
            'jatuh_tempo1' => $supplier->jatuh_tempo1 ? $supplier->jatuh_tempo1->format('d M Y') : null, // Format tanggal jika perlu
            'jatuh_tempo2' => $supplier->jatuh_tempo2 ? $supplier->jatuh_tempo2->format('d M Y') : null,
        ]);
    }

    public function getStoreRequestItems($storeRequestId)
    {
        $storeRequest = StoreRequest::find($storeRequestId);

        if (! $storeRequest) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Store Request tidak ditemukan.',
            ], 404);
        }

        // Eager load items with their ingredient relation
        $items = \App\Models\StoreRequestItem::with('ingredient')
            ->where('store_request_id', $storeRequestId)
            ->get()
            ->map(function ($item) {
                return [
                    'ingredient_id'   => $item->ingredient_id,
                    'ingredient_name' => optional($item->ingredient)->name ?? '-',
                    'quantity'        => $item->requested_quantity,
                    'issued_quantity' => $item->issued_quantity,
                ];
            })
            ->toArray();

        return response()->json([
            'status' => 'success',
            'items'  => $items,
        ]);
    }

    public function purchaseOrderShow($id)
    {
        $purchaseOrder = PurchaseOrder::with([
            'supplier',
            'user',
            'items.ingredient',
            'storeRequest',
        ])->find($id);

        if (! $purchaseOrder) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Purchase Order tidak ditemukan',
            ], 404);
        }

        // Format items
        $items = $purchaseOrder->items->map(function ($item) {
            return [
                'id'              => $item->id,
                'ingredient_id'   => $item->ingredient_id,
                'ingredient_name' => optional($item->ingredient)->name ?? '-',
                'quantity'        => $item->quantity,
                'price'           => $item->price,
            ];
        });

        return response()->json([
            'status' => 'success',
            'po'     => [
                'id'            => $purchaseOrder->id,
                'po_number'     => $purchaseOrder->po_number,
                'order_date'    => $purchaseOrder->order_date,
                'status'        => $purchaseOrder->status,
                'notes'         => $purchaseOrder->notes,
                'payment_type'  => $purchaseOrder->payment_type,
                'total_amount'  => $items->sum(function ($i) {return $i['quantity'] * $i['price'];}),
                'supplier'      => [
                    'id'   => optional($purchaseOrder->supplier)->id,
                    'name' => optional($purchaseOrder->supplier)->name,
                ],
                'store_request' => $purchaseOrder->storeRequest ? [
                    'id'             => $purchaseOrder->storeRequest->id,
                    'request_number' => $purchaseOrder->storeRequest->request_number,
                ] : null,
                'items'         => $items,
            ],
        ]);
    }

    public function purchaseOrderPrint($id)
    {
        $purchaseOrder = PurchaseOrder::with([
            'supplier',
            'user',
            'items.ingredient',
            'storeRequest',
        ])->findOrFail($id);
        $settings = Setting::pluck('value', 'key')->toArray();
        $pdf = Pdf::loadView(
            'purchasing.purchase_orders._print',
            compact('purchaseOrder', 'settings')
        )->setPaper('a4', 'landscape');
        return $pdf->stream('purchase_order_' . $purchaseOrder->po_number . '.pdf');
    }

    public function penerimaanbarangIndex()
    {
        $receipts       = GoodsReceipt::latest()->get();
        $purchaseOrders = PurchaseOrder::orderBy('order_date', 'desc')->get();
        $ingredients    = Ingredient::orderBy('name')->get();

        return view('purchasing.penerimaanbarang.index', compact('receipts', 'purchaseOrders', 'ingredients'));
    }

    public function penerimaanbarangSubmit(Request $request)
    {
        $validated = $request->validate([
            'purchase_order_id'              => 'required|exists:purchase_orders,id',
            'receipt_date'                   => 'required|date',
            'proof_document'                 => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'items'                          => 'required|array|min:1',
            'items.*.purchase_order_item_id' => 'required|exists:purchase_order_items,id',
            'items.*.quantity_received'      => 'required|numeric|min:0.01',
            'items.*.quantity_rejected'      => 'nullable|numeric|min:0',
            'items.*.notes'                  => 'nullable|string|max:500',
        ]);

        $updatedStocks = [];

        try {
            DB::transaction(function () use ($request, $validated, &$updatedStocks) {
                // Generate receipt number
                $date = now()->format('Ymd');

                // Generate nomor penerimaan barang (receipt_number) hari ini
                $lastReceipt = GoodsReceipt::whereDate('created_at', now()->toDateString())
                    ->orderByDesc('id')
                    ->first();

                $urut = 1;
                if ($lastReceipt && preg_match('/^GR-' . $date . '-(\d+)$/', $lastReceipt->receipt_number, $matches)) {
                    $urut = ((int) $matches[1]) + 1;
                }

                $receipt_number = 'GR-' . $date . '-' . str_pad($urut, 3, '0', STR_PAD_LEFT);

                // Handle file bukti
                $proofPath = null;
                if ($request->hasFile('proof_document')) {
                    $proofPath = $request->file('proof_document')->store('proofs', 'public');
                }

                // Ambil purchase order dan supplier secara eager load
                $purchaseOrder = PurchaseOrder::with('supplier')->find($validated['purchase_order_id']);
                if (! $purchaseOrder) {
                    throw new \Exception("Purchase Order tidak ditemukan.");
                }

                // LOGIKA BARU: Hanya jika pembayaran Tempo, kurangi limit kredit supplier
                if (
                    isset($purchaseOrder->payment_type) &&
                    $purchaseOrder->payment_type === PurchaseOrder::PAYMENT_TEMPO &&
                    $purchaseOrder->supplier
                ) {
                    $poTotalAmount = $purchaseOrder->total_amount;
                    Supplier::where('id', $purchaseOrder->supplier_id)->decrement('credit_limit', $poTotalAmount);
                }

                // Buat header GoodsReceipt
                $goodsReceipt = GoodsReceipt::create([
                    'receipt_number'    => $receipt_number,
                    'purchase_order_id' => $validated['purchase_order_id'],
                    'receipt_date'      => $validated['receipt_date'],
                    'user_id'           => auth()->id(),
                    'proof_document'    => $proofPath,
                ]);

                // Update status PO jika masih 'diproses' menjadi 'diterima'
                if ($purchaseOrder->status === 'diproses') {
                    $purchaseOrder->status = 'diterima';
                    $purchaseOrder->save();
                }

                // Update stok bahan (ingredients)
                foreach ($validated['items'] as $itemData) {
                    // Create item record
                    $receiptItem = GoodsReceiptItem::create([
                        'goods_receipt_id'       => $goodsReceipt->id,
                        'purchase_order_item_id' => $itemData['purchase_order_item_id'],
                        'quantity_received'      => $itemData['quantity_received'],
                        'quantity_rejected'      => $itemData['quantity_rejected'] ?? 0,
                        'notes'                  => $itemData['notes'] ?? null,
                    ]);

                    // Update stock ingredient
                    $poItem = PurchaseOrderItem::with('ingredient')->find($itemData['purchase_order_item_id']);

                    if ($poItem && $poItem->ingredient) {
                        $ingredient = $poItem->ingredient;
                        $oldStock   = $ingredient->stock;

                        $ingredient->increment('stock', $itemData['quantity_received']);
                        $newStock = $ingredient->fresh()->stock;

                        $updatedStocks[] = [
                            'ingredient_id'   => $ingredient->id,
                            'ingredient_name' => $ingredient->name,
                            'qty_received'    => $itemData['quantity_received'],
                            'qty_rejected'    => $itemData['quantity_rejected'] ?? 0,
                            'stock_before'    => $oldStock,
                            'stock_after'     => $newStock,
                        ];
                        // Jika ingin buat detail GoodsReceiptItem, bisa buat di sini (jika tabelnya ada)
                        // $goodsReceipt->items()->create([
                        //     'ingredient_id' => $ingredient->id,
                        //     'quantity_received' => $itemData['quantity_received'],
                        // ]);
                    }
                }

                // Update PO status
                $purchaseOrder = PurchaseOrder::find($validated['purchase_order_id']);
                if ($purchaseOrder) {
                    $purchaseOrder->update(['status' => 'diterima']);
                }
            });
        } catch (\Exception $e) {
            \Log::error('Error penerimaan barang: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menyimpan: ' . $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'status'         => 'success',
            'message'        => 'Penerimaan barang berhasil dicatat dan stok telah diperbarui.',
            'updated_stocks' => $updatedStocks, // Berisi list ingredient yang stoknya diperbarui beserta qty
        ]);
    }

    public function penerimaanbarangDestroy(GoodsReceipt $penerimaanbarang)
    {
        try {
            DB::transaction(function () use ($penerimaanbarang) {
                $purchaseOrder = PurchaseOrder::with('supplier')->find($penerimaanbarang->purchase_order_id);

                if ($penerimaanbarang->proof_document) {
                    Storage::disk('public')->delete($penerimaanbarang->proof_document);
                }

                $penerimaanbarang->delete();

                if ($purchaseOrder && $purchaseOrder->payment_method === PurchaseOrder::PAYMENT_TEMPO && $purchaseOrder->supplier) {
                    Supplier::where('id', $purchaseOrder->supplier_id)->increment('credit_limit', $purchaseOrder->total_amount);
                    $purchaseOrder->update(['status' => 'dibatalkan']);
                }

                // Ini lebih kompleks, Anda perlu tabel GoodsReceiptItem untuk tahu berapa qty yg diterima
                // foreach($penerimaanbarang->items as $item) {
                //     Ingredient::where('id', $item->ingredient_id)->decrement('stock', $item->quantity_received);
                // }

            });
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal menghapus: ' . $e->getMessage()], 500);
        }

        return response()->json(['status' => 'success', 'message' => 'Data penerimaan barang berhasil dihapus.']);
    }

    public function penerimaanbarangShow($id)
    {
        $penerimaan  = GoodsReceipt::with(['purchaseOrder', 'user'])->find($id);
        $detailItems = GoodsReceiptItem::with(['purchaseOrderItem.ingredient'])
            ->where('goods_receipt_id', $id)
            ->get();

        if (! $penerimaan) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data penerimaan barang tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'status'       => 'success',
            'penerimaan'   => [
                'id'             => $penerimaan->id,
                'receipt_number' => $penerimaan->receipt_number,
                'receipt_date'   => $penerimaan->receipt_date,
                'user'           => $penerimaan->user ? [
                    'id'   => $penerimaan->user->id,
                    'name' => $penerimaan->user->name,
                ] : null,
                'purchase_order' => $penerimaan->purchaseOrder ? [
                    'id'        => $penerimaan->purchaseOrder->id,
                    'po_number' => $penerimaan->purchaseOrder->po_number,
                ] : null,
                'proof_document' => $penerimaan->proof_document,
            ],
            'detail_items' => $detailItems->map(function ($item) {
                return [
                    'id'                     => $item->id,
                    'purchase_order_item_id' => $item->purchase_order_item_id,
                    'ingredient'             => $item->purchaseOrderItem && $item->purchaseOrderItem->ingredient ? [
                        'id'   => $item->purchaseOrderItem->ingredient->id,
                        'name' => $item->purchaseOrderItem->ingredient->name,
                    ] : null,
                    'quantity_received'      => $item->quantity_received,
                    'quantity_rejected'      => $item->quantity_rejected,
                    'notes'                  => $item->notes,
                ];
            })->all(),
        ]);
    }

    public function getPoItems($poId)
    {
        $purchaseOrder = PurchaseOrder::with([
            'items.ingredient',
        ])->find($poId);

        if (! $purchaseOrder) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Purchase Order tidak ditemukan.',
            ], 404);
        }

        $items = $purchaseOrder->items->map(function ($item) {
            return [
                'purchase_order_item_id' => $item->id,
                'ingredient_id'          => $item->ingredient_id,
                'ingredient_name'        => optional($item->ingredient)->name ?? '-',
                'quantity_ordered'       => $item->quantity,
                'unit'                   => optional($item->ingredient)->unit ?? '',
                'cost_price'             => $item->price,
            ];
        })->all();

        return response()->json([
            'status' => 'success',
            'items'  => $items,
        ]);
    }
}
