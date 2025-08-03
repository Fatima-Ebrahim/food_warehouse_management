<?php
namespace App\Repositories\WarehouseKeeperRepository;

use App\Models\Item;
use App\Models\PurchaseOrder;
use App\Models\PurchaseReceiptItem;
use Illuminate\Support\Facades\DB;

class PurchaseOrderRepository
{
    public function createWithItems(array $orderData, array $itemsData)
    {
        return DB::transaction(function () use ($orderData, $itemsData) {
            $orderData['order_date'] = isset($orderData['order_date']) ? $orderData['order_date'] : now();

            $purchaseOrder = PurchaseOrder::create($orderData);
            $purchaseOrder->po_number = 'PO-' . $purchaseOrder->id;
            $purchaseOrder->save();

            foreach ($itemsData as $item) {
                $unitWeight = isset($item['unit_weight']) ? $item['unit_weight'] : 0;
                $quantity = $item['quantity'];

                $purchaseOrder->purchaseItems()->create([
                    'item_id' => $item['item_id'],
                    'unit_id' => $item['unit_id'],
                    'quantity' => $quantity,
                    'available_quantity' => $quantity,
                    'price' => $item['price'],
                    'total_price' => $quantity * $item['price'],
                    'unit_weight' => $unitWeight,
                    'total_weight' => $quantity * $unitWeight
                ]);
            }
            $this->recalculateOrderTotal($purchaseOrder);
            return $purchaseOrder->load('purchaseItems');
        });
    }

    private function recalculateOrderTotal(PurchaseOrder $order)
    {
        $order->load('purchaseItems');
        $order->total_amount = $order->purchaseItems->sum('total_price');
        $order->save();
    }

    public function getByStatus( $status)
    {
        return PurchaseOrder::where('receipt_status', $status)
            ->with([
                'supplier:id,name',
                'purchaseItems:id,purchase_order_id,item_id,unit_id,quantity,price',
                'purchaseItems.item:id,name,code,description',
                'purchaseItems.unit:id,name'
            ])
            ->latest()
            ->get();
    }

    public function getPurchaseOrderDetails( $orderId)
    {
        return PurchaseOrder::with([
            'supplier:id,name',
            'purchaseItems.item:id,name,code',
            'purchaseItems.unit:id,name'
        ])->select([
            'id', 'po_number', 'supplier_id', 'order_date',
            'expected_delivery_date', 'receipt_status', 'receipt_date',
            'receipt_number', 'order_notes', 'receipt_notes',
            'total_amount',
            'created_at', 'updated_at'
        ])->findOrFail($orderId);
    }

    public function updatePartialReceipt( $orderId, array $receiptData, array $itemsData)
    {
        return DB::transaction(function () use ($orderId, $receiptData, $itemsData) {
            $order = PurchaseOrder::findOrFail($orderId);

            $order->update([
                'receipt_status' => $receiptData['status'],
                'receipt_date' => now(),
                'receipt_number' => 'RC-' . $order->id,
                'receipt_notes' => isset($receiptData['notes']) ? $receiptData['notes'] : $order->receipt_notes
            ]);

            foreach ($itemsData as $itemData) {
                $item = $order->purchaseItems()->findOrFail($itemData['id']);

                $quantity = isset($itemData['quantity']) ? $itemData['quantity'] : $item->quantity;
                $price = isset($itemData['price']) ? $itemData['price'] : $item->price;
                $unitWeight = isset($itemData['unit_weight']) ? $itemData['unit_weight'] : $item->unit_weight;

                $item->update([
                    'quantity' => $quantity,
                    'price' => $price,
                    'unit_weight' => $unitWeight,
                    'total_price' => $quantity * $price,
                    'total_weight' => $quantity * $unitWeight,
                    'notes' => isset($itemData['notes']) ? $itemData['notes'] : $item->notes,
                ]);
            }

            $this->recalculateOrderTotal($order);
            return $order->load(['supplier', 'purchaseItems.item', 'purchaseItems.unit']);
        });
    }

    public function getNonPendingOrders(array $statuses = null, array $withRelations = [])
    {
        $query = PurchaseOrder::where('receipt_status', '!=', 'pending');
        if ($statuses) {
            $query->whereIn('receipt_status', $statuses);
        }
        return $query->with($withRelations)->orderBy('receipt_date', 'desc')->get();
    }

    public function updateProductionDate( $itemId,  $date)
    {
        return $this->updateReceiptItemDate($itemId, 'production_date', $date);
    }

    public function updateExpiryDate( $itemId,  $date)
    {
        return $this->updateReceiptItemDate($itemId, 'expiry_date', $date);
    }

    private function updateReceiptItemDate( $itemId,  $column,  $date)
    {
        $item = PurchaseReceiptItem::findOrFail($itemId);
        $item->update([$column => $date]);
        return $item;
    }

    public function getItemsByDateRange( $column,  $startDate,  $endDate)
    {
        return PurchaseReceiptItem::whereBetween($column, [$startDate, $endDate])
            ->with(['item', 'purchaseOrder.supplier'])
            ->get();
    }

    public function getForInvoice( $orderId)
    {
        return PurchaseOrder::with([
            'supplier:id,name,email,phone,address',
            'purchaseItems.item:id,name,code,barcode',
            'purchaseItems.unit:id,name'
        ])->find($orderId);
    }

    public function getUnstoredOrdersSummary()
    {
        $orders = PurchaseOrder::whereHas('purchaseItems', function ($query) {
            $query->whereRaw('quantity > (SELECT COALESCE(SUM(quantity), 0) FROM batch_storage_locations WHERE batch_storage_locations.purchase_receipt_items_id = purchase_receipt_items.id)');
        })
            ->with(['supplier:id,name'])
            ->get();

        return $orders->map(function ($order) {
            $unstoredItemsCount = $order->purchaseItems->filter(function ($item) {
                $item->loadMissing('storageLocation');
                $storedQuantity = $item->storageLocation->sum('quantity');
                return ($item->quantity - $storedQuantity) > 0;
            })->count();

            if ($unstoredItemsCount === 0) return null;

            return [
                'order_id' => $order->id,
                'po_number' => $order->po_number,
                'supplier_name' => $order->supplier->name,
                'order_date' => $order->order_date->format('Y-m-d'),
                'unstored_items_count' => $unstoredItemsCount,
                'total_amount' => (float) $order->total_amount,
            ];
        })->filter()->values();
    }

    public function getUnstoredOrderItemsDetails( $orderId)
    {
        $order = PurchaseOrder::where('id', $orderId)
            ->whereHas('purchaseItems', function ($query) {
                $query->whereRaw('quantity > (SELECT COALESCE(SUM(quantity), 0) FROM batch_storage_locations WHERE batch_storage_locations.purchase_receipt_items_id = purchase_receipt_items.id)');
            })
            ->with([
                'supplier:id,name',
                'purchaseItems.item:id,name,code',
                'purchaseItems.unit:id,name',
                'purchaseItems.storageLocation:purchase_receipt_items_id,quantity'
            ])
            ->first();

        if (!$order) return null;

        $filteredItems = $order->purchaseItems->map(function ($item) {
            $storedQuantity = $item->storageLocation->sum('quantity');
            $unstoredQuantity = $item->quantity - $storedQuantity;
            if ($unstoredQuantity <= 0) return null;

            return [
                'purchase_receipt_item_id' => $item->id,
                'item_id' => $item->item->id,
                'weight_per_unit' => (float) $item->unit_weight,
                'item_code' => $item->item->code,
                'item_name' => $item->item->name,
                'ordered_quantity' => (float) $item->quantity,
                'stored_quantity' => (float) $storedQuantity,
                'unstored_quantity' => (float) $unstoredQuantity,
            ];
        })->filter()->values();

        $order->setRelation('purchaseItems', $filteredItems);
        return $order;
    }

    public function getItemsBySupplier( $supplierId, array $filters = [])
    {
        $query = Item::query()->where('supplier_id', $supplierId);
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }
        return $query->get();
    }
}
