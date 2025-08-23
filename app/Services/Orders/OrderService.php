<?php
namespace App\Services\Orders;


use App\Http\Resources\OrderDetailsResource;
use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderStatusUpdateNotification;
use App\Repositories\Costumer\CartOfferRepository;
use App\Repositories\CustomerRepository;
use App\Repositories\ItemRepository;
use App\Repositories\OrderOfferRepository;
use App\Repositories\SettingsRepository;
use App\Repositories\Costumer\CartItemRepository;
use App\Repositories\Costumer\OrderItemRepository;
use App\Repositories\Costumer\OrderRepository;
use App\Repositories\Costumer\PointTransactionRepository;
use App\Repositories\SpecialOfferRepository;
use Illuminate\Support\Facades\DB;

class OrderService{


    public function __construct(
       protected OrderRepository $orderRepository,
       protected OrderItemRepository $orderItemRepository,
       protected CartItemRepository $cartItemRepository,
       protected  CustomerRepository $customerRepository,
       protected  SettingsRepository $settingsRepository,
       protected ItemRepository $itemRepository ,
       protected PointTransactionRepository $pointTransactionRepository ,
        protected OrderOfferRepository $orderOfferRepository ,
        protected SpecialOfferRepository $offerRepository,
        protected CartOfferRepository $cartOfferRepository,

    ) {}
    public function confirmOrder(int $userId, string $paymentType, ?array $items,?array $offers, ?int $pointsUsed = 0)
    {
        return DB::transaction(function () use ($userId, $paymentType, $items,$offers, $pointsUsed) {
            $cart = User::findOrFail($userId)->cart;

            $availablePoints = $this->customerRepository->getPoints($userId);
            if ($pointsUsed > $availablePoints) {
                throw new \Exception("you do not have enough points , the number exist {$availablePoints}");
            }
            // حساب السعر
            $priceData = app(CartItemService::class)->calculateSelectedItemsPrice($offers??[], $items??[], $userId, $pointsUsed);

            $status = 'confirmed';
            if ($paymentType === 'installment') {
                $status = app(InstallmentService::class)
                    ->validateInstallment($userId, $priceData['total_after_discount']);
            }


            // إنشاء الطلب
            $order = $this->orderRepository->create([
                'cart_id' => $cart->id,
                'payment_type' => $paymentType,
                'status'=>$status,
                'total_price' => $priceData['total_before_discount'],
                'final_price' => $priceData['total_after_discount'],
                'used_points' => $pointsUsed ?? 0,
               ]);

            if($priceData['data']['items'] )
            {
                $this->addOrderItems($priceData['data']['items'], $userId, $order->id);
            }
            if($priceData['data']['offers']){

                $this->addOrderOffers($priceData['data']['offers'] ,$order->id);
            }

            // تسجيل حركة النقاط
            if ($pointsUsed > 0)
            {
                $this->pointsMovement($userId ,$pointsUsed ,$order->id);
            }
            if($status==="confirmed"){
                app(QrService::class)->generateQr($order ,$userId);
            }
            return $this->orderRepository->getOrderWithRelations($order);
        });

    }

    public function addOrderItems(array $items ,$userId ,$orderId){
        foreach ($items as $itemData) {
            $cartItem = $this->cartItemRepository->getCartItemForUser($itemData['cart_item_id'], $userId);
            $itemUnit = $cartItem->itemUnit;
            $product = $itemUnit->item;


            $qtyInBase = $this->calculateQuantityInBaseUnit(
                $product->id,
                $itemUnit->unit->id,
                $itemUnit->conversion_factor,
                $itemData['requested_quantity']
            );

            // خصم الكمية من المخزون
            $product->decrement('Total_Available_Quantity', $qtyInBase);

            $this->orderItemRepository->create([
                'order_id' => $orderId,
                'item_unit_id' => $itemUnit->id,
                'quantity' => $itemData['requested_quantity'],
                'price' => $itemUnit->selling_price * $itemData['requested_quantity']
            ]);
            $cartItem->delete();
        }
    }

    public function addOrderOffers(array $offers ,$orderId){

        foreach ($offers as $offer) {
            $cartOffer=$this->cartOfferRepository->getCartOfferById($offer['cart_offer_id']);
            $offerItems=$this->offerRepository->getOfferItemsitemDetalis($cartOffer->offer);
            $requestedQty=$offer['offer']['requested_quantity'];

            foreach ($offerItems as $offerItem){

                $itemUnit = $offerItem->itemUnit;
                $product = $itemUnit->item;
                $requiredQty =$offerItem->required_quantity;
                $neededQty=$requestedQty*$requiredQty;

                $qtyInBase = $this->calculateQuantityInBaseUnit(
                    $product->id,
                    $itemUnit->unit->id,
                    $itemUnit->conversion_factor,
                    $neededQty
                );
                $product->decrement('Total_Available_Quantity', $qtyInBase);

            }

            $this->orderOfferRepository->create([
                'order_id'=>$orderId ,
                'offer_id'=>$cartOffer->offer->id ,
                'quantity'=>$requestedQty ,
                'price' => $offer['offer']['finalPrice']
            ]);

            // حذف من السلة
            $cartOffer->delete();
        }
    }


    public function pointsMovement($userId ,$pointsUsed , $orderId){
        $customer = $this->customerRepository->getByUserId($userId);
        $this->customerRepository->subtractPoints($userId, $pointsUsed);

        $this->pointTransactionRepository->create([
            'customer_id' => $customer->id,
            'type' => 'subtract',
            'points' => $pointsUsed,
            'order_id' => $orderId,
            'reason' => 'خصم نقاط عند تأكيد الطلب',
        ]);
    }

    public function delete($id){
        $this->orderRepository->delete($id);
    }

    public function calculateQuantityInBaseUnit(int $itemId, int $selectedUnitId, float $conversionFactor, float $requestedQty): float
    {
        $baseUnitId = $this->itemRepository->getBaseUnitId($itemId);
        return ($baseUnitId !== $selectedUnitId)
            ? $requestedQty * $conversionFactor
            : $requestedQty;
    }

    public function getOrderDetails($orderId)
    {
        $order =$this->orderRepository->get($orderId);
        return new OrderDetailsResource($this->orderRepository->getOrderWithRelations($order)) ;
    }

    public function getOrderQrPath($orderId)
    {
      $order= $this->orderRepository->get($orderId);
      return $order->qr_code_path;
    }

    public function getPendingOrders()
    {
        return $this->orderRepository->getAllPendingOrders();
    }

    public function updateOrderStatus($order_id, $status)
    {
        $order = $this->orderRepository->getWithItems($order_id);
        $user = $this->orderRepository->getOrderOwner($order);

        if ($status === 'rejected') {
            DB::transaction(function () use ($order) {
                foreach ($order->orderItems as $orderItem) {
                    if (!$orderItem->itemUnit || !$orderItem->itemUnit->item) {
                        throw new \Exception('بيانات العنصر غير مكتملة');
                    }
                    $item = $orderItem->itemUnit->item;
                    $item->increment('Total_Available_Quantity', $orderItem->quantity);
                }
            });
        }

        $this->orderRepository->updateStatus($order, $status);

        if ($user) {
            $user->notify(new OrderStatusUpdateNotification($order, $status));
        }

        return $order;
    }

    public function deliverOrder(array $data): array
    {
        $order = $this->orderRepository->get($data['order_id']);
        $order = $this->orderRepository->getOrderWithRelations($order);

        if (!$order) {
            throw new \Exception('QR code غير صالح.');
        }

        if ($this->orderRepository->isPaid($order) || $this->orderRepository->isPartiallyPaid($order)) {
            throw new \Exception("تم تأكيد استلام هذا الطلب مسبقاً.");
        }

        DB::transaction(function () use ($data, $order) {
            app(FifoStockDeductionService::class)->deductStockFromBatches($order, $data['batchesData']);
            $order->update([
                'status' => $order->payment_type === 'cash' ? 'paid' : 'partially_paid'
            ]);
            if($order->payment_type==="installment"){
                app(InstallmentService::class)->createInitialInstallment($order, $data['paidAmount']);
            }

        });

        return [
            'order_id' => $order->id,
            'status' => $order->status,
            'message' => 'تم تأكيد استلام الطلب بنجاح.',
        ];
    }




    public function getUserPendingOrders(User $user){
        return $this->orderRepository->getUserPendingOrders($user);
    }


    public function getUserActiveOrders(User $user){
        return $this->orderRepository->getUserActiveOrders($user);
    }

    public function getOrderBatches($orderId){
        return $this->orderRepository->getOrderBatches($orderId);
    }



}
