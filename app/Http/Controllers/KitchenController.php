<?php
namespace App\Http\Controllers;

use App\Models\EnergyCost;
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
    public function bahanbakuIndex()
    {
        $bahanbakus = Ingredient::orderBy('id')->get();
        $suppliers  = Supplier::orderBy('name')->get();
        return view('kitchen.bahanbaku.index', compact('bahanbakus', 'suppliers'));
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
        $storerequests = StoreRequest::with(['items.ingredient'])->orderBy('id', 'desc')->get();
        $bahanbakus    = Ingredient::orderBy('id')->get();
        return view('kitchen.storerequest.index', compact('storerequests', 'bahanbakus'));
    }

    public function storerequestSubmit(Request $request)
    {
        $validated = $request->validate([
            'note'                       => 'nullable|string',
            'items'                      => 'required|array|min:1',
            'items.*.ingredient_id'      => 'required|exists:ingredients,id',
            'items.*.requested_quantity' => 'required|numeric|min:0.01',
        ]);

        // Generate request_number
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
            'status'         => 'proses', // Hardcoded status
        ];

        \Log::info('StoreRequest Input', $storeRequestData);
        \Log::info('Items Input', $validated['items']);

        $storeRequest = StoreRequest::updateOrCreate(
            ['id' => $request->id],
            $storeRequestData
        );

        // Handle items
        if ($request->id) {
            StoreRequestItem::where('store_request_id', $storeRequest->id)->delete();
        }

        $itemsData = [];
        foreach ($validated['items'] as $item) {
            $itemsData[] = [
                'store_request_id'   => $storeRequest->id,
                'ingredient_id'      => $item['ingredient_id'],
                'requested_quantity' => $item['requested_quantity'],
                'issued_quantity'    => $item['requested_quantity'], // Default sama dengan requested
                'created_at'         => now(),
                'updated_at'         => now(),
            ];
        }
        DB::table('store_request_items')->insert($itemsData);

        \Log::info('StoreRequest ID ' . $storeRequest->id . ' Items Inserted', $itemsData);

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
        // Mengambil store request beserta relasi ke items, ingredient, dan user (issuer)
        $storeRequest = StoreRequest::with(['items.ingredient', 'issuer'])->findOrFail($id);
        return view('kitchen.storerequest._print', compact('storeRequest'));
    }

    public function modifierGroupStore(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255|unique:modifier_groups,name',
            'selection_type' => ['required', Rule::in(['single', 'multiple'])],
        ]);

        // Simpan data grup modifier
        $modifierGroup = ModifierGroup::create($validated);

        // Return JSON untuk swal
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
}
