<?php
namespace App\Http\Controllers;

use App\Models\EnergyCost;
use App\Models\Extra;
use App\Models\Ffne;
use App\Models\FfneStockAdj;
use App\Models\Ingredient;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Modifier;
use App\Models\ModifierGroup;
use App\Models\StoreRequest;
use App\Models\StoreRequestItem;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Throwable;

class KitchenController extends Controller
{
    public function kategoriIndex()
    {
        $kategoris = MenuCategory::orderBy('id')->get();
        return view('kitchen.kategori.index', compact('kategoris'));
    }

    public function kategoriSubmit(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:menu_categories,name' . ($request->id ? ',' . $request->id : ''),
        ]);

        MenuCategory::updateOrCreate(
            ['id' => $request->id],
            $validated
        );

        $message = $request->id ? 'Data kategori berhasil diperbarui.' : 'Kategori baru berhasil ditambahkan.';
        return response()->json(['status' => 'success', 'message' => $message]);
    }

    public function kategoriDestroy(MenuCategory $kategori)
    {
        $kategori->delete();
        return response()->json(['status' => 'success', 'message' => 'Kategori berhasil dihapus.']);
    }

    // Bahan Baku Management
    public function bahanbakuKitchenIndex()
    {
        $bahanbakus = Ingredient::where('category', Ingredient::CATEGORY_KITCHEN)
            ->orderBy('name')
            ->get();
        $suppliers = Supplier::orderBy('name')->get();
        return view('kitchen.bahanbaku.kitchen.index', compact('bahanbakus', 'suppliers'));
    }

    public function bahanbakuBarIndex()
    {
        $bahanbakus = Ingredient::where('category', Ingredient::CATEGORY_BAR)
            ->orderBy('name')
            ->get();
        $suppliers = Supplier::orderBy('name')->get();
        return view('kitchen.bahanbaku.bar.index', compact('bahanbakus', 'suppliers'));
    }

    public function bahanbakuSubmit(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255|unique:ingredients,name' . ($request->id ? ',' . $request->id : ''),
            'stock'         => 'nullable|numeric|min:0',
            'unit'          => 'required|string|max:50',
            'cost_price'    => 'required|numeric|min:0',
            'supplier_id'   => 'required|exists:suppliers,id', // Validasi supplier
            'minimum_stock' => 'required|numeric|min:0',
            'category'      => ['required', Rule::in([Ingredient::CATEGORY_KITCHEN, Ingredient::CATEGORY_BAR])],
        ]);

        Ingredient::updateOrCreate(
            ['id' => $request->id],
            $validated
        );

        $message = $request->id ? 'Data bahan baku berhasil diperbarui.' : 'Bahan baku baru berhasil ditambahkan.';
        return response()->json(['status' => 'success', 'message' => $message]);
    }

    public function bahanbakuDestroy(Ingredient $bahanbaku)
    {
        $bahanbaku->delete();
        return response()->json(['status' => 'success', 'message' => 'Bahan baku berhasil dihapus.']);
    }

    public function menuIndex(Request $request)
    {
        $menuQuery = MenuItem::with(['ingredients', 'menuCategory']) // Ganti 'menu_category' menjadi 'menuCategory' sesuai konvensi
            ->orderBy('name', 'asc');

        if ($request->filled('search')) {
            $search = $request->search;
            $menuQuery->where('name', 'like', "%{$search}%");
        }

        if ($request->filled('category')) {
            $menuQuery->where('menu_category_id', $request->category);
        }

        $menus = $menuQuery->get();

        $kategoris  = MenuCategory::orderBy('name')->get();
        $bahanbakus = Ingredient::orderBy('name')->get();

        $modifierGroups = ModifierGroup::with('modifiers.ingredient')->latest()->get();

        return view('kitchen.menu.index', compact('menus', 'kategoris', 'bahanbakus', 'modifierGroups'));
    }

    public function menuSubmit(Request $request)
    {
        $validated = $request->validate([
            'name'                   => 'required|string|max:255|unique:menu_items,name' . ($request->id ? ',' . $request->id : ''),
            'category_id'            => 'required|exists:menu_categories,id',
            'price'                  => 'required|numeric|min:0',
            'description'            => 'nullable|string',
            'ingredients'            => 'required|array|min:1',
            'ingredients.*.id'       => 'required|exists:ingredients,id',
            'ingredients.*.quantity' => 'required|numeric|min:0.01',
            'modifier_groups'        => 'nullable|array', // Pastikan 'modifier_groups' adalah array
            'modifier_groups.*'      => 'exists:modifier_groups,id',
        ]);

        // Prepare menu item data
        $menuData = [
            'name'             => $validated['name'],
            'menu_category_id' => $validated['category_id'],
            'price'            => $validated['price'],
            'description'      => $validated['description'] ?? null,
        ];

        if (! $request->id) {
            $category     = MenuCategory::find($validated['category_id']);
            $categoryName = strtolower($category->name);
            if (strpos($categoryName, 'makanan') !== false) {
                $catCode = 'F';
            } elseif (strpos($categoryName, 'minuman') !== false) {
                $catCode = 'B';
            } else {
                $catCode = strtoupper(substr($categoryName, 0, 1));
            }

            // First letter of menu name
            $menuFirst = strtoupper(substr($validated['name'], 0, 1));

            // Count existing menu items with same prefix
            $prefix   = $catCode . '-' . $menuFirst;
            $count    = MenuItem::where('id', 'like', $prefix . '-%')->count();
            $number   = $count + 1;
            $customId = $prefix . '-' . $number;

            $menuData['id'] = $customId;
        }

        // Create or update menu item
        $menu = MenuItem::updateOrCreate(
            ['id' => $request->id ?? ($menuData['id'] ?? null)],
            $menuData
        );

        // Sync ingredients
        $syncData = [];
        foreach ($validated['ingredients'] as $ingredient) {
            $syncData[$ingredient['id']] = ['quantity' => $ingredient['quantity']];
        }
        $menu->ingredients()->sync($syncData);

        // Tambahan untuk sync modifier groups
        $menu->modifierGroups()->sync($request->input('modifier_groups', []));

        $message = $request->id ? 'Data menu berhasil diperbarui.' : 'Menu baru berhasil ditambahkan.';
        return response()->json(['status' => 'success', 'message' => $message]);
    }

    public function menuDestroy(MenuItem $menu)
    {
        $menu->ingredients()->detach();
        $menu->delete();
        return response()->json(['status' => 'success', 'message' => 'Menu berhasil dihapus.']);
    }

    public function energycostIndex()
    {
        $energycosts = EnergyCost::orderBy('id')->get();
        return view('kitchen.energycost.index', compact('energycosts'));
    }

    public function energycostSubmit(Request $request)
    {
        $validated = $request->validate([
            'name'   => 'required|string|max:255|unique:energy_costs,name' . ($request->id ? ',' . $request->id : ''),
            'cost'   => 'required|numeric|min:0',
            'period' => 'required|date',
        ]);

        $energyCostData = [
            'name'   => $validated['name'],
            'cost'   => $validated['cost'],
            'period' => $validated['period'],
        ];

        $energycost = EnergyCost::updateOrCreate(
            ['id' => $request->id],
            $energyCostData
        );

        $message = $request->id ? 'Data energy cost berhasil diperbarui.' : 'Energy cost baru berhasil ditambahkan.';
        return response()->json(['status' => 'success', 'message' => $message]);
    }

    public function energycostDestroy(EnergyCost $energycost)
    {
        $energycost->delete();
        return response()->json(['status' => 'success', 'message' => 'Energy cost berhasil dihapus.']);
    }

    // STORE REQUEST SECTION

    public function storerequestIndex()
    {
        $storerequests = StoreRequest::with(['items.itemable'])
            ->orderBy('id', 'desc')
            ->get();

        $ingredients = Ingredient::orderBy('id')->get();
        $ffnes       = \App\Models\Ffne::where('kategori_ffne', 'Barang Habis Pakai')->orderBy('id')->get();
        $bahanbakus  = $ingredients->map(function ($item) {
            return [
                'id'         => $item->id,
                'name'       => $item->name,
                'cost_price' => $item->cost_price ?? 0,
                'type'       => 'App\Models\Ingredient', // <-- Gunakan Nama Kelas Model
            ];
        })->concat(
            $ffnes->map(function ($item) {
                return [
                    'id'         => $item->id,
                    'name'       => $item->nama_ffne,
                    'cost_price' => $item->harga ?? 0,
                    'type'       => 'App\Models\Ffne', // <-- Gunakan Nama Kelas Model
                ];
            })
        );

        return view('kitchen.storerequest.index', compact('storerequests', 'bahanbakus'));
    }

    public function storerequestSubmit(Request $request)
    {
        $validated = $request->validate([
            'note'                   => 'nullable|string',
            'items'                  => 'required|array|min:1',
            // Validasi item_id dan item_type baru
            'items.*.item_id'        => 'required|integer',
            'items.*.item_type'      => ['required', \Illuminate\Validation\Rule::in(['App\Models\Ingredient', 'App\Models\Ffne'])], // Validasi tipe
            'items.*.requested_quantity' => 'required|numeric|min:0.01',
        ], [
            'items.required' => 'Minimal harus ada 1 item barang.',
            'items.*.item_id.required' => 'Item barang wajib dipilih.',
            'items.*.item_type.required' => 'Tipe item tidak valid.',
            'items.*.requested_quantity.required' => 'Jumlah (Qty) wajib diisi.',
        ]);
        // --- AKHIR PERBAIKAN VALIDASI ---

        $date        = now()->format('Ymd');
        $lastRequest = StoreRequest::where('request_number', 'like', "SR-{$date}%")
            ->orderBy('id', 'desc')->first();
        $sequence       = $lastRequest ? (int) substr($lastRequest->request_number, -3) + 1 : 1;
        $request_number = "SR-{$date}-" . str_pad($sequence, 3, '0', STR_PAD_LEFT);

        $storeRequestData = [
            'request_number' => $request_number,
            'remarks'        => $validated['note'] ?? null,
            'issued_at'      => now(),
            'issued_by'      => auth()->id(),
            'status'         => 'proses',
        ];

        try { // <-- Tambahkan try-catch-DB::transaction untuk keamanan data

            $storeRequest = \DB::transaction(function() use ($request, $storeRequestData, $validated) {

                $storeRequest = \App\Models\StoreRequest::updateOrCreate(
                    ['id' => $request->id],
                    $storeRequestData
                );

                // Hapus item lama jika ini adalah update
                if ($request->id) {
                    \App\Models\StoreRequestItem::where('store_request_id', $storeRequest->id)->delete();
                }

                // --- PERBAIKI PENYIMPANAN ITEM ---
                $itemsData = [];
                foreach ($validated['items'] as $item) {
                    $itemsData[] = [
                        'store_request_id'   => $storeRequest->id,
                        'itemable_id'        => $item['item_id'],       // <-- Ganti 'ingredient_id'
                        'itemable_type'      => $item['item_type'],     // <-- Tambahkan 'itemable_type'
                        'requested_quantity' => $item['requested_quantity'],
                        'issued_quantity'    => $item['requested_quantity'], // Default sama
                        'created_at'         => now(),
                        'updated_at'         => now(),
                    ];
                }
                // Simpan item baru
                \DB::table('store_request_items')->insert($itemsData);
                // --- AKHIR PERBAIKAN ---

                return $storeRequest; // Kembalikan storeRequest dari transaksi
            }); // <-- Akhir DB::transaction

        } catch (\Throwable $e) { // Tangkap semua error
            \Log::error('Gagal submit Store Request: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Gagal menyimpan data: ' . $e->getMessage()], 500);
        }

        $message = $request->id ? 'Data permintaan berhasil diperbarui.' : 'Permintaan baru berhasil ditambahkan.';
        return response()->json(['status' => 'success', 'message' => $message]);
    }

    public function storerequestDestroy(StoreRequest $storerequest)
    {
        \DB::table('store_request_items')->where('store_request_id', $storerequest->id)->delete();
        $storerequest->delete();
        return response()->json(['status' => 'success', 'message' => 'Store request berhasil dihapus.']);
    }

    public function storerequestPrint($id)
    {
        $storeRequest = StoreRequest::with(['items.ingredient', 'issuer'])->findOrFail($id);
        return view('kitchen.storerequest._print', compact('storeRequest'));
    }

    public function modifierGroupStore(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255|unique:modifier_groups,name',
            'selection_type' => ['required', Rule::in(['single', 'multiple'])],
        ]);

        $modifierGroup = ModifierGroup::create($validated);

        return response()->json([
            'status'            => 'success',
            'message'           => 'Grup Pilihan berhasil dibuat.',
            'modifier_group_id' => $modifierGroup->id,
        ]);
    }

    public function modifierGroupUpdate(Request $request, ModifierGroup $modifierGroup)
    {
        $validated = $request->validate([
            'name'           => [
                'required',
                'string',
                'max:255',
                Rule::unique('modifier_groups')->ignore($modifierGroup->id),
            ],
            'selection_type' => ['required', Rule::in(['single', 'multiple'])],
        ]);

        $modifierGroup->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Grup Pilihan berhasil diperbarui.',
        ]);
    }

    public function modifierGroupDestroy(ModifierGroup $modifierGroup)
    {
        $modifierGroup->modifiers()->delete();

        $modifierGroup->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Grup Pilihan berhasil dihapus.',
        ]);
    }

    // --- FUNGSI UNTUK MENGELOLA PILIHAN (MODIFIER) ---

    public function storeModifier(Request $request, ModifierGroup $modifierGroup)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'price'         => 'required|numeric|min:0',
            'ingredient_id' => 'nullable|exists:ingredients,id',
            'quantity_used' => 'required_with:ingredient_id|numeric|min:0|nullable',
        ]);

        $modifier = $modifierGroup->modifiers()->create($validated);

        return response()->json([
            'status'      => 'success',
            'message'     => 'Pilihan baru berhasil ditambahkan.',
            'modifier_id' => $modifier->id,
        ]);
    }

    public function updateModifier(Request $request, Modifier $modifier)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'price'         => 'required|numeric|min:0',
            'ingredient_id' => 'nullable|exists:ingredients,id',
            'quantity_used' => 'required_with:ingredient_id|numeric|min:0|nullable',
        ]);

        if (empty($validated['ingredient_id'])) {
            $validated['quantity_used'] = null;
        }

        $modifier->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Pilihan berhasil diperbarui.',
        ]);
    }

    public function destroyModifier(Modifier $modifier)
    {
        $modifier->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Pilihan berhasil dihapus.',
        ]);
    }

    public function indexFFNE()
    {
        $ffnes = Ffne::with('extras')->orderBy('id', 'desc')->get();
        return view('kitchen.ffne.index', compact('ffnes'));
    }

    public function submitFFNE(Request $request)
    {
        $ffneId = $request->id;

        $rules = [
            'nama_ffne'     => 'required|string|max:255',
            'kategori_ffne' => ['required', Rule::in(['Barang Habis Pakai', 'Barang Tidak Habis Pakai'])],
            'harga'         => 'required|numeric|min:0',
            'satuan_ffne'   => 'required|string|max:100',
            'kondisi_ffne'  => 'nullable|boolean',
            'stock'         => 'nullable|numeric|min:0',
        ];

        if (empty($ffneId)) {
            $rules['stock'] = 'required|numeric|min:0';
        }

        $validated = $request->validate($rules);

        $validated['kondisi_ffne'] = $request->has('kondisi_ffne');

        try {
            DB::transaction(function () use ($validated, $ffneId, $request) {

                if ($ffneId) {
                    $ffne = Ffne::findOrFail($ffneId);
                    unset($validated['stock']);

                    if ($ffne->kategori_ffne === 'Barang Habis Pakai' && $validated['kategori_ffne'] === 'Barang Tidak Habis Pakai') {
                        $validated['stock'] = 0;
                    }

                    $ffne->update($validated);
                    $message = 'Data FF&E berhasil diperbarui.';

                } else {
                    $prefix     = ($validated['kategori_ffne'] === 'Barang Tidak Habis Pakai') ? 'F-' : 'E-';
                    $lastFfne   = Ffne::where('kode_ffne', 'like', $prefix . '%')->orderBy('kode_ffne', 'desc')->lockForUpdate()->first();
                    $nextNumber = 1;
                    if ($lastFfne) {
                        $lastNumber = (int) substr($lastFfne->kode_ffne, strlen($prefix));
                        $nextNumber = $lastNumber + 1;
                    }
                    $validated['kode_ffne'] = $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

                    if ($validated['kategori_ffne'] === 'Barang Tidak Habis Pakai') {
                        $validated['stock'] = 0; // Aset tidak dihitung stoknya
                    } else {
                        $validated['stock'] = $validated['stock'] ?? 0;
                    }

                    $ffne = Ffne::create($validated);

                    if ($ffne->stock > 0) {
                        FfneStockAdj::create([
                            'ffne_id' => $ffne->id,
                            'qty'     => $ffne->stock,
                            'type'    => 'initial',
                            'notes'   => 'Stok Awal',
                        ]);
                    }
                    $message = 'Data FF&E baru berhasil ditambahkan.';
                }

                session()->flash('success_message', $message);

            });
            $message = session('success_message', 'Operasi berhasil.');

            return response()->json(['status' => 'success', 'message' => $message]);

        } catch (Throwable $e) { // Tangkap semua jenis error
            \Log::error("Error submit FFNE: " . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menyimpan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function submitStockAdjustment(Request $request)
    {
        $validated = $request->validate([
            'ffne_id' => 'required|exists:ffnes,id',
            'type'    => 'required|in:usage,waste,adjustment',
            'qty'     => 'required|numeric|min:0.01', // Selalu positif dari form
            'notes'   => 'nullable|string|max:255',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $ffne = Ffne::findOrFail($validated['ffne_id']);

                if ($ffne->kategori_ffne !== 'Barang Habis Pakai') {
                    throw new \Exception('Hanya Barang Habis Pakai yang bisa disesuaikan stoknya.');
                }

                $qtyToChange  = (float) $validated['qty'];
                $logQty       = 0;
                $currentStock = (float) $ffne->stock;

                if ($validated['type'] === 'usage' || $validated['type'] === 'waste') {
                    if ($qtyToChange > $currentStock) {
                        throw new \Exception("Stok tidak mencukupi. Sisa stok: $currentStock");
                    }
                    $logQty = -$qtyToChange;                 // Qty di log adalah negatif
                    $ffne->decrement('stock', $qtyToChange); // Kurangi stok master

                } elseif ($validated['type'] === 'adjustment') {
                    $difference  = $qtyToChange - $currentStock; // Selisihnya
                    $logQty      = $difference;                  // Catat selisihnya (+ atau -)
                    $ffne->stock = $qtyToChange;
                    $ffne->save();
                }

                FfneStockAdj::create([
                    'ffne_id' => $ffne->id,
                    'qty'     => $logQty, // Catat qty positif (initial/received) atau negatif (usage/waste) atau selisih (adjustment)
                    'type'    => $validated['type'],
                    'notes'   => $validated['notes'],
                ]);
            });

            return response()->json(['status' => 'success', 'message' => 'Penyesuaian stok berhasil disimpan.']);

        } catch (Throwable $e) {
            \Log::error("Error submit Stock Adj: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422); // 422 jika error validasi (cth: stok kurang)
        }
    }
    public function destroyFFNE(Ffne $ffne)
    {
        $ffne->extras()->delete();
        $ffne->delete();

        return response()->json(['status' => 'success', 'message' => 'Data FF&E berhasil dihapus.']);
    }

    public function editFFNE(Ffne $ffne)
    {
        return response()->json($ffne->load('extras'));
    }

    public function listExtra(Ffne $ffne)
    {
        $extras = $ffne->extras()->orderBy('tanggal', 'desc')->get();
        return response()->json($extras);
    }

    public function submitExtra(Request $request)
    {
        $validated = $request->validate([
            'id'         => 'nullable|exists:extras,id',
            'ffne_id'    => 'required|exists:ffnes,id',
            'nama'       => 'required|string|max:255',
            'harga'      => 'required|numeric|min:0',
            'tanggal'    => 'required|date',
            'keterangan' => 'nullable|string|max:500',
        ]);

        Extra::updateOrCreate(['id' => $request->id], $validated);

        $message = $request->id
            ? 'Data perbaikan berhasil diperbarui.'
            : 'Perbaikan baru berhasil ditambahkan.';

        return response()->json(['status' => 'success', 'message' => $message]);
    }

    public function destroyExtra(Extra $extra)
    {
        $extra->delete();
        return response()->json(['status' => 'success', 'message' => 'Data Extra berhasil dihapus.']);
    }

    public function editExtra(Extra $extra)
    {
        return response()->json($extra);
    }
}
