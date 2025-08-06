<?php
namespace App\Services\Orders;
use App\Models\Order;
use App\Repositories\Costumer\OrderBatchDetailRepository;
use App\Repositories\Costumer\PurchaseReceiptItemRepository;

class FifoStockDeductionService{

    public function __construct(
        protected OrderBatchDetailRepository $orderBatchDetailRepository ,
        protected PurchaseReceiptItemRepository $purchaseReceiptItemRepository,
        protected OrderService $orderService
    )
    {
    }

    public function deductStockFromBatches(Order $order): void
    {
        foreach ($order->orderItems as $orderItem) {
            $item = $orderItem->itemUnit->item;

            $qtyToDeduct = $this->orderService->calculateQuantityInBaseUnit(
                $item->id,
                $orderItem->itemUnit->unit_id,
                $orderItem->itemUnit->conversion_factor,
                $orderItem->quantity
            );


            // جلب الدفعات الأقدم (FIFO)
            $batches = $this->purchaseReceiptItemRepository->getOlderPurchasOfItem($item->id);

            foreach ($batches as $batch) {
                if ($qtyToDeduct <= 0) break;

                $available = $batch->available_quantity;
                $used = min($qtyToDeduct, $available);

                // خصم الكمية
                $batch->decrement('available_quantity', $used);
                $qtyToDeduct -= $used;

                // إنشاء سجل في order_batch_details
                $this->orderBatchDetailRepository->create([
                    'order_item_id' => $orderItem->id,
                    'purchase_receipt_item_id' => $batch->id,
                    'quantity' => $used,
                    ]);

            }

            if ($qtyToDeduct > 0) {
                throw new \Exception("الكمية المطلوبة للمنتج '{$item->name}' غير متوفرة بشكل كافٍ.");
            }
        }
    }


}
