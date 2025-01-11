<?php

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

Route::get('wholesaler/{unique_id}',[WholeSelerController::class, 'index'])->name('Wholesaler.Product');
Route::post('create-order',[WholeSelerController::class, 'order'])->name('Wholesaler.Order');
Route::get('order-confirm/{order_id}',[WholeSelerController::class, 'confirmOrder'])->name('Wholesaler.confirmOrder');
