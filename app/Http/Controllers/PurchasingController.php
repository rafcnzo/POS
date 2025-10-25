<?php
namespace App\Http\Controllers;

use App\Models\Ffne;
use App\Models\FfneStockAdj;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Ingredient;
use App\Models\IngredientStockAdjustment;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Setting;
use App\Models\StoreRequest;
use App\Models\StoreRequestItem;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class PurchasingController extends Controller
{
    public function purchaseOrderIndex()
    {
        $purchaseOrders = PurchaseOrder::with(['supplier', 'storeRequest'])->latest()->get();
        $suppliers      = Supplier::orderBy('name')->get();
        $storeRequests  = StoreRequest::where('status', 'proses')->get();
        $ingredients    = Ingredient::orderBy('name')->get(['id', 'name', 'unit', 'cost_price']);
        $ffnes          = \App\Models\Ffne::where('kategori_ffne', 'Barang Habis Pakai') // Hanya yg habis pakai
            ->orderBy('nama_ffne')
            ->get(['id', 'nama_ffne', 'satuan_ffne', 'harga']);

        // Gabungkan keduanya untuk dropdown
        $bahanbakus = $ingredients->map(function ($item) {
            return [
                'id'         => $item->id,
                'name'       => $item->name,
                'unit'       => $item->unit,
                'cost_price' => $item->cost_price ?? 0,
                'type'       => Ingredient::class, // Kirim Nama Kelas Lengkap
            ];
        })->concat(
            $ffnes->map(function ($item) {
                return [
                    'id'         => $item->id,
                    'name'       => $item->nama_ffne,
                    'unit'       => $item->satuan_ffne,
                    'cost_price' => $item->harga ?? 0,
                    'type'       => Ffne::class, // Kirim Nama Kelas Lengkap
                ];
            })
        )->sortBy('name'); // Urutkan berdasarkan nama gabungan

        return view('purchasing.purchase_orders.index', compact(
            'purchaseOrders',
            'suppliers',
            'storeRequests',
            'ingredients',
            'bahanbakus'
        ));
    }

    public function purchaseOrderSubmit(Request $request)
    {
        $validated = $request->validate([
            'supplier_id'            => 'required|exists:suppliers,id',
            'store_request_id'       => 'nullable|exists:store_requests,id',
            'notes'                  => 'nullable|string',
            'payment_type'           => 'required|in:petty_cash,tempo',
            'expected_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'items'                  => 'required|array|min:1',
            'items.*.item_id'        => 'required|integer',
            'items.*.item_type'      => ['required', Rule::in([Ingredient::class, Ffne::class])],
            'items.*.quantity'       => 'required|numeric|min:0.01',
            'items.*.price'          => 'required|numeric|min:0',
        ], [
            'items.required'             => 'Minimal harus ada 1 item barang.',
            'items.*.item_id.required'   => 'Item barang wajib dipilih.',
            'items.*.item_type.required' => 'Tipe item tidak valid.',
        ]);

        $supplier      = Supplier::findOrFail($validated['supplier_id']);
        $paymentMethod = $validated['payment_type'];

        $totalAmount = 0;
        foreach ($validated['items'] as $item) {
            $totalAmount += $item['quantity'] * $item['price'];
        }

        if ($paymentMethod === Supplier::TYPE_TEMPO) {
            if ($supplier->type !== Supplier::TYPE_TEMPO) {
                throw ValidationException::withMessages(['supplier_id' => 'Supplier ini tidak mendukung pembayaran tempo.']);
            }
            $currentCreditLimit = Supplier::find($supplier->id)->credit_limit;
            if ($totalAmount > $currentCreditLimit) {
                throw ValidationException::withMessages([
                    'payment_type' => "Total PO (" . number_format($totalAmount, 0, ',', '.') . ") melebihi sisa limit kredit (" . number_format($currentCreditLimit, 0, ',', '.') . ").",
                ]);
            }
        }

        try {
            $result = \DB::transaction(function () use ($validated, $supplier, $paymentMethod, $totalAmount) {
                $date       = Carbon::now()->format('Ymd');
                $latestPo   = PurchaseOrder::where('po_number', 'like', "PO-{$date}-%")->lockForUpdate()->latest('id')->first();
                $nextNumber = $latestPo ? intval(substr($latestPo->po_number, -4)) + 1 : 1;
                $poNumber   = "PO-{$date}-" . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

                $purchaseOrder = PurchaseOrder::create([
                    'po_number'              => $poNumber,
                    'supplier_id'            => $validated['supplier_id'],
                    'store_request_id'       => $validated['store_request_id'] ?? null,
                    'user_id'                => Auth::id(),
                    'order_date'             => now(),
                    'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
                    'payment_type'           => $paymentMethod,
                    'total_amount'           => $totalAmount,
                    'status'                 => 'diproses',
                    'notes'                  => $validated['notes'] ?? null,
                ]);

                foreach ($validated['items'] as $item) {
                    if (in_array($item['item_type'], [Ingredient::class, Ffne::class])) {
                        $purchaseOrder->items()->create([
                            'itemable_id'   => $item['item_id'],
                            'itemable_type' => $item['item_type'],
                            'quantity'      => $item['quantity'],
                            'price'         => $item['price'],
                            'subtotal'      => $item['quantity'] * $item['price'],
                        ]);
                    }
                }

                if ($purchaseOrder->store_request_id) {
                    $storeRequest = StoreRequest::find($purchaseOrder->store_request_id);
                    if ($storeRequest && $storeRequest->status === 'proses') {
                        $storeRequest->status = 'po';
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
                'message' => $e->getMessage(),
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            \Log::error("Error creating PO: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal membuat Purchase Order: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function purchaseOrderDestroy(PurchaseOrder $purchaseOrder)
    {
        try {
            \DB::transaction(function () use ($purchaseOrder) {
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

        $items = StoreRequestItem::with('itemable') // <-- UBAH DI SINI
            ->where('store_request_id', $storeRequestId)
            ->get()
            ->map(function ($item) {
                if (! $item->itemable) {
                    return null; // Lewati item yang rusak/dihapus
                }

                // Cek tipenya
                if ($item->itemable instanceof Ingredient) {
                    return [
                        'item_id'   => $item->itemable_id,
                        'item_type' => Ingredient::class, // Kirim nama kelas
                        'item_name' => $item->itemable->name,
                        'item_unit' => $item->itemable->unit,
                        'quantity'  => $item->requested_quantity,
                    ];
                } elseif ($item->itemable instanceof Ffne) {
                    return [
                        'item_id'   => $item->itemable_id,
                        'item_type' => Ffne::class, // Kirim nama kelas
                        'item_name' => $item->itemable->nama_ffne,
                        'item_unit' => $item->itemable->satuan_ffne,
                        'quantity'  => $item->requested_quantity,
                    ];
                }
                return null;
            })
            ->filter()
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
            'items.itemable',
            'storeRequest',
        ])->find($id);

        if (! $purchaseOrder) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Purchase Order tidak ditemukan',
            ], 404);
        }

        // --- PERBAIKI FORMAT ITEMS ---
        $items = $purchaseOrder->items->map(function ($item) {
            $itemName = '-';
            $itemUnit = '-';
            $itemCode = null;
            $itemType = null;

            if ($item->itemable) {
                if ($item->itemable instanceof \App\Models\Ingredient) {
                    $itemName = $item->itemable->name;
                    $itemUnit = $item->itemable->unit;
                    $itemCode = $item->itemable->code ?? $item->itemable->id;
                    $itemType = \App\Models\Ingredient::class;
                } elseif ($item->itemable instanceof \App\Models\Ffne) {
                    $itemName = $item->itemable->nama_ffne;
                    $itemUnit = $item->itemable->satuan_ffne;
                    $itemCode = $item->itemable->kode_ffne;
                    $itemType = \App\Models\Ffne::class;
                }
            }

            return [
                'id'        => $item->id,
                'item_id'   => $item->itemable_id,
                'item_type' => $item->itemable_type,
                'item_code' => $itemCode,
                'item_name' => $itemName,
                'item_unit' => $itemUnit,
                'quantity'  => $item->quantity,
                'price'     => $item->price,
                'subtotal'  => $item->subtotal,
            ];
        });
        // --- AKHIR PERBAIKAN ---

        return response()->json([
            'status' => 'success',
            'po'     => [
                'id'            => $purchaseOrder->id,
                'po_number'     => $purchaseOrder->po_number,
                'order_date'    => $purchaseOrder->order_date,
                'status'        => $purchaseOrder->status,
                'notes'         => $purchaseOrder->notes,
                'payment_type'  => $purchaseOrder->payment_type,
                'total_amount'  => (float) $purchaseOrder->total_amount,
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
            'items.itemable',
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
        $purchaseOrders = PurchaseOrder::where('status', 'diproses')->orderBy('order_date', 'desc')->get();
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

        $updatedStocksLog = [];

        try {
            \DB::transaction(function () use ($request, $validated, &$updatedStocksLog) {
                $date = now()->format('Ymd');

                $lastReceipt = GoodsReceipt::whereDate('created_at', now()->toDateString())
                    ->orderByDesc('id')
                    ->first();

                $urut = 1;
                if ($lastReceipt && preg_match('/^GR-' . $date . '-(\d+)$/', $lastReceipt->receipt_number, $matches)) {
                    $urut = ((int) $matches[1]) + 1;
                }

                $receipt_number = 'GR-' . $date . '-' . str_pad($urut, 3, '0', STR_PAD_LEFT);

                $proofPath = null;
                if ($request->hasFile('proof_document')) {
                    $proofPath = $request->file('proof_document')->store('proofs', 'public');
                }

                $purchaseOrder = PurchaseOrder::with('supplier')->find($validated['purchase_order_id']);
                if (! $purchaseOrder) {
                    throw new \Exception("Purchase Order tidak ditemukan.");
                }

                if (
                    isset($purchaseOrder->payment_type) &&
                    $purchaseOrder->payment_type === PurchaseOrder::PAYMENT_TEMPO &&
                    $purchaseOrder->supplier
                ) {
                    $poTotalAmount = $purchaseOrder->total_amount;
                    Supplier::where('id', $purchaseOrder->supplier_id)->decrement('credit_limit', $poTotalAmount);
                }

                $goodsReceipt = GoodsReceipt::create([
                    'receipt_number'    => $receipt_number,
                    'purchase_order_id' => $validated['purchase_order_id'],
                    'receipt_date'      => $validated['receipt_date'],
                    'user_id'           => auth()->id(),
                    'proof_document'    => $proofPath,
                ]);

                if ($purchaseOrder->status === 'diproses') {
                    $purchaseOrder->status = 'diterima';
                    $purchaseOrder->save();

                    // Setelah PO diterima, update juga status store request yang terkait (jika ada)
                    if ($purchaseOrder->store_request_id) {
                        $storeRequest = \App\Models\StoreRequest::find($purchaseOrder->store_request_id);
                        if ($storeRequest) {
                            $storeRequest->status = 'diterima';
                            $storeRequest->save();
                        }
                    }
                }

                foreach ($validated['items'] as $itemData) {
                    $qtyReceived = (float) ($itemData['quantity_received'] ?? 0);
                    $qtyRejected = (float) ($itemData['quantity_rejected'] ?? 0);
                    $qtyToAdd    = $qtyReceived - $qtyRejected;

                    if ($qtyToAdd < 0) {
                        throw new \Exception("Jumlah ditolak tidak boleh melebihi jumlah diterima.");
                    }

                    $receiptItem = $goodsReceipt->items()->create([
                        'purchase_order_item_id' => $itemData['purchase_order_item_id'],
                        'quantity_received'      => $qtyReceived,
                        'quantity_rejected'      => $qtyRejected,
                        'notes'                  => $itemData['notes'] ?? null,
                    ]);

                    $poItem = PurchaseOrderItem::with('itemable')->find($itemData['purchase_order_item_id']);

                    if ($poItem && $poItem->itemable) {
                        $itemMaster = $poItem->itemable;
                        $oldStock   = (float) $itemMaster->stock;

                        if ($qtyToAdd > 0) {
                            $itemMaster->increment('stock', $qtyToAdd);
                        }

                        $newStock = (float) $itemMaster->fresh()->stock;

                        $newAverageCost = $itemMaster->getAverageCost();

                        if ($itemMaster instanceof Ingredient) {
                            $itemMaster->cost_price = $newAverageCost; // Update 'cost_price'
                            $itemName               = $itemMaster->name;
                        } elseif ($itemMaster instanceof Ffne) {
                            $itemMaster->harga = $newAverageCost; // Update 'harga'
                            $itemName          = $itemMaster->nama_ffne;
                        }
                        $itemMaster->save();

                        if ($itemMaster instanceof Ingredient) {
                            IngredientStockAdjustment::create([
                                'ingredient_id' => $itemMaster->id,
                                'user_id'       => auth()->id(),
                                'type'          => 'pembelian', // Pastikan tipe sesuai ENUM migration!
                                'quantity'      => $qtyToAdd,
                                'stock_before'  => $oldStock,
                                'stock_after'   => $newStock,
                                'notes'         => 'Penerimaan dari PO: ' . $purchaseOrder->po_number,
                            ]);
                        } elseif ($itemMaster instanceof Ffne) {
                            FfneStockAdj::create([
                                'ffne_id'        => $itemMaster->id,
                                'user_id'        => auth()->id(),
                                'type'           => 'pembelian', // Pastikan tipe sesuai ENUM migration!
                                'quantity'       => $qtyToAdd,
                                'stock_before'   => $oldStock,
                                'stock_after'    => $newStock,
                                'notes'          => 'Penerimaan dari PO: ' . $purchaseOrder->po_number,
                                'reference_id'   => $goodsReceipt->id,
                                'reference_type' => GoodsReceipt::class,
                            ]);
                        }

                        $updatedStocksLog[] = [
                            'item_type'    => ($itemMaster instanceof Ingredient) ? 'Ingredient' : 'FFNE',
                            'item_id'      => $itemMaster->id,
                            'item_name'    => $itemName,
                            'qty_received' => $qtyReceived,
                            'qty_rejected' => $qtyRejected,
                            'qty_added'    => $qtyToAdd, // Qty yg benar-benar masuk stok
                            'stock_before' => $oldStock,
                            'stock_after'  => $newStock,
                        ];
                    }
                }

                return $goodsReceipt;
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
            'updated_stocks' => $updatedStocksLog,
        ]);
    }

    public function penerimaanbarangDestroy(GoodsReceipt $penerimaanbarang)
    {
        try {
            \DB::transaction(function () use ($penerimaanbarang) {
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
        $detailItems = GoodsReceiptItem::with(['purchaseOrderItem.itemable'])
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

                $itemName = 'N/A';
                $itemUnit = 'N/A';
                $itemCode = '-';
                $itemId   = null;
                $itemType = null;

                if ($item->purchaseOrderItem && $item->purchaseOrderItem->itemable) {
                    $itemable = $item->purchaseOrderItem->itemable;

                    if ($itemable instanceof \App\Models\Ingredient) {
                        $itemName = $itemable->name;
                        $itemUnit = $itemable->unit;
                        // $itemCode = $itemable->code ?? $itemable->id;
                        $itemId   = $itemable->id;
                        $itemType = Ingredient::class;
                    } elseif ($itemable instanceof \App\Models\Ffne) {
                        $itemName = $itemable->nama_ffne;
                        $itemUnit = $itemable->satuan_ffne;
                        $itemCode = $itemable->kode_ffne;
                        $itemId   = $itemable->id;
                        $itemType = Ffne::class;
                    }
                }

                return [
                    'id'                     => $item->id,
                    'purchase_order_item_id' => $item->purchase_order_item_id,
                    'item_id'                => $itemId,
                    'item_type'              => $itemType,
                    'item_code'              => $itemCode,
                    'item_name'              => $itemName,
                    'item_unit'              => $itemUnit,
                    'quantity_received'      => (float) $item->quantity_received,
                    'quantity_rejected'      => (float) $item->quantity_rejected,
                    'notes'                  => $item->notes,
                ];
            })->all(),
        ]);
    }

    public function getPoItems($poId)
    {
        $purchaseOrder = PurchaseOrder::with([
            'items.itemable', // Load polymorphic relation
        ])->find($poId);

        if (! $purchaseOrder) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Purchase Order tidak ditemukan.',
            ], 404);
        }

        $items = $purchaseOrder->items->map(function ($item) {
            // Inisialisasi default values
            $itemName  = '-';
            $itemUnit  = '';
            $costPrice = 0;

            // Cek tipe itemable dan ambil data yang sesuai
            if ($item->itemable) {
                if ($item->itemable instanceof \App\Models\Ingredient) {
                    $itemName  = $item->itemable->name;
                    $itemUnit  = $item->itemable->unit;
                    $costPrice = $item->price; // Gunakan price dari PO item
                } elseif ($item->itemable instanceof \App\Models\Ffne) {
                    $itemName  = $item->itemable->nama_ffne;
                    $itemUnit  = $item->itemable->satuan_ffne;
                    $costPrice = $item->price; // Gunakan price dari PO item
                }
            }

            return [
                'purchase_order_item_id' => $item->id,
                'item_id'                => $item->itemable_id,
                'item_type'              => $item->itemable_type,
                'name'                   => $itemName, // PERBAIKAN: gunakan 'name' bukan 'ingredient_name'
                'quantity_ordered'       => $item->quantity,
                'unit'                   => $itemUnit,
                'cost_price'             => $costPrice,
            ];
        })->all();

        return response()->json([
            'status' => 'success',
            'items'  => $items,
        ]);
    }
}
