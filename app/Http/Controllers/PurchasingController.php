<?php
namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\StoreRequest;
use App\Models\Supplier;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Setting;
use App\Models\Ingredient;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchasingController extends Controller
{
    public function purchaseOrderIndex()
    {
        $purchaseOrders = PurchaseOrder::with(['supplier', 'storeRequest'])->latest()->get();
        $suppliers      = Supplier::orderBy('name')->get();
        $storeRequests  = StoreRequest::where('status', 'proses')->get();

        return view('purchasing.purchase_orders.index', compact('purchaseOrders', 'suppliers', 'storeRequests'));
    }

    public function purchaseOrderSubmit(Request $request)
    {
        $validated = $request->validate([
            'supplier_id'           => 'required|exists:suppliers,id',
            'store_request_id'      => 'nullable|exists:store_requests,id',
            // 'order_date'        => 'required|date', // Tidak usah divalidasi karena akan di-generate otomatis
            'notes'                 => 'nullable|string',
            'items'                 => 'required|array|min:1',
            'items.*.ingredient_id' => 'required|exists:ingredients,id',
            'items.*.quantity'      => 'required|numeric|min:0.01',
            'items.*.price'         => 'required|numeric|min:0',
        ]);

        try {
            $result = DB::transaction(function () use ($validated) {
                $date = Carbon::now()->format('Ymd');
                $latestPo = PurchaseOrder::where('po_number', 'like', "PO-{$date}-%")->latest('id')->first();
                $nextNumber = $latestPo ? intval(substr($latestPo->po_number, -4)) + 1 : 1;
                $poNumber = "PO-{$date}-" . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

                $purchaseOrder = PurchaseOrder::create([
                    'po_number'        => $poNumber,
                    'supplier_id'      => $validated['supplier_id'],
                    'store_request_id' => $validated['store_request_id'] ?? null,
                    'user_id'          => Auth::id(),
                    'order_date'       => Carbon::now()->toDateString(),
                    'status'           => 'diproses',
                    'notes'            => $validated['notes'] ?? null,
                    'total_amount'     => 0,
                ]);

                $totalAmount = 0;

                foreach ($validated['items'] as $item) {
                    $subtotal = $item['quantity'] * $item['price'];
                    $purchaseOrder->items()->create([
                        'ingredient_id' => $item['ingredient_id'],
                        'quantity'      => $item['quantity'],
                        'price'         => $item['price'],
                        'subtotal'      => $subtotal,
                    ]);
                    $totalAmount += $subtotal;
                }

                $purchaseOrder->total_amount = $totalAmount;
                $purchaseOrder->save();

                if ($purchaseOrder->store_request_id) {
                    $storeRequest = StoreRequest::find($purchaseOrder->store_request_id);
                    if ($storeRequest) {
                        // Update status sesuai enum ('proses', 'po'); gunakan 'po'
                        $storeRequest->status = 'po';
                        $storeRequest->save();
                    }
                }

                return $purchaseOrder;
            });
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal membuat Purchase Order: ' . $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'status'            => 'success',
            'message'           => 'Purchase Order ' . $result->po_number . ' berhasil dibuat.',
            'po_number'         => $result->po_number,
            'purchase_order_id' => $result->id,
        ]);
    }

    public function purchaseOrderDestroy(PurchaseOrder $purchaseOrder)
    {
        try {
            DB::transaction(function () use ($purchaseOrder) {
                if ($purchaseOrder->store_request_id) {
                    $storeRequest = StoreRequest::find($purchaseOrder->store_request_id);
                    if ($storeRequest) {
                        $storeRequest->status = 'proses';
                        $storeRequest->save();
                    }
                }

                $purchaseOrder->delete();
            });
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menghapus Purchase Order: ' . $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Purchase Order berhasil dihapus.',
        ]);
    }

    public function getStoreRequestItems($storeRequestId)
    {
        $storeRequest = StoreRequest::find($storeRequestId);

        if (!$storeRequest) {
            return response()->json([
                'status' => 'error',
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
            'items' => $items,
        ]);
    }

    public function purchaseOrderShow($id)
    {
        $purchaseOrder = PurchaseOrder::with([
            'supplier',
            'user',
            'items.ingredient',
            'storeRequest'
        ])->find($id);

        if (!$purchaseOrder) {
            return response()->json([
                'status' => 'error',
                'message' => 'Purchase Order tidak ditemukan'
            ], 404);
        }

        // Format items
        $items = $purchaseOrder->items->map(function($item) {
            return [
                'id' => $item->id,
                'ingredient_id' => $item->ingredient_id,
                'ingredient_name' => optional($item->ingredient)->name ?? '-',
                'quantity' => $item->quantity,
                'price' => $item->price,
            ];
        });

        return response()->json([
            'status' => 'success',
            'po' => [
                'id' => $purchaseOrder->id,
                'po_number' => $purchaseOrder->po_number,
                'order_date' => $purchaseOrder->order_date,
                'status' => $purchaseOrder->status,
                'notes' => $purchaseOrder->notes,
                'total_amount' => $items->sum(function($i) { return $i['quantity'] * $i['price']; }),
                'supplier' => [
                    'id' => optional($purchaseOrder->supplier)->id,
                    'name' => optional($purchaseOrder->supplier)->name,
                ],
                'store_request' => $purchaseOrder->storeRequest ? [
                    'id' => $purchaseOrder->storeRequest->id,
                    'request_number' => $purchaseOrder->storeRequest->request_number,
                ] : null,
                'items' => $items,
            ],
        ]);
    }

    public function purchaseOrderPrint($id)
    {
        $purchaseOrder = PurchaseOrder::with([
            'supplier',
            'user',
            'items.ingredient',
            'storeRequest'
        ])->findOrFail($id);

        $setting = Setting::all();

        return view('purchasing.purchase_orders._print', compact('purchaseOrder', 'setting'));
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
            'purchase_order_id'         => 'required|exists:purchase_orders,id',
            'receipt_date'              => 'required|date',
            'proof_document'            => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'items'                     => 'required|array|min:1',
            'items.*.ingredient_id'     => 'required|exists:ingredients,id',
            'items.*.quantity_received' => 'required|numeric|min:0.01',
        ]);

        $updatedStocks = [];

        try {
            DB::transaction(function () use ($request, $validated, &$updatedStocks) {
                $date           = now()->format('Ymd');
                $receipt_number = 'GR-' . $date . '-';

                $proofPath = null;
                if ($request->hasFile('proof_document')) {
                    $proofPath = $request->file('proof_document')->store('proofs', 'public');
                }

                // Buat only header, tanpa detail item
                $goodsReceipt = GoodsReceipt::create([
                    'receipt_number'    => $receipt_number,
                    'purchase_order_id' => $validated['purchase_order_id'],
                    'receipt_date'      => $validated['receipt_date'],
                    'user_id'           => auth()->id(),
                    'proof_document'    => $proofPath,
                ]);

                // Update status purchase order menjadi 'diterima'
                $purchaseOrder = PurchaseOrder::find($validated['purchase_order_id']);
                if ($purchaseOrder) {
                    $purchaseOrder->status = 'diterima';
                    $purchaseOrder->save();
                }

                // Update stock ingredients dan catat update-nya
                foreach ($validated['items'] as $itemData) {
                    $ingredient = Ingredient::find($itemData['ingredient_id']);
                    if ($ingredient) {
                        $oldStock = $ingredient->stock;
                        $ingredient->increment('stock', $itemData['quantity_received']);
                        $newStock = $ingredient->fresh()->stock;
                        $updatedStocks[] = [
                            'ingredient_id'   => $ingredient->id,
                            'ingredient_name' => $ingredient->name,
                            'qty_increased'   => $itemData['quantity_received'],
                            'stock_before'    => $oldStock,
                            'stock_after'     => $newStock,
                        ];
                    }
                }
            });
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal menyimpan: ' . $e->getMessage()], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Penerimaan barang berhasil dicatat dan stok telah diperbarui.',
            'updated_stocks' => $updatedStocks, // Berisi list ingredient yang stoknya diperbarui beserta qty
        ]);
    }

    public function penerimaanbarangDestroy(GoodsReceipt $penerimaanbarang)
    {
        try {
            DB::transaction(function () use ($penerimaanbarang) {
                if ($penerimaanbarang->proof_document) {
                    Storage::disk('public')->delete($penerimaanbarang->proof_document);
                }

                $penerimaanbarang->delete();
            });
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal menghapus: ' . $e->getMessage()], 500);
        }

        return response()->json(['status' => 'success', 'message' => 'Data penerimaan barang berhasil dihapus.']);
    }

    public function penerimaanbarangShow($id)
    {
        $penerimaan = GoodsReceipt::with(['purchaseOrder', 'user'])->find($id);

        if (!$penerimaan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data penerimaan barang tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'penerimaan' => [
                'id' => $penerimaan->id,
                'receipt_number' => $penerimaan->receipt_number,
                'receipt_date' => $penerimaan->receipt_date,
                'user' => $penerimaan->user ? [
                    'id' => $penerimaan->user->id,
                    'name' => $penerimaan->user->name,
                ] : null,
                'purchase_order' => $penerimaan->purchaseOrder ? [
                    'id' => $penerimaan->purchaseOrder->id,
                    'po_number' => $penerimaan->purchaseOrder->po_number,
                ] : null,
                'proof_document' => $penerimaan->proof_document,
            ]
        ]);
    }

    public function getPoItems($poId)
    {
        $purchaseOrder = PurchaseOrder::with([
            'items.ingredient'
        ])->find($poId);

        if (!$purchaseOrder) {
            return response()->json([
                'status' => 'error',
                'message' => 'Purchase Order tidak ditemukan.'
            ], 404);
        }

        $items = $purchaseOrder->items->map(function($item) {
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
            'items'  => $items
        ]);
    }
}
