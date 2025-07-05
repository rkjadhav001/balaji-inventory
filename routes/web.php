<?php

use App\Http\Controllers\Wholeseler\ReportController;
use App\Http\Controllers\Wholeseler\WholeSelerController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('wholesaler',[WholeSelerController::class, 'index'])->name('Wholesaler.Product');
Route::get('wholesaler-data',[WholeSelerController::class, 'getData'])->name('wholesaler-data');
Route::get('wholesaler-cart',[WholeSelerController::class, 'cart'])->name('Wholesaler.Cart');
Route::post('add-to-cart',[WholeSelerController::class, 'addToCart'])->name('Wholesaler.addToCart');
Route::post('remove-cart',[WholeSelerController::class, 'removeCart'])->name('Wholesaler.Cart.Remove');
Route::post('/update-cart', [WholeSelerController::class, 'updateCart'])->name('update.cart');
Route::post('/cart/update-session', [WholeSelerController::class, 'updateCartSession'])->name('Wholesaler.Cart.UpdateSession');

Route::post('create-order',[WholeSelerController::class, 'order'])->name('Wholesaler.Order');
Route::get('order-confirm/{order_id}',[WholeSelerController::class, 'confirmOrder'])->name('Wholesaler.confirmOrder');

Route::get('invoice',[WholeSelerController::class, 'invoice'])->name('Invoice');
Route::get('purchase-invoice/{id}',[WholeSelerController::class, 'purchaseInvoice'])->name('PurchaseInvoice');
Route::get('party-order/{id}',[WholeSelerController::class, 'partyOrder'])->name('partyOrder');
Route::get('expanse-transaction',[WholeSelerController::class, 'expanseTransaction'])->name('expanseTransaction');
Route::get('expanse-transaction-summery',[WholeSelerController::class, 'expanseTransactionSummery'])->name('expanseTransactionSummery');

Route::get('a4-print',[WholeSelerController::class, 'printA4'])->name('printA4');

Route::get('sale-purchase-report',[WholeSelerController::class, 'salePurchaseReport'])->name('salePurchaseReport');
// Route::get('purchase-invoice/{id}',[WholeSelerController::class, 'purchaseInvoice'])->name('purchaseInvoice');

Route::get('hsn-report',[ReportController::class, 'hsnWiseReport'])->name('hsnWiseReport');