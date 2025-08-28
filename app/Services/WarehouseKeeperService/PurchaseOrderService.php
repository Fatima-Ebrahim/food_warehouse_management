<?php
namespace App\Services\WarehouseKeeperService;

use App\Models\User;
use App\Notifications\NewPurchaseOrderForKeeper;
use App\Notifications\PurchaseOrderCompletedForAdmin;
use App\Repositories\WarehouseKeeperRepository\PurchaseOrderRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Notification;

class PurchaseOrderService
{
    protected $purchaseOrderRepository;

    public function __construct(PurchaseOrderRepository $purchaseOrderRepository)
    {
        $this->purchaseOrderRepository = $purchaseOrderRepository;
    }

    public function createPurchaseOrder(array $data)
    {
        $orderData = [
            'supplier_id' => $data['supplier_id'],
            'expected_delivery_date' => $data['expected_delivery_date'],
            'order_notes' => isset($data['order_notes']) ? $data['order_notes'] : null,
        ];

        $purchaseOrder = $this->purchaseOrderRepository->createWithItems($orderData, $data['items']);

        $warehouseKeepers = User::getWarehouseKeepers();
        if ($warehouseKeepers->isNotEmpty()) {
            Notification::send($warehouseKeepers, new NewPurchaseOrderForKeeper($purchaseOrder));
        }

        return $purchaseOrder;
    }

    public function getOrdersByStatus( $status)
    {
        return $this->purchaseOrderRepository->getByStatus($status);
    }

    public function getPurchaseOrderWithDetails( $orderId)
    {
        $order = $this->purchaseOrderRepository->getPurchaseOrderDetails($orderId);

        $formattedItems = $order->purchaseItems->map(function ($item) {
            return [
                'id' => $item->id,
                'item' => $item->item,
                'unit' => $item->unit,
                'ordered_quantity' => (float)$item->quantity,
                'unit_price' => (float)$item->price,
                'total_price' => (float)$item->total_price,
                'notes' => $item->notes,
            ];
        });

        return [
            'order_info' => [
                'id' => $order->id,
                'po_number' => $order->po_number,
                'order_date' => $order->order_date->format('Y-m-d'),
                'receipt_status' => $order->receipt_status,
                'total_amount' => (float)$order->total_amount,
                'created_at' => $order->created_at->format('Y-m-d H:i'),
            ],
            'supplier' => $order->supplier,
            'items' => $formattedItems,
        ];
    }

    public function processPartialReceipt( $orderId, array $receiptData)
    {
        $itemsData = isset($receiptData['items']) ? $receiptData['items'] : [];
        $order = $this->purchaseOrderRepository->updatePartialReceipt($orderId, $receiptData, $itemsData);

        if (isset($receiptData['status']) && $receiptData['status'] !== 'pending') {
            $admins = User::getAdmins();
            if ($admins->isNotEmpty()) {
                Notification::send($admins, new PurchaseOrderCompletedForAdmin($order));
            }
        }
        return $order;
    }

    public function getProcessedOrders(array $filters = [])
    {
        $statuses = isset($filters['statuses']) ? $filters['statuses'] : null;
        $orders = $this->purchaseOrderRepository->getNonPendingOrders($statuses, ['supplier', 'purchaseItems.item', 'purchaseItems.unit']);

        return $orders->map(function($order) {
            return $this->formatOrderDetails($order);
        });
    }

    public function formatOrderDetails($order)
    {
        return [
            'order_id' => $order->id,
            'po_number' => $order->po_number,
            'receipt_number' => $order->receipt_number,
            'order_date' => $order->order_date->format('Y-m-d'),
            'receipt_date' => isset($order->receipt_date) ? $order->receipt_date->format('Y-m-d') : null,
            'status' => $order->receipt_status,
            'supplier' => $order->supplier,
            'total_items' => $order->purchaseItems->count(),
            'total_amount' => (float)$order->total_amount,
        ];
    }

    public function setProductionDate( $itemId,  $date)
    {
        return $this->purchaseOrderRepository->updateProductionDate($itemId, $date);
    }

    public function setExpiryDate( $itemId,  $date)
    {
        return $this->purchaseOrderRepository->updateExpiryDate($itemId, $date);
    }

    public function getItemsNearExpiry($days = 30)
    {
        $startDate = now()->toDate();
        $endDate = now()->addDays((int) $days)->toDate();
        $items = $this->purchaseOrderRepository->getItemsByDateRange('expiry_date', $startDate, $endDate);

        return $items->map(function ($item) {
            $locations = $item->storageLocation->map(function ($location) {

                if ($location->shelf) {
                    return [
                        'shelf_code' => $location->shelf->code,
                        'shelf_id' => $location->shelf->id,
                        'stored_quantity' => (float) $location->quantity
                    ];
                }
                return null;
            })->filter()->values();

            return [
                'purchase_receipt_item_id' => $item->id,
                'item_id' => $item->item->id,
                'item_name' => $item->item->name,
                'item_code' => $item->item->code,
                'expiry_date' => $item->expiry_date->format('Y-m-d'),
                'quantity_in_batch' => (float) $item->quantity,
                'supplier_name' => $item->purchaseOrder->supplier->name,
                'storage_locations' => $locations,
            ];
        });
    }

    public function getPurchaseOrderAsInvoice( $orderId)
    {
        $order = $this->purchaseOrderRepository->getForInvoice($orderId);
        if (!$order) {
            throw new ModelNotFoundException('Invoice not found.');
        }

        $itemsTable = $order->purchaseItems->map(function ($item, $index) {
            return [
                'number' => $index + 1,
                'item_code' => $item->item->code,
                'item_name' => $item->item->name,
                'quantity' => (float) $item->quantity,
                'unit_name' => $item->unit->name,
                'unit_price' => (float) $item->price,
                'total_price' => (float) $item->total_price,
                'unit_weight' => (float) $item->unit_weight,
                'total_weight' => (float) $item->total_weight,
            ];
        });

        return [
            'invoice_header' => [
                'title' => 'Purchase Invoice',
                'invoice_number' => $order->po_number,
                'issue_date' => $order->created_at->format('Y-m-d'),
                'due_date' => $order->expected_delivery_date->format('Y-m-d'),
            ],
            'supplier_details' => [
                'billed_from' => 'Billed From:',
                'name' => $order->supplier->name,
                'address' => $order->supplier->address,
                'phone' => $order->supplier->phone,
                'email' => $order->supplier->email,
            ],
            'order_details' => [
                'invoice_details' => 'Invoice Details:',
                'invoice_no_label' => 'Invoice No:',
                'issue_date_label' => 'Issue Date:',
                'due_date_label' => 'Due Date:',
                'status_label' => 'Status:',
                'status' => $order->receipt_status,
            ],
            'items_table' => $itemsTable,
            'summary' => [
                'total_items_count' => $itemsTable->count(),
                'grand_total' => (float) $order->total_amount,
            ],
            'notes' => [
                'notes_label' => 'Notes:',
                'content' => $order->order_notes,
            ]
        ];
    }

    public function getUnstoredOrdersSummary()
    {
        return $this->purchaseOrderRepository->getUnstoredOrdersSummary();
    }

    public function getUnstoredOrderItems( $orderId)
    {
        $order = $this->purchaseOrderRepository->getUnstoredOrderItemsDetails($orderId);
        if (!$order) {
            throw new ModelNotFoundException('Order not found or has no unstored items.');
        }
        return [
            'order_id' => $order->id,
            'po_number' => $order->po_number,
            'supplier_name' => $order->supplier->name,
            'order_date' => $order->order_date->format('Y-m-d'),
            'items_to_store' => $order->purchaseItems,
        ];
    }

    public function getItemsBySupplier( $supplierId, array $filters = [])
    {
        return $this->purchaseOrderRepository->getItemsBySupplier($supplierId, $filters);
    }
    public function getExpiredItemsForReport()
    {
        $items = $this->purchaseOrderRepository->getExpiredItems();

        $formattedItems = $items->map(function ($item, $index) {
            $locationsText = $item->storageLocation->map(function ($location) {
                return optional($location->shelf)->code . ' (كمية: ' . (float) $location->quantity . ')';
            })->implode(' | ');

            return [
                'number' => $index + 1,
                'item_code' => $item->item->code,
                'item_name' => $item->item->name,
                'expiry_date' => $item->expiry_date->format('Y-m-d'),
                'supplier_name' => $item->purchaseOrder->supplier->name,
                'total_quantity' => (float) $item->quantity,
                'location' => $locationsText ?: 'غير مخزنة',
            ];
        });

        return [
            'report_header' => [
                'title' => 'تقرير المواد منتهية الصلاحية',
                'report_date' => now()->format('Y-m-d'),
            ],
            'items_table' => $formattedItems,
        ];
    }

    public function getExpiredItemsWithDetailedLocations()
    {
        $items = $this->purchaseOrderRepository->getExpiredItems();

        return $items->map(function ($item) {
                $locations = ($item->storageLocation ?? collect())->map(function ($location) {
                    if ($location->shelf) {
                        return [
                            'shelf_id' => $location->shelf->id,
                            'shelf_code' => $location->shelf->code,
                            'stored_quantity' => (float) $location->quantity
                        ];
                    }
                    return null;
            })->filter()->values();

            return [
                'purchase_receipt_item_id' => $item->id,
                'item_id' => $item->item->id,
                'item_name' => $item->item->name,
                'item_code' => $item->item->code,
                'expiry_date' => $item->expiry_date->format('Y-m-d'),
                'quantity_in_batch' => (float) $item->quantity,
                'supplier_name' => optional($item->purchaseOrder->supplier)->name,
                'storage_locations' => $locations,
            ];
        });
    }
    public function handleDamagedItems( $receiptItemId, array $locations, ?string $reason)
    {

        return $this->purchaseOrderRepository->markItemsAsDamaged($receiptItemId, $locations, $reason);
    }
    public function handleRemoveQuantityFromShelf(array $data)
    {
        return $this->purchaseOrderRepository->removeBatchQuantityFromShelf(
            $data['purchase_receipt_item_id'],
            $data['shelf_id'],
            $data['quantity'],
            $data['unit_id']
        );
    }
}
