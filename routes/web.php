<?php

use App\Http\Controllers\AccountingController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CashierController;
use App\Http\Controllers\KitchenController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchasingController;
use App\Http\Controllers\SetupController;
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
    });

});

Route::middleware(['auth'])->prefix('acc')->name('acc.')->group(function () {

    Route::middleware(['role:Super Admin'])->group(function () {
        Route::get('/suppliers', [AccountingController::class, 'suppliersIndex'])->name('suppliers.index');
        Route::post('/suppliers/submit', [AccountingController::class, 'suppliersSubmit'])->name('suppliers.submit');
        Route::delete('/suppliers/{supplier}', [AccountingController::class, 'suppliersDestroy'])->name('suppliers.destroy');
        Route::get('/credit-limit-monitoring', [AccountingController::class, 'creditLimitMonitoring'])->name('suppliers.credit_limit_monitoring');
    });
});

Route::middleware(['auth'])->prefix('prc')->name('prc.')->group(function () {

    Route::middleware(['role:Super Admin'])->group(function () {
        Route::get('/penerimaanbarang', [PurchasingController::class, 'penerimaanbarangIndex'])->name('penerimaanbarang.index');
        Route::post('/penerimaanbarang/submit', [PurchasingController::class, 'penerimaanbarangSubmit'])->name('penerimaanbarang.submit');
        Route::delete('/penerimaanbarang/{penerimaanbarang}', [PurchasingController::class, 'penerimaanbarangDestroy'])->name('penerimaanbarang.destroy');
        Route::get('/penerimaanbarang/po-items/{poId}', [PurchasingController::class, 'getPoItems'])->name('penerimaanbarang.po_items');
        Route::get('/penerimaanbarang/{id}', [PurchasingController::class, 'penerimaanbarangShow'])->name('penerimaanbarang.show');

        // Purchase Order Routes
        Route::get('/purchase-orders', [PurchasingController::class, 'purchaseOrderIndex'])->name('purchase_orders.index');
        Route::post('/purchase-orders/submit', [PurchasingController::class, 'purchaseOrderSubmit'])->name('purchase_orders.submit');
        Route::delete('/purchase-orders/{purchaseOrder}', [PurchasingController::class, 'purchaseOrderDestroy'])->name('purchase_orders.destroy');
        Route::get('/purchase-orders/sr-items/{storeRequestId}', [PurchasingController::class, 'getStoreRequestItems'])->name('purchase_orders.sr_items');
        Route::get('/purchase-orders/{purchaseOrder}', [PurchasingController::class, 'purchaseOrderShow'])->name('purchase_orders.show');
        Route::get('/purchase-orders/print/{id}', [PurchasingController::class, 'purchaseOrderPrint'])->name('purchase_orders.print');
    });
});

Route::middleware(['auth'])->prefix('kitchen')->name('kitchen.')->group(function () {

    Route::middleware(['role:Super Admin'])->group(function () {
        Route::get('/kategori', [KitchenController::class, 'kategoriIndex'])->name('kategori.index');
        Route::post('/kategori/submit', [KitchenController::class, 'kategoriSubmit'])->name('kategori.submit');
        Route::delete('/kategori/{kategori}', [KitchenController::class, 'kategoriDestroy'])->name('kategori.destroy');

        Route::get('/bahanbaku', [KitchenController::class, 'bahanbakuIndex'])->name('bahanbaku.index');
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

    });
});

Route::middleware(['auth'])->prefix('cashier')->name('cashier.')->group(function () {

    Route::middleware(['role:Super Admin'])->group(function () {
        Route::get('/', [CashierController::class, 'Index'])->name('index');
        Route::post('/submit', [CashierController::class, 'Submit'])->name('submit');
        Route::get('/history', [CashierController::class, 'History'])->name('history');
        Route::get('/sales/{sale}/print/customer', [CashierController::class, 'printCustomerReceipt'])->name('print.customer');
        Route::get('/sales/{sale}/print/kitchen', [CashierController::class, 'printKitchenReceipt'])->name('print.kitchen');
        Route::post('/start-transaction', [CashierController::class, 'startTransaction'])->name('startTransaction');
        Route::get('/payment/{sale}', [CashierController::class, 'showPaymentPage'])->name('payment.page');
        Route::post('/payment/{sale}/process', [CashierController::class, 'processPayment'])->name('payment.process');
        Route::post('/payment/{sale}/void', [CashierController::class, 'voidTransaction'])->name('payment.void');
    });
});

require __DIR__ . '/auth.php';
