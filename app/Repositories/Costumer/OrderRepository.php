<?php
namespace App\Repositories\Costumer;
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


    public function getOrderBatches($orderId)
    {
        $order = Order::findOrFail($orderId);

        // دفعات المنتجات العادية
        $regularBatches = OrderBatchDetail::with(['purchaseReceiptItem', 'orderItem.itemUnit'])
            ->whereHas('orderItem', function($query) use ($orderId) {
                $query->where('order_id', $orderId);
            })
            ->get()
            ->map(function($detail) {
                return [
                    'purchase_receipt_item_id' => $detail->purchase_receipt_item_id,
                    'item_unit_id' => $detail->orderItem->item_unit_id,
                    'unit_id'=>$detail->orderItem->itemUnit->Unit->id,
                    'quantity' => $detail->quantity
                ];
            });

        // دفعات العروض
        $offerBatches = OrderOfferItemBatchDetails::with(['purchaseReceiptItem', 'orderOfferItem.itemUnit'])
            ->whereHas('orderOffer', function($query) use ($orderId) {
                $query->where('order_id', $orderId);
            })
            ->get()
            ->map(function($detail) {
                return [
                    'purchase_receipt_item_id' => $detail->purchase_receipt_item_id,
                    'item_unit_id' => $detail->orderOfferItem->item_unit_id,
                    'unit_id'=>$detail->orderOfferItem->itemUnit->Unit->id,
                    'quantity' => $detail->quantity
                ];
            });

        // دمج الكل بنفس الـ format
        $allBatches = $regularBatches->merge($offerBatches)->values();

        return response()->json($allBatches);
    }

}
