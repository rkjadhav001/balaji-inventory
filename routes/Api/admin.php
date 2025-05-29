<?php

use App\Http\Controllers\API\AppConfigController;
use App\Http\Controllers\API\AreaController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\CityController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\ExpanseCategoryController;
use App\Http\Controllers\API\ExpanseItemController;
use App\Http\Controllers\API\FinancialReportController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\PaymentInController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\PurchaseController;
use App\Http\Controllers\API\PurchaseOrderController;
use App\Http\Controllers\API\StaffController;
use App\Http\Controllers\API\StateController;
use App\Http\Controllers\API\StockManagementController;
use App\Http\Controllers\API\SupplierController;
use App\Http\Controllers\API\TaxController;
use App\Http\Controllers\API\BanksController;
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
    Route::post('sorting-update',[ProductController::class, 'sortingUpdate']);
});

#Purchase
Route::group(['namespace' => 'Api', 'prefix' => 'purchase'], function () {
    Route::get('list',[PurchaseController::class, 'index']);
    Route::post('store',[PurchaseController::class, 'store']);
    Route::get('view/{id}',[PurchaseController::class, 'view']);
    Route::post('update/{id}',[PurchaseController::class, 'update']);
    Route::get('product-list',[PurchaseController::class, 'purchaseProductList']);
    Route::get('delete/{id}',[PurchaseController::class, 'deletePurchaseTransaction']);
});

#Stock Management
Route::group(['namespace' => 'Api', 'prefix' => 'stock'], function () {
    Route::get('available',[StockManagementController::class, 'available']);
    Route::get('low',[StockManagementController::class, 'lowStock']);
    Route::get('outof',[StockManagementController::class, 'outofStock']);
});


#Bank
Route::group(['namespace' => 'Api', 'prefix' => 'bank'], function () {
    Route::post('add-bank',[BanksController::class, 'add_bank']);
    Route::post('update-bank',[BanksController::class, 'udpateBank']);
    Route::get('bank-list',[BanksController::class, 'bank_list']);   
    Route::post('add-bank-transaction',[BanksController::class, 'bank_transaction']);
    Route::post('update-bank-transaction',[BanksController::class, 'update_bank_transaction']);
    Route::get('bank-transaction-list',[BanksController::class, 'bank_transaction_list']);
    Route::get('bank-transaction-details',[BanksController::class, 'bank_transaction_details']);
    Route::get('cash-transaction-list',[BanksController::class, 'cashTransactionList']);
    Route::get('change-default-bank',[BanksController::class, 'defaultBankChange']);
    
});

#Manual Stock Management
Route::group(['namespace' => 'Api', 'prefix' => 'manual-stock'], function () {
    Route::post('add',[StockManagementController::class, 'addManual']);
    Route::post('minus',[StockManagementController::class, 'minusManual']);
});

#Vehicle
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
    Route::get('delete/{id}',[SupplierController::class, 'delete']);
    Route::get('bill-list',[SupplierController::class, 'bills']);
});

#Staff
Route::group(['namespace' => 'Api', 'prefix' => 'staff'], function () {
    Route::get('list',[StaffController::class, 'index']);
    Route::post('create',[StaffController::class, 'create']);
    Route::post('update/{id}',[StaffController::class, 'update']);
    Route::post('status',[StaffController::class, 'status']);
});

#Order
Route::group(['namespace' => 'Api', 'prefix' => 'order'], function () {
    Route::get('list',[OrderController::class, 'index']);
    Route::post('create',[OrderController::class, 'create']);
    Route::post('update/{id}',[OrderController::class, 'updateOrder']);
});
    
#Wholesaler
Route::group(['namespace' => 'Api', 'prefix' => 'wholesaler'], function () {
    Route::get('list',[WholesalerController::class, 'list']);
    Route::post('create',[WholesalerController::class, 'create']);
    Route::post('update/{id}',[WholesalerController::class, 'update']);
    Route::post('status',[WholesalerController::class, 'status']);
    Route::get('order-list',[WholesalerController::class, 'order']);
    Route::post('cancle_order',[WholesalerController::class, 'cancleOrder']);
    Route::post('order_update',[WholesalerController::class, 'orderUpdate']);        
    Route::get('order-status',[WholesalerController::class, 'statusUpdate']);
    Route::get('product-list',[WholesalerController::class, 'products']);
    Route::post('sorting-update',[WholesalerController::class, 'sortingUpdate']);
});

Route::group(['namespace' => 'Api', 'prefix' => 'dashboard'], function () {
    Route::get('list',[DashboardController::class, 'index']);
});

#Financial Report
Route::group(['namespace' => 'Api', 'prefix' => 'financial-report'], function () {
    Route::get('/',[FinancialReportController::class, 'report']);
    Route::get('party-list',[FinancialReportController::class, 'partyList']);
    Route::get('party-sell-list',[FinancialReportController::class, 'partySellslist']);
    Route::post('bill-collection',[FinancialReportController::class, 'billCollection']);
    Route::get('party-collection-list',[FinancialReportController::class, 'billCollectionList']);
    Route::get('date-wise-party-bills',[FinancialReportController::class, 'dateWisePartyBills']);
    Route::get('date-wise-party-collection',[FinancialReportController::class, 'dateWisePartyCollections']);
    Route::post('bill-collection-update/{id}',[FinancialReportController::class, 'billCollectionUpdate']);
    Route::get('bill-return/{id}',[FinancialReportController::class, 'billReturn']);
    Route::post('bill-return-store/{id}',[FinancialReportController::class, 'billReturnStore']);
    Route::get('bill-view/{id}',[FinancialReportController::class, 'billView']);
    Route::post('transfer-amount',[FinancialReportController::class, 'transferAmount']);
    Route::get('transfer-amount-list',[FinancialReportController::class, 'transferAmountList']);
    Route::post('expanse-add',[FinancialReportController::class, 'expenseAdd']);
    Route::get('expanse-list',[FinancialReportController::class, 'expenseList']);
    Route::get('expanse-detail',[FinancialReportController::class, 'expenseDetail']);
    Route::post('expanse-update/{id}',[FinancialReportController::class, 'updateExpense']);
    Route::get('expanse-delete/{id}',[FinancialReportController::class, 'deleteExpense']);


});

#Return Order
Route::group(['namespace' => 'Api', 'prefix' => 'return-order'], function () {
    Route::get('edit/{id}',[OrderController::class, 'editReturn']);
    Route::post('update/{id}',[OrderController::class, 'billReturnStore']);
});

#Purchase Order
Route::group(['namespace' => 'Api', 'prefix' => 'purchase-order'], function () {
    Route::get('product-list',[PurchaseOrderController::class, 'productList']);
    Route::get('list',[PurchaseOrderController::class, 'index']);
    Route::post('store',[PurchaseOrderController::class, 'store']);
    Route::get('view/{id}',[PurchaseOrderController::class, 'view']);
    Route::post('update/{id}',[PurchaseOrderController::class, 'update']);
});

#Transasctions
Route::group(['namespace' => 'Api', 'prefix' => 'transaction'], function () {
    Route::get('list',[OrderController::class, 'transactions']);
    Route::get('delete/{id}',[OrderController::class, 'deleteTransaction']);
});

#Payment In
Route::group(['namespace' => 'Api', 'prefix' => 'payment-in'], function () {
    Route::post('store',[PaymentInController::class, 'paymentInStore']);
    Route::get('view',[PaymentInController::class, 'paymentInView']);
    Route::post('update/{id}',[PaymentInController::class, 'paymentInUpdate']);

});

#Expanse Category
Route::group(['namespace' => 'Api', 'prefix' => 'expanse-category'], function () {
    Route::post('store',[ExpanseCategoryController::class, 'addCategory']);
    Route::get('list',[ExpanseCategoryController::class, 'listCategory']);
});

#Expanse Item
Route::group(['namespace' => 'Api', 'prefix' => 'expanse-item'], function () {
    Route::post('store',[ExpanseItemController::class, 'addItem']);
    Route::get('list',[ExpanseItemController::class, 'listItem']);
});

#Payment Type
Route::group(['namespace' => 'Api', 'prefix' => 'payment-type'], function () {
    Route::post('store',[ExpanseCategoryController::class, 'addPaymentType']);
    Route::get('list',[ExpanseCategoryController::class, 'listPaymentType']);
});

#Transaction List
Route::group(['namespace' => 'Api', 'prefix' => 'expanse-transaction'], function () {
    Route::get('list',[ExpanseCategoryController::class, 'transactionList']);
});

#Purchase Return
Route::group(['namespace' => 'Api', 'prefix' => 'purchase-return'], function () {
    Route::get('list',[PurchaseController::class, 'returnList']);
    Route::post('store',[PurchaseController::class, 'returnStore']);
    Route::get('view/{id}',[PurchaseController::class, 'returnView']);
    Route::post('update/{id}',[PurchaseController::class, 'returnUpdate']);
    Route::get('delete/{id}',[PurchaseController::class, 'purchaseReturnDelete']);
});


#Due Payment
Route::group(['namespace' => 'Api', 'prefix' => 'due-payment'], function () {
    Route::get('list',[FinancialReportController::class, 'duePaymentList']);
    Route::post('store',[FinancialReportController::class, 'duePaymentStore']);
    Route::get('view/{id}',[FinancialReportController::class, 'duePaymentView']);
    Route::post('update/{id}',[FinancialReportController::class, 'duePaymentUpdate']);
    Route::get('delete/{id}',[FinancialReportController::class, 'duePaymentDelete']);
});

#App Config
Route::group(['namespace' => 'Api', 'prefix' => 'app-config'], function () {
    Route::get('get',[AppConfigController::class, 'getAppConfig']);
    Route::post('update',[AppConfigController::class, 'updateAppConfig']);
});