<?php

namespace App\Http\Controllers\WarehouseKeeper;

use App\Http\Controllers\Controller;
use App\Http\Requests\RemoveQuantityFromShelfRequest;
use App\Http\Requests\WarehouseKeeperRequests\PurchaseOrderRequests\GetItemsBySupplierRequest;
use App\Http\Requests\WarehouseKeeperRequests\PurchaseOrderRequests\ProcessPartialReceiptRequest;
use App\Http\Requests\ReportDamagedItemRequest;
use App\Http\Requests\WarehouseKeeperRequests\PurchaseOrderRequests\StorePurchaseOrderRequest;
use App\Http\Requests\WarehouseKeeperRequests\PurchaseOrderRequests\UpdateExpiryDateRequest;
use App\Http\Requests\WarehouseKeeperRequests\PurchaseOrderRequests\UpdateProductionDateRequest;
use App\Services\WarehouseKeeperService\PurchaseOrderService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Exception;
use niklasravnsborg\LaravelPdf\Facades\Pdf;

class PurchaseOrderController extends Controller
{
    protected $purchaseOrderService;

    public function __construct(PurchaseOrderService $purchaseOrderService)
    {
        $this->purchaseOrderService = $purchaseOrderService;
    }

    public function store(StorePurchaseOrderRequest $request)
    {
        try {
            $order = $this->purchaseOrderService->createPurchaseOrder($request->validated());
            return response()->json(['success' => true, 'data' => $order, 'message' => 'Purchase order created successfully'], 201);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to create purchase order: ' . $e->getMessage()], 400);
        }
    }

    public function getPendingOrders()
    {
        try {
            $orders = $this->purchaseOrderService->getOrdersByStatus('pending');
            return response()->json(['success' => true, 'data' => $orders, 'message' => 'Pending orders retrieved successfully']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to retrieve orders: ' . $e->getMessage()], 500);
        }
    }

    public function showPurchaseOrderDetails($orderId)
    {
        try {
            $order = $this->purchaseOrderService->getPurchaseOrderWithDetails($orderId);
            return response()->json(['success' => true, 'data' => $order, 'message' => 'Purchase order details retrieved successfully']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Purchase order not found'], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to retrieve data: ' . $e->getMessage()], 500);
        }
    }

    public function processPartialReceipt(ProcessPartialReceiptRequest $request, $orderId)
    {
        try {
            $order = $this->purchaseOrderService->processPartialReceipt($orderId, $request->validated());
            return response()->json(['success' => true, 'data' => $order, 'message' => 'Receipt processed successfully']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to process receipt: ' . $e->getMessage()], 400);
        }
    }

    public function getProcessedOrders(Request $request)
    {
        try {
            $orders = $this->purchaseOrderService->getProcessedOrders($request->all());
            return response()->json(['success' => true, 'data' => $orders, 'message' => 'Processed orders retrieved successfully']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to retrieve processed orders: ' . $e->getMessage()], 500);
        }
    }

    public function updateProductionDate(UpdateProductionDateRequest $request, $itemId)
    {
        try {
            $item = $this->purchaseOrderService->setProductionDate($itemId, $request->validated()['date']);
            return response()->json(['success' => true, 'data' => $item, 'message' => 'Production date updated successfully']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update production date: ' . $e->getMessage()], 400);
        }
    }

    public function updateExpiryDate(UpdateExpiryDateRequest $request, $itemId)
    {
        try {
            $item = $this->purchaseOrderService->setExpiryDate($itemId, $request->validated()['date']);
            return response()->json(['success' => true, 'data' => $item, 'message' => 'Expiry date updated successfully']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update expiry date: ' . $e->getMessage()], 400);
        }
    }

    public function getExpiringSoon(Request $request)
    {
        $items = $this->purchaseOrderService->getItemsNearExpiry($request->input('days', 30));
        return response()->json(['success' => true, 'data' => $items]);
    }

    public function showAsInvoice($orderId)
    {
        try {
            $invoice = $this->purchaseOrderService->getPurchaseOrderAsInvoice($orderId);
            return response()->json(['success' => true, 'data' => $invoice, 'message' => 'Invoice fetched successfully.']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Invoice not found.'], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred while fetching the invoice: ' . $e->getMessage()], 500);
        }
    }

  /*  public function exportToPdf($orderId)
    {
        try {
            $invoiceData = $this->purchaseOrderService->getPurchaseOrderAsInvoice($orderId);
            $pdf = PDF::loadView('invoices.purchase_invoice', ['invoice' => $invoiceData]);
            return $pdf->stream('invoice-' . $invoiceData['invoice_header']['invoice_number'] . '.pdf');
        } catch (ModelNotFoundException $e) {
            return response("Invoice not found", 404);
        }
    }*/
    public function exportToPdf($orderId)
    {
        try {
            $invoiceData = $this->purchaseOrderService->getPurchaseOrderAsInvoice($orderId);

            // قم بتحديد اسم الخط الافتراضي هنا
            $pdf = PDF::loadView('invoices.purchase_invoice', ['invoice' => $invoiceData], [], [
                'default_font' => 'tajawal'
            ]);

            $fileName = 'invoice-' . $invoiceData['invoice_header']['invoice_number'] . '.pdf';

            return $pdf->stream($fileName);

        } catch (ModelNotFoundException $e) {
            return response("Invoice not found", 404);
        }}
    public function getUnstoredOrdersSummary()
    {
        $summary = $this->purchaseOrderService->getUnstoredOrdersSummary();
        return response()->json(['success' => true, 'data' => $summary, 'message' => 'Summary of unstored orders retrieved successfully']);
    }

    public function getUnstoredOrderItems($orderId)
    {
        try {
            $order = $this->purchaseOrderService->getUnstoredOrderItems($orderId);
            return response()->json(['success' => true, 'data' => $order, 'message' => 'Unstored items for the order retrieved successfully']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Order not found or has no unstored items.'], 404);
        }
    }

//    public function exportExpiredItemsToPdf()
//    {
//        try {
//            $reportData = $this->purchaseOrderService->getExpiredItemsForReport();
//
//            $pdf = PDF::loadView('reports.expired_items_report', ['report' => $reportData], [], [
//                'default_font' => 'tajawal'
//            ]);
//
//            return $pdf->stream('expired-items-report-' . now()->format('Y-m-d') . '.pdf');
//
//        } catch (Exception $e) {
//            return response()->json(['success' => false, 'message' => 'حدث خطأ أثناء إنشاء التقرير: ' . $e->getMessage()], 500);
//        }
//    }

    public function getExpiredItemsJson()
    {
        try {
            $items = $this->purchaseOrderService->getExpiredItemsWithDetailedLocations();

            return response()->json([
                'success' => true,
                'data' => $items,
                'message' => 'Expired items retrieved successfully.'
            ]);

        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to retrieve expired items: ' . $e->getMessage()], 500);
        }
    }
    // TODO
    public function getBySupplier(GetItemsBySupplierRequest $request, $supplierId)
    {
        $items = $this->purchaseOrderService->getItemsBySupplier($supplierId, $request->validated());
        return response()->json(['success' => true, 'data' => $items, 'message' => 'Items retrieved successfully for supplier ' . $supplierId]);
    }
    public function reportDamage(ReportDamagedItemRequest $request, $receiptItemId)
    {
        try {
            $validatedData = $request->validated();

            $this->purchaseOrderService->handleDamagedItems(
                $receiptItemId,
                $validatedData['locations'],
                $validatedData['reason'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Damaged items were reported and inventory has been updated successfully.'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to report damaged items: ' . $e->getMessage()
            ], 400);
        }
    }
    public function removeQuantityFromShelf(RemoveQuantityFromShelfRequest $request)
    {
        try {
            $this->purchaseOrderService->handleRemoveQuantityFromShelf($request->validated());
            return response()->json(['success' => true, 'message' => 'تمت إزالة الكمية من الرف وتحديث المخزون بنجاح.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'فشلت العملية: ' . $e->getMessage()], 400);
        }
    }
}
