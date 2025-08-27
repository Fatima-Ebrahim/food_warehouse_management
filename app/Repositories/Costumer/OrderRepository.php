<?php
namespace App\Repositories\Costumer;
use App\Models\BatchStorageLocation;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderBatchDetail;
use App\Models\OrderOfferItemBatchDetails;
use App\Models\User;

class OrderRepository{


    public function create(array $data)
    {
        return Order::query()->create($data);
    }

    public function delete($id)
    {
        Order::query()->find($id)->delete();
    }

    public function makeOrderPaid(Order $order){
        $order->update(['status'=>'paid']);
    }
    public function get($id)
    {
        return Order::findOrFail($id);
    }

    public function getWithItems(int $id): Order
    {
        return Order::with(['orderItems.itemUnit.item'])->findOrFail($id);
    }
    public function getOrderWithRelations(Order $order){
        return $order->load('orderOffer','orderItems');
    }

    public function updateQRPath($path , $orderId){
        Order::query()->find($orderId)->update(['qr_code_path'=>$path]);
    }


    public function isPaid(Order $order): bool
    {
        return $order->status === 'paid';
    }

    public function isPartiallyPaid(Order $order): bool
    {
        return $order->status === 'partially_paid';
    }

    public function updateStatus(Order $order ,$status)
    {
     return   $order->update(['status'=>$status]);

    }

    public function getAllPendingOrders(){
        return Order::query()->where('status','=','pending')->get();
    }

    public function getWithInstallments($orderId)
    {
        return Order::with('installments')->findOrFail($orderId);
    }

    public function getOrderOwner(Order $order){
        return $order->cart->user->id;
    }

    public function getUserActiveOrders(User $user){
//            if($user->cart()->doesntExist())
//            {Cart::create($user->id);}
        return $user->cart->orders()->
            whereNotIn('status', ['pending', 'rejected'])->
                with(['orderItems.itemUnit.item','orderOffer.offer.Items'])->get();
    }
    public function getUserPendingOrders(User $user){
        return $user->cart->orders()->where('status','pending')
            ->with(['orderItems.itemUnit.item','orderOffer.offer.Items'])->get();
    }


//    public function getOrderBatches($orderId)
//    {
//        $order = Order::findOrFail($orderId);
//
//        // دفعات المنتجات العادية
//        $regularBatches = OrderBatchDetail::with(['purchaseReceiptItem', 'orderItem.itemUnit'])
//            ->whereHas('orderItem', function($query) use ($orderId) {
//                $query->where('order_id', $orderId);
//            })
//            ->get()
//            ->map(function($detail) {
//                return [
//                    'purchase_receipt_item_id' => $detail->purchase_receipt_item_id,
//                    'item_unit_id' => $detail->orderItem->item_unit_id,
//                    'unit_id'=>$detail->orderItem->itemUnit->Unit->id,
//                    'quantity' => $detail->quantity
//                ];
//            });
//
//        // دفعات العروض
//        $offerBatches = OrderOfferItemBatchDetails::with(['purchaseReceiptItem', 'orderOfferItem.itemUnit'])
//            ->whereHas('orderOffer', function($query) use ($orderId) {
//                $query->where('order_id', $orderId);
//            })
//            ->get()
//            ->map(function($detail) {
//                return [
//                    'purchase_receipt_item_id' => $detail->purchase_receipt_item_id,
//                    'item_unit_id' => $detail->orderOfferItem->item_unit_id,
//                    'unit_id'=>$detail->orderOfferItem->itemUnit->Unit->id,
//                    'quantity' => $detail->quantity
//                ];
//            });
//
//        // دمج الكل بنفس الـ format
//        $allBatches = $regularBatches->merge($offerBatches)->values();
//
//        return response()->json($allBatches);
//    }

    public function getOrderBatches($orderId)
    {
        $order = Order::findOrFail($orderId);

        // دفعات المنتجات العادية
        $regularBatches = OrderBatchDetail::with(['purchaseReceiptItem', 'orderItem.itemUnit.Unit'])
            ->whereHas('orderItem', function($query) use ($orderId) {
                $query->where('order_id', $orderId);
            })
            ->get()
            ->map(function($detail) {
                return [
                    'purchase_receipt_item_id' => $detail->purchase_receipt_item_id,
                    'item_unit_id' => $detail->orderItem->item_unit_id,
                    'unit_id' => optional(optional($detail->orderItem->itemUnit)->Unit)->id,
                    'quantity' => $detail->quantity
                ];
            });

        // دفعات العروض
        $offerBatches = OrderOfferItemBatchDetails::with(['purchaseReceiptItem', 'orderOfferItem.itemUnit.Unit'])
            ->whereHas('orderOffer', function($query) use ($orderId) {
                $query->where('order_id', $orderId);
            })
            ->get()
            ->map(function($detail) {
                return [
                    'purchase_receipt_item_id' => $detail->purchase_receipt_item_id,
                    'item_unit_id' => $detail->orderOfferItem->item_unit_id,
                    'unit_id' => optional(optional($detail->orderOfferItem->itemUnit)->Unit)->id,
                    'quantity' => $detail->quantity
                ];
            });

        // دمج الكل بنفس الـ format
        $allBatches = $regularBatches->merge($offerBatches);

        if ($allBatches->isEmpty()) {
            return response()->json([]);
        }

        // --- يبدأ التعديل هنا ---

        // 1. استخلاص كل معرفات purchase_receipt_item_id الفريدة
        $purchaseReceiptItemIds = $allBatches->pluck('purchase_receipt_item_id')->unique()->filter()->values();

        // 2. جلب معلومات التخزين المرتبطة بها دفعة واحدة مع تحميل العلاقات (رف -> خزانة -> إحداثيات -> منطقة)
        $storageData = BatchStorageLocation::with([
            'shelf.cabinet.coordinates.zone'
        ])
            ->whereIn('purchase_receipt_items_id', $purchaseReceiptItemIds)
            ->get()
            ->groupBy('purchase_receipt_items_id'); // تجميع النتائج حسب معرف الدفعة لتسهيل الوصول

        // 3. إضافة معلومات التخزين إلى كل دفعة منتج
        $result = $allBatches->map(function($batch) use ($storageData) {
            $locations = $storageData->get($batch['purchase_receipt_item_id']);

            // قد يتم تخزين الدفعة في أكثر من مكان، لذلك نعيد مصفوفة
            if ($locations) {
                $batch['storage_info'] = $locations->map(function($location) {
                    $shelf = optional($location->shelf);
                    $cabinet = optional($shelf->cabinet);
                    // الخزانة قد تكون في أكثر من إحداثي، نأخذ الأول كمرجع للمنطقة
                    $coordinate = optional($cabinet->coordinates)->first();
                    $zone = optional(optional($coordinate)->zone);

                    return [
                        'zone' => $zone->name,
                        'cabinet' => $cabinet->code,
                        'shelf' => $shelf->id,
                        'quantity_on_shelf' => $location->quantity,
                    ];
                });
            } else {
                $batch['storage_info'] = []; // إذا لم يتم تخزين المنتج بعد
            }
            return $batch;
        });

        // --- ينتهي التعديل هنا ---

        return response()->json($result->values());
    }

}
