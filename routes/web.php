<?php

use App\Http\Controllers\AccountingController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CashierController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchasingController;
use App\Http\Controllers\SetupController;
use App\Models\Sale;
use App\Models\Setting;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthenticatedSessionController::class, 'create'])->name('auth.login');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/setup', [SetupController::class, 'showSetupForm'])->name('setup.show');
Route::post('/setup', [SetupController::class, 'processSetup'])->name('setup.process');

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

    Route::middleware(['role:Super Admin'])->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::get('/users', [AdminController::class, 'users_index'])->name('users.index');
        Route::post('/users/submit', [AdminController::class, 'users_submit'])->name('users.submit');
        Route::delete('/users/{id}', [AdminController::class, 'users_delete'])->name('users.delete');

        Route::get('/settings', [AdminController::class, 'settings'])->name('settings.index');
        Route::post('/settings', [AdminController::class, 'settingsUpdate'])->name('settings.update');

        Route::get('/backup', [AdminController::class, 'backupIndex'])->name('backup.index');
        Route::post('/backup/create', [AdminController::class, 'backupCreate'])->name('backup.create');
        Route::get('/backup/{id}/download', [AdminController::class, 'backupDownload'])->name('backup.download');
        Route::delete('/backup/{id}/delete', [AdminController::class, 'backupDestroy'])->name('backup.destroy');

        Route::get('/karyawan', [AdminController::class, 'karyawanIndex'])->name('karyawan.index');
        Route::post('/mst/karyawan', [AdminController::class, 'karyawanStore'])->name('karyawan.store');
        Route::delete('/mst/karyawan/{id}', [AdminController::class, 'karyawanDestroy'])->name('karyawan.destroy');

        Route::get('/roles', [AdminController::class, 'rolesIndex'])->name('roles.index');
        Route::post('/roles/submit', [AdminController::class, 'rolesSubmit'])->name('roles.submit');
        Route::delete('/roles/{role}', [AdminController::class, 'rolesDestroy'])->name('roles.destroy');

    });

});

Route::middleware(['auth'])->prefix('acc')->name('acc.')->group(function () {

    Route::get('/suppliers', [AccountingController::class, 'suppliersIndex'])->name('suppliers.index');
    Route::post('/suppliers/submit', [AccountingController::class, 'suppliersSubmit'])->name('suppliers.submit');
    Route::delete('/suppliers/{supplier}', [AccountingController::class, 'suppliersDestroy'])->name('suppliers.destroy');

    Route::get('/suppliers/payments', [AccountingController::class, 'suppPaymentIndex'])->name('suppliers.payments.index');
    Route::post('/suppliers/payments/store', [AccountingController::class, 'suppPaymentStore'])->name('suppliers.payments.store');
    Route::delete('/suppliers/payments/{supplier}', [AccountingController::class, 'suppPaymentDestroy'])->name('suppliers.payments.destroy');

    Route::get('payroll', [AccountingController::class, 'payrollIndex'])->name('payroll.index');
    Route::post('payroll/store', [AccountingController::class, 'payrollStore'])->name('payroll.store');
    Route::delete('payroll/destroy/{id}', [AccountingController::class, 'payrollDestroy'])->name('payroll.destroy');
    Route::get('payroll/download/{id}', [AccountingController::class, 'downloadBukti'])->name('payroll.download');

    Route::get('/credit-limit-monitoring', [AccountingController::class, 'creditLimitMonitoring'])->name('suppliers.credit_limit_monitoring');
    Route::get('/suppliers/{supplier}/credit-history', [AccountingController::class, 'suppCreditHistory'])->name('suppliers.credit.history');

    Route::get('/laporan-penjualan', [AccountingController::class, 'salesReport'])->name('laporan-penjualan');
    Route::get('/laporan-penjualan/cetak', [AccountingController::class, 'salesReportPdf'])->name('laporan-penjualan.export.pdf');
    Route::get('/laporan-penjualan/export', [AccountingController::class, 'salesReportXls'])->name('laporan-penjualan.export.excel');

    // Routes Laporan Stok
    Route::get('/laporan/stok-mutasi', [AccountingController::class, 'stockMovementReport'])->name('laporan-stok-mutasi');
    Route::get('/laporan/stok-mutasi/export/{type}', [AccountingController::class, 'stockMovementExport'])->name('laporan-stok-mutasi.export');

    // Routes Laporan Laba Rugi
    Route::get('/laporan/laba-rugi', [AccountingController::class, 'profitAndLossReport'])->name('laporan-labarugi');
    Route::get('/laporan/laba-rugi/download/excel', [AccountingController::class, 'profitAndLossDownloadExcel'])
    ->name('laporan-labarugi.download.excel');
});

Route::middleware(['auth'])->prefix('prc')->name('prc.')->group(function () {

    Route::get('/penerimaanbarang', [PurchasingController::class, 'penerimaanbarangIndex'])->name('penerimaanbarang.index');
    Route::post('/penerimaanbarang/submit', [PurchasingController::class, 'penerimaanbarangSubmit'])->name('penerimaanbarang.submit');
    Route::delete('/penerimaanbarang/{penerimaanbarang}', [PurchasingController::class, 'penerimaanbarangDestroy'])->name('penerimaanbarang.destroy');
    Route::get('/penerimaanbarang/po-items/{poId}', [PurchasingController::class, 'getPoItems'])->name('penerimaanbarang.po_items');
    Route::get('/penerimaanbarang/{id}', [PurchasingController::class, 'penerimaanbarangShow'])->name('penerimaanbarang.show');

    Route::get('/purchase-orders', [PurchasingController::class, 'purchaseOrderIndex'])->name('purchase_orders.index');
    Route::post('/purchase-orders/submit', [PurchasingController::class, 'purchaseOrderSubmit'])->name('purchase_orders.submit');
    Route::delete('/purchase-orders/{purchaseOrder}', [PurchasingController::class, 'purchaseOrderDestroy'])->name('purchase_orders.destroy');
    Route::get('/purchase-orders/sr-items/{storeRequestId}', [PurchasingController::class, 'getStoreRequestItems'])->name('purchase_orders.sr_items');
    Route::get('/purchase-orders/{purchaseOrder}', [PurchasingController::class, 'purchaseOrderShow'])->name('purchase_orders.show');
    Route::get('/purchase-orders/print/{id}', [PurchasingController::class, 'purchaseOrderPrint'])->name('purchase_orders.print');
    Route::get('/suppliers/{supplier}', [PurchasingController::class, 'getSupplierDetails'])->name('suppliers.details');
});

Route::middleware(['auth'])->prefix('kitchen')->name('kitchen.')->group(function () {

    Route::get('/kategori', [KitchenController::class, 'kategoriIndex'])->name('kategori.index');
    Route::post('/kategori/submit', [KitchenController::class, 'kategoriSubmit'])->name('kategori.submit');
    Route::delete('/kategori/{kategori}', [KitchenController::class, 'kategoriDestroy'])->name('kategori.destroy');

    Route::get('/bahanbaku/kitchen', [KitchenController::class, 'bahanbakuKitchenIndex'])->name('bahanbaku.kitchen.index');
    Route::get('/bahanbaku/bar', [KitchenController::class, 'bahanbakuBarIndex'])->name('bahanbaku.bar.index');
    Route::post('/bahanbaku/submit', [KitchenController::class, 'bahanbakuSubmit'])->name('bahanbaku.submit');
    Route::delete('/bahanbaku/{bahanbaku}', [KitchenController::class, 'bahanbakuDestroy'])->name('bahanbaku.destroy');

    Route::get('/menu', [KitchenController::class, 'menuIndex'])->name('menu.index');
    Route::post('/menu/submit', [KitchenController::class, 'menuSubmit'])->name('menu.submit');
    Route::delete('/menu/{menu}', [KitchenController::class, 'menuDestroy'])->name('menu.destroy');

    Route::get('/energycost', [KitchenController::class, 'energycostIndex'])->name('energycost.index');
    Route::post('/energycost/submit', [KitchenController::class, 'energycostSubmit'])->name('energycost.submit');
    Route::delete('/energycost/{energycost}', [KitchenController::class, 'energycostDestroy'])->name('energycost.destroy');

    Route::get('/storerequest', [KitchenController::class, 'storerequestIndex'])->name('storerequest.index');
    Route::post('/storerequest/submit', [KitchenController::class, 'storerequestSubmit'])->name('storerequest.submit');
    Route::delete('/storerequest/{storerequest}', [KitchenController::class, 'storerequestDestroy'])->name('storerequest.destroy');
    Route::get('/storerequest/print/{id}', [KitchenController::class, 'storerequestPrint'])->name('storerequest.print');

    Route::post('/modifier-groups', [KitchenController::class, 'modifierGroupStore'])->name('modifier-groups.store');
    Route::put('/modifier-groups/{modifierGroup}', [KitchenController::class, 'modifierGroupUpdate'])->name('modifier-groups.update');
    Route::delete('/modifier-groups/{modifierGroup}', [KitchenController::class, 'modifierGroupDestroy'])->name('modifier-groups.destroy');

    Route::post('modifier-groups/{modifierGroup}/modifiers', [KitchenController::class, 'storeModifier'])->name('modifiers.store');
    Route::put('modifiers/{modifier}', [KitchenController::class, 'updateModifier'])->name('modifiers.update');
    Route::delete('modifiers/{modifier}', [KitchenController::class, 'destroyModifier'])->name('modifiers.destroy');

    // Routes FFNE
    Route::get('/ffne', [KitchenController::class, 'indexFFNE'])->name('ffne.index');
    Route::post('/ffne/submit', [KitchenController::class, 'submitFfne'])->name('ffne.submit');
    Route::post('/ffne/stock/submit', [KitchenController::class, 'submitStockAdjustment'])->name('ffne.stock.submit');

    Route::delete('/ffne/{ffne}', [KitchenController::class, 'destroyFFNE'])->name('ffne.destroy');
    Route::get('/ffne/{ffne}/edit', [KitchenController::class, 'editFFNE'])->name('ffne.edit');

    // Routes Extra
    Route::get('/ffne/{ffne}/extras', [KitchenController::class, 'listExtra'])->name('ffne.extras.list');
    Route::post('/ffne/extras/submit', [KitchenController::class, 'submitExtra'])->name('ffne.extras.submit');
    Route::get('/ffne/extras/{extra}/edit', [KitchenController::class, 'editExtra'])->name('ffne.extras.edit');
    Route::delete('/ffne/extras/{extra}', [KitchenController::class, 'destroyExtra'])->name('ffne.extras.destroy');

    // Route Laporan
    Route::get('/laporan/bahanbaku', [KitchenController::class, 'laporaBahanbaku'])->name('laporan.bahanbaku.vvip');
    Route::get('/laporan/bahanbaku', [KitchenController::class, 'laporanFfneIndex'])->name('laporan.bahanbaku.vvip');
});

Route::middleware(['auth'])->prefix('cashier')->name('cashier.')->group(function () {
    Route::get('/', [CashierController::class, 'Index'])->name('index');
    Route::post('/submit', [CashierController::class, 'Submit'])->name('submit');
    Route::get('/history', [CashierController::class, 'History'])->name('history');
    Route::get('/sales/{sale}/print/customer', [CashierController::class, 'printCustomerReceipt'])->name('print.customer');
    Route::get('/sales/{sale}/print/kitchen', [CashierController::class, 'printKitchenReceipt'])->name('print.kitchen');
    Route::post('/sales/{id}/print/smart', [CashierController::class, 'smartPrintAfterPayment'])->name('payment.print.smart');
    Route::post('/start-transaction', [CashierController::class, 'startTransaction'])->name('startTransaction');
    Route::get('/payment/{sale}', [CashierController::class, 'showPaymentPage'])->name('payment.page');
    Route::post('/payment/{sale}/process', [CashierController::class, 'processPayment'])->name('payment.process');
    Route::post('/payment/{sale}/void', [CashierController::class, 'voidTransaction'])->name('payment.void');

    Route::get('/', [CashierController::class, 'Index'])->name('index');
    Route::post('/submit', [CashierController::class, 'Submit'])->name('submit');
    Route::get('/history', [CashierController::class, 'History'])->name('history');
    Route::get('/sales/{sale}/print/customer', [CashierController::class, 'printCustomerReceipt'])->name('print.customer');
    Route::get('/sales/{sale}/print/kitchen', [CashierController::class, 'printKitchenReceipt'])->name('print.kitchen');
    Route::post('/sales/{id}/print/smart', [CashierController::class, 'smartPrintAfterPayment'])->name('payment.print.smart');
    Route::post('/start-transaction', [CashierController::class, 'startTransaction'])->name('startTransaction');

    Route::get('/payment/{sale}', [CashierController::class, 'showPaymentPage'])->name('payment.page');
    Route::post('/payment/{sale}/process', [CashierController::class, 'processPayment'])->name('payment.process');
    Route::post('/payment/{sale}/void', [CashierController::class, 'voidTransaction'])->name('payment.void');

    Route::get('/history', [CashierController::class, 'historyIndex'])->name('history');
    Route::get('/history/{sale}', [CashierController::class, 'historyShow'])->name('history.show');

    Route::post('/reservations/store', [CashierController::class, 'storeReservation'])->name('reservations.store');
});

Route::get('/test-print/{id}', function ($id) {
    $sale         = Sale::with(['user', 'items.menuItem.menuCategory', 'items.selectedModifiers.modifier'])->findOrFail($id);
    $settings     = Setting::pluck('value', 'key')->toArray();
    $itemsToPrint = $sale->items;

    return view('cashier._print_kitchen', compact('sale', 'itemsToPrint', 'settings'));
});

// Route::get('/test-printers', function () {
//     try {
//         $printers = System::printers();
//         return response()->json([
//             'success'  => true,
//             'count'    => count($printers),
//             'printers' => collect($printers)->map(fn($p) => [
//                 'name'        => $p->name,
//                 'displayName' => $p->displayName,
//                 'status'      => $p->status ?? 'unknown',
//             ]),
//         ]);
//     } catch (\Exception $e) {
//         return response()->json([
//             'success' => false,
//             'error'   => $e->getMessage(),
//         ]);
//     }
// });

// // Test print sample
// Route::get('/test-print', function () {
//     try {
//         $html = '<html><body style="font-family: monospace; padding: 20px;">
//             <h1>TEST PRINT</h1>
//             <p>Ini adalah test printing dari NativePHP</p>
//             <p>Tanggal: ' . now()->format('d/m/Y H:i:s') . '</p>
//         </body></html>';

//         System::print($html);

//         return 'Printing... Check your printer!';
//     } catch (\Exception $e) {
//         return 'Error: ' . $e->getMessage();
//     }
// });
require __DIR__ . '/auth.php';
