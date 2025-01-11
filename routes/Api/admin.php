<?php

use App\Http\Controllers\API\AreaController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\CityController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\PurchaseController;
use App\Http\Controllers\API\StaffController;
use App\Http\Controllers\API\StateController;
use App\Http\Controllers\API\StockManagementController;
use App\Http\Controllers\API\SupplierController;
use App\Http\Controllers\API\TaxController;
use App\Http\Controllers\API\UnitTypeController;
use App\Http\Controllers\API\VehicleController;
use App\Http\Controllers\API\WholesalerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

#Auth
Route::post('/login',[AuthController::class,'login']);

#Category
Route::group(['namespace' => 'Api', 'prefix' => 'category'], function () {
    Route::get('list',[CategoryController::class, 'index']);
    Route::post('create',[CategoryController::class, 'create']);
    Route::post('update/{id}',[CategoryController::class, 'update']);
    Route::post('status',[CategoryController::class, 'status']);
});

#Tax
Route::group(['namespace' => 'Api', 'prefix' => 'tax'], function () {
    Route::get('list',[TaxController::class, 'index']);
    Route::post('create',[TaxController::class, 'create']);
    Route::post('update/{id}',[TaxController::class, 'update']);
});

#Unit Type
Route::group(['namespace' => 'Api', 'prefix' => 'unit-type'], function () {
    Route::get('list',[UnitTypeController::class, 'index']);
    Route::post('update/{id}',[UnitTypeController::class, 'update']);
});

#State
Route::group(['namespace' => 'Api', 'prefix' => 'state'], function () {
    Route::get('list',[StateController::class, 'index']);
    Route::post('create',[StateController::class, 'create']);
    Route::post('update/{id}',[StateController::class, 'update']);
    Route::post('status',[StateController::class, 'status']);
});

#City
Route::group(['namespace' => 'Api', 'prefix' => 'city'], function () {
    Route::get('list',[CityController::class, 'index']);
    Route::post('create',[CityController::class, 'create']);
    Route::post('update/{id}',[CityController::class, 'update']);
    Route::post('status',[CityController::class, 'status']);
});

#Area
Route::group(['namespace' => 'Api', 'prefix' => 'area'], function () {
    Route::get('list',[AreaController::class, 'index']);
    Route::post('create',[AreaController::class, 'create']);
    Route::post('update/{id}',[AreaController::class, 'update']);
    Route::post('status',[AreaController::class, 'status']);
});

#Product
Route::group(['namespace' => 'Api', 'prefix' => 'product'], function () {
    Route::get('list',[ProductController::class, 'index']);
    Route::post('create',[ProductController::class, 'create']);
    Route::post('update/{id}',[ProductController::class, 'update']);
    Route::post('status',[ProductController::class, 'status']);
    Route::get('scan',[ProductController::class, 'scanProduct']);
});

#Purchase
Route::group(['namespace' => 'Api', 'prefix' => 'purchase'], function () {
    Route::get('list',[PurchaseController::class, 'index']);
    Route::post('store',[PurchaseController::class, 'store']);
    Route::get('view/{id}',[PurchaseController::class, 'view']);
});

#Stock Management
Route::group(['namespace' => 'Api', 'prefix' => 'stock'], function () {
    Route::get('available',[StockManagementController::class, 'available']);
});

#Manual Stock Management
Route::group(['namespace' => 'Api', 'prefix' => 'manual-stock'], function () {
    Route::post('add',[StockManagementController::class, 'addManual']);
    Route::post('minus',[StockManagementController::class, 'minusManual']);
});

Route::group(['namespace' => 'Api', 'prefix' => 'vehicle'], function () {
    Route::get('list',[VehicleController::class, 'index']);
    Route::post('create',[VehicleController::class, 'create']);
    Route::post('update/{id}',[VehicleController::class, 'update']);
    Route::post('status',[VehicleController::class, 'status']);
});

#Supplier
Route::group(['namespace' => 'Api', 'prefix' => 'supplier'], function () {
    Route::get('list',[SupplierController::class, 'index']);
    Route::post('create',[SupplierController::class, 'create']);
    Route::post('update/{id}',[SupplierController::class, 'update']);
    Route::post('status',[SupplierController::class, 'status']);
});

#Staff
Route::group(['namespace' => 'Api', 'prefix' => 'staff'], function () {
    Route::get('list',[StaffController::class, 'index']);
    Route::post('create',[StaffController::class, 'create']);
    Route::post('update/{id}',[StaffController::class, 'update']);
    Route::post('status',[StaffController::class, 'status']);
});

#Staff
Route::group(['namespace' => 'Api', 'prefix' => 'order'], function () {
    Route::get('list',[OrderController::class, 'index']);
    Route::post('create',[OrderController::class, 'create']);
});

Route::group(['namespace' => 'Api', 'prefix' => 'wholesaler'], function () {
    Route::get('list',[WholesalerController::class, 'list']);
    Route::post('create',[WholesalerController::class, 'create']);
    Route::post('update/{id}',[WholesalerController::class, 'update']);
    Route::post('status',[WholesalerController::class, 'status']);
    Route::get('order-list',[WholesalerController::class, 'order']);
    Route::get('product-list',[WholesalerController::class, 'products']);
    Route::post('sorting-update',[WholesalerController::class, 'sortingUpdate']);
});