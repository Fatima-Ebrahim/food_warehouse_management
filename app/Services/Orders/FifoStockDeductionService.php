<?php
namespace App\Services\Orders;
use App\Models\Order;
use App\Models\OrderBatchDetail;
use App\Models\OrderOfferItemBatchDetails;
use App\Models\PurchaseReceiptItem;
use App\Repositories\Costumer\OrderBatchDetailRepository;
use App\Repositories\Costumer\PurchaseReceiptItemRepository;
use Illuminate\Support\Facades\DB;

class FifoStockDeductionService{

    public function __construct(
        protected OrderBatchDetailRepository $orderBatchDetailRepository ,
        protected PurchaseReceiptItemRepository $purchaseReceiptItemRepository,
        protected OrderService $orderService
    )
    {}
    public function FIFORecommendation(Order $order)
    {
        $recommendations = [];

        // 1) المنتجات العادية
        foreach ($order->orderItems as $orderItem) {
            $requestedQty=$orderItem->quantity;
            $recommendations[] = [
                'type' => 'product',
                'order_item_id'=>$orderItem->id,
                'details' => $this->getFIFOItemRecommendation(
                    $orderItem->itemUnit->item,
                    $orderItem->itemUnit->unit_id,
                    $orderItem->itemUnit->conversion_factor,
                    $requestedQty
                )
            ];
        }


        foreach ($order->orderOffer as $orderOffer) {
            $offerData = [
                'type' => 'offer',
                'order_offer_id'=>$orderOffer->id,
                'offer_id' => $orderOffer->offer->id,
                'offer_description' => $orderOffer->offer->description,
                'offer_s_items' => []
            ];
            $requestedQty = $orderOffer->quantity;

            foreach ($orderOffer->offer->items as $offerItem) {
                $requiredQty = $offerItem->required_quantity;
                $item = $offerItem->itemUnit->item;
                $quantity = $requestedQty * $requiredQty;
                $offerData['offer_s_items'][] = array_merge(
                    ['order_offer_item_id' => $offerItem->id],
                    $this->getFIFOItemRecommendation(
                        $item,
                        $offerItem->itemUnit->unit_id,
                        $offerItem->itemUnit->conversion_factor,
                        $quantity
                    )
                );
            }

            $recommendations[] = $offerData;
        }

        return [
            'order_id' => $order->id,
            'recommendations' => $recommendations
        ];
    }

    private function getFIFOItemRecommendation($item, $unitId, $conversionFactor, $requestedQty)
    {
        $qtyToDeduct = $this->orderService->calculateQuantityInBaseUnit(
            $item->id,
            $unitId,
            $conversionFactor,
            $requestedQty
        );

        $batches = $this->purchaseReceiptItemRepository->getOlderPurchasesOfItemWithLimit($item->id, $qtyToDeduct);

        $itemBatches = [];
        $remainingQty = $qtyToDeduct;

        foreach ($batches as $batch) {
            if ($remainingQty <= 0) break;

            $available = $batch->available_quantity;
            $recommended = min($remainingQty, $available);

            $itemBatches[] = [
                'batch_id' => $batch->id,
                'expiry_date' => $batch->expiry_date,
                'available_quantity' => $batch->available_quantity,
                'recommended_quantity' => $recommended,
            ];

            $remainingQty -= $recommended;
        }

        return [
            'item_id' => $item->id,
            'item_name' => $item->name,
            'item_code' => $item->code,
            'required_quantity' => $qtyToDeduct,
            'remaining_quantity' => max(0, $remainingQty),
            'is_quantity_sufficient' => $remainingQty <= 0,
            'batches' => $itemBatches
        ];
    }


    public function deductStockFromBatches(Order $order, array $batchesData): void
    {
        DB::transaction(function () use ($order, $batchesData) {
            // معالجة منتجات الطلب العادية
            foreach ($batchesData as $batchData) {
                if (isset($batchData['order_item_id'])) {
                    $this->processOrderItemBatch($order, $batchData);
                }

                if (isset($batchData['order_offer_id'])) {
                    $this->processOfferItemBatch($order, $batchData);
                }
            }
        });
    }

    protected function processOrderItemBatch(Order $order, array $batchData)
    {
        $orderItem = $order->orderItems()->find($batchData['order_item_id']);
        if (!$orderItem) {
            throw new \Exception('عنصر الطلب غير موجود');
        }

        $batch = $this->purchaseReceiptItemRepository->find($batchData['batch_id']);
        if (!$batch) {
            throw new \Exception('الدفعة غير موجودة');
        }

        // التحقق من الكمية المتاحة
        if ($batch->available_quantity < $batchData['quantity']) {
            throw new \Exception('الكمية المطلوبة غير متوفرة في الدفعة المحددة');
        }

        // خصم الكمية
        $batch->decrement('available_quantity', $batchData['quantity']);

        // تسجيل التفاصيل
        OrderBatchDetail::create([
            'order_item_id' => $orderItem->id,
            'purchase_receipt_item_id' => $batch->id,
            'quantity' => $batchData['quantity'],
        ]);
    }

    protected function processOfferItemBatch(Order $order, array $batchData)
    {
        $orderOffer = $order->orderOffer()->find($batchData['order_offer_id']);
        if (!$orderOffer) {
            throw new \Exception('العرض الترويجي غير موجود');
        }

        $offerItem = $orderOffer->offer->items()->find($batchData['order_offer_item_id']);
        if (!$offerItem) {
            throw new \Exception('عنصر العرض الترويجي غير موجود');
        }

        $batch = $this->purchaseReceiptItemRepository->find($batchData['batch_id']);
        if (!$batch) {
            throw new \Exception('الدفعة غير موجودة');
        }

        // التحقق من الكمية المتاحة
        if ($batch->available_quantity < $batchData['quantity']) {
            throw new \Exception('الكمية المطلوبة غير متوفرة في الدفعة المحددة');
        }

        // خصم الكمية
        $batch->decrement('available_quantity', $batchData['quantity']);

        // تسجيل التفاصيل
        OrderOfferItemBatchDetails::create([
            'order_offer_id' => $orderOffer->id,
            'order_offer_Items_id' => $offerItem->id,
            'purchase_receipt_item_id' => $batch->id,
            'quantity' => $batchData['quantity'],
        ]);
    }

}
