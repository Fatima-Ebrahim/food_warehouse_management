<?php

use App\Http\Controllers\Admin\FinancialReportsController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SalesAnalysisReportController;
use App\Http\Controllers\Admin\SalesReportController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\WarehouseDesignController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Customer\CategoryController;
use App\Http\Controllers\Customer\ItemController;
use App\Http\Controllers\Customer\ItemUnitController;
use App\Http\Controllers\Customer\RegisterRequestController;
use App\Http\Controllers\Customer\SettingsController;
use App\Http\Controllers\Customer\UnitContoller;
use App\Http\Controllers\Admin\InventoryReportController;
use App\Http\Controllers\Notification\NotificationController;
use App\Http\Controllers\Orders\CartItemController;
use App\Http\Controllers\Orders\InstallmentController;
use App\Http\Controllers\Orders\OrderController;
use App\Http\Controllers\Orders\PointsController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\specialOfferController;
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
Route::get('showAll', [RegisterRequestController::class, 'getAllRegistration']);
Route::post('registerRequests', [RegisterRequestController::class, 'store']);
Route::get('showById/{id}', [RegisterRequestController::class, 'show']);
Route::get('showCertificate/{id}/certificate', [RegisterRequestController::class, 'showCertificate']);
Route::patch('/register-requests/{id}/status', [RegisterRequestController::class, 'updateStatus']);
Route::put('changePassword', [AuthController::class, 'addPassword']);
//----------------


Route::get('getLastLevelForCat/{catId}', [CategoryController::class, 'getLastLevelForCat']);
//category
Route::post('addCategory', [CategoryController::class, 'store']);
Route::get('showCategories', [CategoryController::class, 'getAllCategories']);
Route::get('showSubCategories/{parent_id}', [CategoryController::class, 'getSubCategories']);
Route::get('lastLevel', [CategoryController::class, 'showLastLevel']);
Route::get('getAllCategoriesWithChildAndItems', [CategoryController::class, 'getAllCategoriesWithChildAndItems']);
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
Route::get('showItemImage/{id}', [ItemController::class, 'showItemImage']);
Route::get('getAllReceiptItemForItem/{itemId}',[ItemController::class,'getAllReceiptItemForItem']);
//itemUnit
Route::post('/addItemUnit', [ItemUnitController::class, 'store']);
Route::get('showItemUnit', [ItemUnitController::class, 'show']);
//Route::get('showAllItemUnit/{itemId}', [ItemUnitController::class, 'showAll']);
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

Route::middleware('auth:api')->group(function () {


    Route::get('getItemFIFORecommendation/{order}',[ItemController::class,'getItemFIFORecommendation']);


    //user points
    Route::get('showPoints', [PointsController::class, 'getPoints']);
//    Route::get('addPoints', [PointsController::class, 'addPoints']);
//cart item
    Route::get('cart-items', [CartItemController::class, 'showAllCartItems']);
    Route::post('cart-items', [CartItemController::class, 'addToCart']);
    Route::put('cart-items/{type}/{id}', [CartItemController::class, 'update']) ->where('type', 'item|offer');
    Route::delete('cart-items/{type}/{id}', [CartItemController::class, 'destroy'])->where('type', 'item|offer');
    Route::post('preview-price', [CartItemController::class, 'previewSelectedItemsPrice']);
//orders---------------------
    Route::post('/orders/confirm', [OrderController::class, 'confirm']);
    Route::get('getOrderDetails/{orderId}', [OrderController::class, 'getOrderDetails']);
    Route::get('showOrderQr/{orderId}', [OrderController::class, 'getOrderQr']);
    Route::get('getPendedOrders', [OrderController::class, 'getPendingOrders']);
    Route::put('updateOrderStatus', [OrderController::class, 'updateOrderStatus']);
    Route::post('deliverOrder', [OrderController::class, 'deliverOrder']);
    Route::get('getUserActiveOrders',[OrderController::class,'getUserActiveOrders']);
    Route::get('getUserPendingOrders',[OrderController::class,'getUserPendingOrders']);
    Route::get('getOrderBatches/{orderId}',[OrderController::class,'getOrderBatches']);
//    installment
    Route::get('getOrderInstallmentPlan/{orderId}', [InstallmentController::class, 'getOrderInstallmentPlan']);
    Route::get('getOrderInstallmentsBatchs/{orderId}', [InstallmentController::class, 'getOrderInstallmentsBatchs']);
    Route::get('getUserUnpaidInstallments', [InstallmentController::class, 'getUserUnpaidInstallments']);
    Route::post('payNextInstallment', [InstallmentController::class, 'payNextInstallment']);

//payment methods
    Route::get('paymentMethods', [PaymentController::class, 'paymentMethods']);
    ///todo additional orders
    Route::post('addAdditionalOrder');
    Route::get('showAdditionalOrders');
    Route::get('showAdditionalOrderDetails/{id}');
    Route::put('updateAdditionalOrderStatus/{id}');


    //todo special offers
    Route::Post('addSpecialOrder',[specialOfferController::class,'create']);
    Route::get('showActiveOffers',[specialOfferController::class,'getActiveOffers']);
    Route::get('showInactiveOffers',[specialOfferController::class,'getInactiveOffers']);
    Route::get('showAllOffers',[specialOfferController::class,'getAllOffers']);
    Route::put('updateOfferStatus',[specialOfferController::class,'updateOfferStatus']);
    Route::delete('deleteOffer/{offerId}',[specialOfferController::class,'destroy']);
    //todo update offer
//    Route::put('updateOffer/{offerId}', [SpecialOfferController::class, 'update']);
//todo reports
    Route::get('salesReport', [ReportController::class, 'salesReport']);


    Route::get('/expired-items/pdf', [PurchaseOrderController::class, 'exportExpiredItemsToPdf']);

    Route::prefix('reports/inventory')->group(function () {
        Route::get('currentStock', [InventoryReportController::class, 'currentStock']);
        Route::get('lowStock', [InventoryReportController::class, 'lowStock']);
        Route::get('movements', [InventoryReportController::class, 'getStockMovements']);
        Route::get('batches', [InventoryReportController::class, 'batchesStatus']);
        Route::get('slowMoving', [InventoryReportController::class, 'slowMoving']);
        Route::get('topMoving', [InventoryReportController::class, 'topMoving']);
    });


    Route::prefix('reports/sales')->group(function () {
        Route::get('customerStatement/{userId}', [SalesReportController::class, 'customerStatement']);
        Route::get('byCustomer',       [SalesReportController::class, 'salesByCustomer']);
        Route::get('byProduct',        [SalesReportController::class, 'salesByProduct']);
        Route::get('aggregate',         [SalesReportController::class, 'aggregateSales']);
        Route::get('byPaymentType',   [SalesReportController::class, 'salesByPaymentType']);
        Route::get('deliveryStatus',   [SalesReportController::class, 'ordersDeliveryStatus']);
        Route::get('topCustomers',     [SalesReportController::class, 'topCustomers']);
    });

    Route::prefix('reports/financial')->group(function () {
        Route::get('netProfitPerItem', [FinancialReportsController::class, 'netProfitPerItem']);
        Route::get('accountsReceivable', [FinancialReportsController::class, 'accountsReceivable']);
        Route::get('netProfitPerOffers', [FinancialReportsController::class, 'getNetProfitPerOffers']);
        Route::get('netProfit', [FinancialReportsController::class, 'netProfit']);
    });

    Route::prefix('reports')->group(function (){
        Route::get('salesComparison', [SalesAnalysisReportController::class, 'compareSales']);
        Route::get('getInventoryTurnover', [SalesAnalysisReportController::class, 'getInventoryTurnover']);
        Route::get('getOffersImpactWithIncrease', [SalesAnalysisReportController::class, 'getOffersImpactWithIncrease']);

    });

    ///todo search
    Route::post('search');


});



//--------------------------api  fatima
Route::middleware('auth:api')->group(function () {

    // --- Admin: Suppliers ---
    Route::apiResource('suppliers', SupplierController::class);

    // --- Admin: Warehouse Design ---


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
//        Route::get('{orderId}/pdf', [PurchaseOrderController::class, 'exportToPdf']);
    });
    Route::get('/purchase-receipt-items/{purchaseReceiptItemId}/cabinet-shelf-statuses', [ItemStorageController::class, 'getShelfStatuses']);
    // المواد يلي حنخلص صلاحيتها
    Route::get('/items/expired', [PurchaseOrderController::class, 'getExpiredItemsJson']);
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
//        Route::get('item-details/{purchaseReceiptItemId}', [ItemStorageController::class, 'getItemDetails']);
        Route::post('shelf-capacity', [ItemStorageController::class, 'getShelfCapacity']);
        Route::get('shelf-statuses/{purchaseReceiptItemId}', [ItemStorageController::class, 'getShelfStatuses']);

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
        // Scheduled Stocktakes Management
        Route::get('/scheduled-stocktakes', [InventoryController::class, 'getScheduledStocktakes']);
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

    //     reports
//    Route::prefix('reports')->group(function () {
////        Route::get('/low-stock', [LowStockReportController::class, 'getReport']);
//        Route::get('/expired-items/pdf', [PurchaseOrderController::class, 'exportExpiredItemsToPdf']);
//    });
});
Route::get('/test', function () {
    return 'ok';
});
Route::get('purchase-orders/{orderId}/pdf', [PurchaseOrderController::class, 'exportToPdf']);
Route::get('item-details/{purchaseReceiptItemId}', [ItemStorageController::class, 'getItemDetails']);
Route::get('items/{item}/suggested-zones-with-cabinets', [ItemStorageController::class, 'suggestedZonesWithCabinets']);
Route::get('/unstored-orders/{orderId}/items', [PurchaseOrderController::class, 'getUnstoredOrderItems']);
Route::get('/unstored-orders-summary', [PurchaseOrderController::class, 'getUnstoredOrdersSummary']);

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
    Route::post('/cabinets-with-shelves', [WarehouseDesignController::class, 'storeCabinetWithShelves']);
    Route::get('shelves', [WarehouseDesignController::class, 'indexShelves']);
    Route::post('shelves', [WarehouseDesignController::class, 'storeShelf']);
    Route::get('shelves/{id}', [WarehouseDesignController::class, 'showShelf']);

    // Coordinates
    Route::get('coordinates', [WarehouseDesignController::class, 'indexCoordinate']);
    Route::post('coordinates', [WarehouseDesignController::class, 'storeCoordinate']);
    Route::post('coordinates/{id}/assign-zone', [WarehouseDesignController::class, 'assignZone']);

    Route::get('status/complete', [WarehouseDesignController::class, 'setComplete']);
    Route::get('status', [WarehouseDesignController::class, 'getStatus']);
});
Route::get('storage/shelves/{shelfId}/details', [ItemStorageController::class, 'getShelfDetails']);
Route::get('cabinets/{cabinetId}/summary', [ItemStorageController::class, 'getCabinetSummary']);
