<?php

use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\WarehouseDesignController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Customer\CategoryController;
use App\Http\Controllers\Customer\ItemController;
use App\Http\Controllers\Customer\ItemUnitController;
use App\Http\Controllers\Customer\RegisterRequestController;
use App\Http\Controllers\Customer\SettingsController;
use App\Http\Controllers\Customer\UnitContoller;
use App\Http\Controllers\Notification\NotificationController;
use App\Http\Controllers\Orders\CartItemController;
use App\Http\Controllers\Orders\OrderController;
use App\Http\Controllers\Orders\PointsController;
use App\Http\Controllers\WarehouseKeeper\InventoryController;
use App\Http\Controllers\WarehouseKeeper\ItemStorageController;
use App\Http\Controllers\WarehouseKeeper\PurchaseOrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::middleware('auth:api')->group(function () {
    Route::get('user', function (Request $request) {
        return $request->user();
    });
});
//registration

Route::get('showAll', [RegisterRequestController::class, 'index']);
Route::post('registerRequests', [RegisterRequestController::class, 'store']);
Route::get('showById/{id}', [RegisterRequestController::class, 'show']);
Route::get('showCertificate/{id}/certificate', [RegisterRequestController::class, 'showCertificate']);
Route::patch('/register-requests/{id}/status', [RegisterRequestController::class, 'updateStatus']);
Route::put('changePassword', [AuthController::class, 'addPassword']);
//----------------


//category
Route::post('addCategory', [CategoryController::class, 'store']);
Route::get('showCategories', [CategoryController::class, 'index']);
Route::get('showSubCategories/{parent_id}', [CategoryController::class, 'show']);
Route::get('lastLevel', [CategoryController::class, 'showLastLevel']);
//unit
Route::post('addUnit', [UnitContoller::class, 'store']);
Route::get('showUnits', [UnitContoller::class, 'index']);
//item
Route::get('ItemsInCategory/{category_id}', [ItemController::class, 'index']);
Route::post('/item', [ItemController::class, 'store']);
Route::put('/updateItems/{id}', [ItemController::class, 'update']);
Route::get('isBaseUnit', [ItemController::class, 'baseUnitForItem']);
Route::get('/showAllItems', [ItemController::class, 'getAllItems']);
Route::get('itemDetails/{id}', [ItemController::class, 'getItemById']);
//itemUnit
Route::post('/addItemUnit', [ItemUnitController::class, 'store']);
Route::get('showItemUnit', [ItemUnitController::class, 'show']);
Route::get('showAllItemUnit/{itemId}', [ItemUnitController::class, 'showAll']);
//settings-------------------------------------
//point
Route::get('/showPointsSettings', [SettingsController::class, 'indexPoints']);
Route::put('/updatePointsSettings', [SettingsController::class, 'updatePoints']);
//order
Route::put('/updateOrdersSettings', [SettingsController::class, 'updateOrders']);
Route::get('/showOrdersSettings', [SettingsController::class, 'indexOrders']);
//Installments
Route::put('/updateInstallmentsSettings', [SettingsController::class, 'updateInstallments']);
Route::get('/showInstallmentsSettings', [SettingsController::class, 'indexInstallments']);
//-------------------------------------------------------------------------------------------
///todo
Route::middleware('auth:api')->group(function () {
    //user points
    Route::get('showPoints', [PointsController::class, 'getPoints']);
    Route::get('addPoints', [PointsController::class, 'addPoints']);
//cart item      testing
    Route::get('cart-items', [CartItemController::class, 'index']);
    Route::post('cart-items', [CartItemController::class, 'store']);
    Route::put('cart-items/{cartItem}', [CartItemController::class, 'update']);
    Route::delete('cart-items/{cartItem}', [CartItemController::class, 'destroy']);
    Route::post('preview-price', [CartItemController::class, 'previewSelectedItemsPrice']);
//orders---------------------
    Route::post('/orders/confirm', [OrderController::class, 'confirm']);
});

//--------------------------api  fatima
Route::middleware('auth:api')->group(function () {

    // --- Admin: Suppliers ---
    Route::apiResource('suppliers', SupplierController::class);

    // --- Admin: Warehouse Design ---
    Route::prefix('warehouse-design')->group(function () {
        // Zones
        Route::get('zones', [WarehouseDesignController::class, 'indexZones']);
        Route::post('zones', [WarehouseDesignController::class, 'storeZone']);
        Route::get('zones/{id}', [WarehouseDesignController::class, 'showZone']);
        Route::put('zones/{id}', [WarehouseDesignController::class, 'updateZone']);
        Route::delete('zones/{id}', [WarehouseDesignController::class, 'deleteZone']);

        // Cabinets
        Route::get('cabinets', [WarehouseDesignController::class, 'indexCabinets']);
        Route::post('cabinets', [WarehouseDesignController::class, 'storeCabinet']);
        Route::post('cabinets-with-shelves', [WarehouseDesignController::class, 'storeCabinetWithShelves']);
        Route::get('cabinets/{id}', [WarehouseDesignController::class, 'showCabinet']);
        Route::put('cabinets/{id}', [WarehouseDesignController::class, 'updateCabinet']);
        Route::delete('cabinets/{id}', [WarehouseDesignController::class, 'deleteCabinet']);
        Route::get('cabinets/{id}/coordinates', [WarehouseDesignController::class, 'getCabinetWithCoordinates']);

        // Shelves
        Route::get('shelves', [WarehouseDesignController::class, 'indexShelves']);
        Route::post('shelves', [WarehouseDesignController::class, 'storeShelf']);
        Route::get('shelves/{id}', [WarehouseDesignController::class, 'showShelf']);

        // Coordinates
        Route::get('coordinates', [WarehouseDesignController::class, 'indexCoordinate']);
        Route::post('coordinates', [WarehouseDesignController::class, 'storeCoordinate']);
        Route::post('coordinates/{id}/assign-zone', [WarehouseDesignController::class, 'assignZone']);
    });

    // --- Warehouse Keeper: Purchase Orders ---
    Route::prefix('purchase-orders')->group(function () {
        Route::post('/', [PurchaseOrderController::class, 'store']);
        Route::get('pending', [PurchaseOrderController::class, 'getPendingOrders']);
        Route::get('processed', [PurchaseOrderController::class, 'getProcessedOrders']);
        Route::get('unstored-summary', [PurchaseOrderController::class, 'getUnstoredOrdersSummary']);
        Route::get('{orderId}/details', [PurchaseOrderController::class, 'showPurchaseOrderDetails']);
        Route::post('{orderId}/process-partial-receipt', [PurchaseOrderController::class, 'processPartialReceipt']);
        Route::get('{orderId}/unstored-items', [PurchaseOrderController::class, 'getUnstoredOrderItems']);
        Route::get('{orderId}/invoice', [PurchaseOrderController::class, 'showAsInvoice']);
        Route::get('{orderId}/pdf', [PurchaseOrderController::class, 'exportToPdf']);
    });

    // Get supplier specific items
    Route::get('suppliers/{supplier}/items', [PurchaseOrderController::class, 'getBySupplier']);

    // Update receipt item dates
    Route::put('receipt-items/{item}/production-date', [PurchaseOrderController::class, 'updateProductionDate']);
    Route::put('receipt-items/{item}/expiry-date', [PurchaseOrderController::class, 'updateExpiryDate']);
    Route::get('items/expiring-soon', [PurchaseOrderController::class, 'getExpiringSoon']);


    // --- Warehouse Keeper: Item Storage ---
    Route::prefix('storage')->group(function () {
        Route::post('store-item', [ItemStorageController::class, 'storeItem']);
        Route::post('store-item-auto', [ItemStorageController::class, 'storeItemAuto']);
        Route::get('item-details/{purchaseReceiptItemId}', [ItemStorageController::class, 'getItemDetails']);
        Route::post('shelf-capacity', [ItemStorageController::class, 'getShelfCapacity']);
        Route::get('shelf-statuses/{purchaseReceiptItemId}', [ItemStorageController::class, 'getShelfStatuses']);
        Route::get('cabinets/{cabinetId}/summary', [ItemStorageController::class, 'getCabinetSummary']);
        Route::get('shelves/{shelfId}/details', [ItemStorageController::class, 'getShelfDetails']);

        // Suggestions for a specific item
        Route::prefix('items/{item}/suggestions')->group(function () {
            Route::get('zones', [ItemStorageController::class, 'suggestedZones']);
            Route::get('zones-with-cabinets', [ItemStorageController::class, 'suggestedZonesWithCabinets']);
            Route::get('cabinets', [ItemStorageController::class, 'suggestedCabinets']);
            Route::get('shelves', [ItemStorageController::class, 'suggestedShelves']);
        });
    });

    // --- Inventory & Stocktake ---
    Route::prefix('inventory')->group(function () {
        Route::post('request-stocktake', [InventoryController::class, 'requestStocktake']);
        Route::post('submit-stocktake/{stocktakeId}', [InventoryController::class, 'submitStocktake']);
        Route::get('reports', [InventoryController::class, 'getStocktakeReports']);
        Route::get('reports/{id}', [InventoryController::class, 'getStocktakeReportDetails']);
        Route::put('scheduled-stocktake/{id}', [InventoryController::class, 'updateScheduledStocktake']);
        Route::delete('scheduled-stocktake/{id}', [InventoryController::class, 'cancelScheduledStocktake']);
    });

    // --- Notifications ---
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::post('/send', [NotificationController::class, 'sendToUser']);
        Route::post('/store-device-token', [NotificationController::class, 'storeUserDeviceToken']);
        Route::put('/{notificationId}/mark-as-seen', [NotificationController::class, 'markAsSeen']);
    });

});
