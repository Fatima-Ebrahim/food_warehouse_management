<?php
namespace App\Services\Orders;

use App\Http\Resources\Items\CartItemsResource;
use App\Http\Resources\ShowSpecialOfferResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\SpecialOffer;
use App\Repositories\Costumer\CartOfferRepository;
use App\Repositories\CustomerRepository;
use App\Repositories\ItemRepository;
use App\Repositories\SettingsRepository;
use App\Repositories\Costumer\CartItemRepository;
use App\Repositories\SpecialOfferRepository;

class CartItemService{



    public function __construct( protected CustomerRepository $customerRepository,
                                protected CartItemRepository $cartItemRepository,
                               protected  SettingsRepository $settingsRepository,
                                protected ItemRepository     $itemRepository,
                              protected CartOfferRepository $cartOfferRepository,
    protected SpecialOfferRepository $specialOfferRepository ,
    )
    {}

    public function addToCart( array $data ,$user)
    {
        $cart = $user->cart ?? Cart::create(['user_id' => $user->id]);

       if (isset($data['item_unit_id'])){
           if($this->cartItemRepository->checkIfItemExistsOnCart($data['item_unit_id'] , $cart))
           {
               return "this item already exist on cart with this unit" ;
           }

           return $this->addItem([
                'cart_id' => $cart->id,
                'item_unit_id' => $data['item_unit_id'],
                'quantity' => $data['quantity'],
            ]);
        }
        else{
           return $this->addOffer([
                'cart_id' => $cart->id,
                'offer_id' => $data['offer_id'],
                'quantity' => $data['quantity'],
            ]);
        }
    }

    public function addOffer(array $data){
        return $this->cartOfferRepository->create($data);
    }

    public function addItem(array $data){
        return $this->cartItemRepository->create($data);
    }

    public function updateQuantity( array $data,$type ,$id)
    {
        if($type==="item"){
            return $this->cartItemRepository->update($id, ['quantity' => $data['quantity']]);
        }
        elseif ($type==="offer"){
            return $this->cartOfferRepository->update($id, ['quantity' => $data['quantity']]);
        }
        else {
            throw new \Exception("Invalid type ");
        }

    }

    public function removeFromCart($type ,$id)
    {
        if($type==="item"){
            return $this->cartItemRepository->delete($id);
        }
        elseif ($type==="offer"){
            return $this->cartOfferRepository->delete($id);
        }
        else {
            throw new \Exception("Invalid type ");
        }
    }

    //added offer
    public function getUserCartItems($user)
    {

        $cart = $user->cart;
        if (!$cart) {
            return ['items'=>null , 'offers'=>null];
        }

        $cartItems = $this->cartItemRepository->getByCartId($cart->id);
        $data['items']= CartItemsResource::collection($cartItems);
        $offers = $this->cartOfferRepository->getOfferByCartId($cart->id);
        $data['offers'] = $offers->map(function ($offer) {
            return [
                'id' => $offer->id,
                'cart_id' => $offer->cart_id,
                'offer_id' => $offer->offer_id,
                'quantity' => $offer->quantity,
                'offer' => new ShowSpecialOfferResource($offer->offer)
            ];
        });
        return $data   ;
    }


    public function calculateSelectedItemsPrice(?array $offers,?array $items,
                                                int $userId, ?int $pointsUsed = 0): array
    {

        gc_enable();
        $totalBeforeDiscount = 0;
        $itemsDetails = [];
        $offersDetails = [];

        if (!empty($items)) {
            // معالجة العناصر العادية
            foreach ($items as $item) {
                $result = $this->processSingleItem($item, $userId);
                $totalBeforeDiscount += $result['line_total'];
                $itemsDetails[] = $result;
                unset($result);
            }
        }
        if (!empty($offers)) {
            // معالجة العروض المختارة
            foreach ($offers as $offer) {
                $result = $this->processSingleOffer($offer);
                $totalBeforeDiscount += $result['line_total'];
                $offersDetails[] = $result;
                unset($result);
            }
        }
        gc_collect_cycles();

            return $this->applyPointsDiscount(
            $totalBeforeDiscount,
            $userId,
            $pointsUsed,
            $itemsDetails ,
            $offersDetails
        );
    }

    private function processSingleItem(array $item, int $userId): array
    {
        $cartItem = $this->cartItemRepository->getCartItemForUserWithRelations(
            $item['cart_item_id'],
            $userId,
            ['itemUnit.item', 'itemUnit.unit']
        );

        $requestedQty = $item['quantity'];
        $itemModel = $cartItem->itemUnit->item;

        $requestedQtyInBaseUnit = app(OrderService::class)->calculateQuantityInBaseUnit(
            $itemModel->id,
            $cartItem->itemUnit->unit->id,
            $cartItem->itemUnit->conversion_factor,
            $requestedQty
        );

        if ($requestedQtyInBaseUnit > $itemModel->Total_Available_Quantity) {
            throw new \Exception("الكمية المطلوبة غير متاحة للمنتج {$itemModel->name}");
        }

        return [
            'cart_item_id' => $cartItem->id,
            'product' => $itemModel->name,
            'unit' => $cartItem->itemUnit->unit->name,
            'unit_price' => $cartItem->itemUnit->selling_price,
            'requested_quantity' => $requestedQty,
            'available_quantity' => $itemModel->Total_Available_Quantity,
            'line_total' => $requestedQty * $cartItem->itemUnit->selling_price,
        ];
    }

    private function applyPointsDiscount(float $total, int $userId, int $pointsUsed,
                                         array $itemsDetails ,array $offersDetails): array
    {
        $availablePoints = $this->customerRepository->getPoints($userId);

        if ($pointsUsed > $availablePoints) {
            throw new \Exception("نقاط غير كافية. المتاح: {$availablePoints}");
        }

        $moneyPerPoint = $this->settingsRepository->getPointsSettings()['sy_lira_per_point'];
        $discountAmount = $pointsUsed * $moneyPerPoint;

        return [
            'total_before_discount' => $total,
            'points_used' => $pointsUsed,
            'discount_amount' => $discountAmount,
            'total_after_discount' => max($total - $discountAmount, 0),
            'available_points' => $availablePoints,
            'data' => ['items'=>$itemsDetails ,
                        'offers'=>$offersDetails],
        ];
    }


    private function processSingleOffer(array $offer): array
    {
        $cartOffer = $this->cartOfferRepository->getCartOfferById($offer['cart_offer_id']);
        $offerModel = $cartOffer->offer;

        // جلب عناصر العرض بتفاصيل الوحدات (تأكد أن هذه الدالة ترجع relations itemUnit.unit و itemUnit.item)
        $offerItems = $this->specialOfferRepository->getOfferItemsitemDetalis($offerModel);

        $requestedQty = $offer['quantity'];
        $offerItemsArr = [];
        $originalPrice = 0.0;

        foreach ($offerItems as $offerItem) {
            $requiredQty = $offerItem->required_quantity;
            $neededQty = $requestedQty * $requiredQty;

            $itemUnitModel = $offerItem->itemUnit;
            $itemModel = $itemUnitModel->item;

            $requestedQtyInBaseUnit = app(OrderService::class)->calculateQuantityInBaseUnit(
                $offerItem->item_id,
                $itemUnitModel->unit->id,
                $itemUnitModel->conversion_factor,
                $neededQty
            );

            if ($requestedQtyInBaseUnit > $itemModel->Total_Available_Quantity) {
                throw new \Exception("الكمية المطلوبة غير متاحة للعرض '{$offerModel->id}' للمنتج '{$itemModel->name}', المطلوب {$neededQty}، المتوفر {$itemModel->Total_Available_Quantity}.");
            }

            $originalPrice += ((float)$itemUnitModel->selling_price) * $neededQty;

            $offerItemsArr[] = [
                'item_price'=>$itemUnitModel->selling_price * $neededQty,
                'original_item_id' => $offerItem->item_id,
                'item_name' => $itemModel->name,
                'quantity' => $neededQty,
                'unit' => $itemUnitModel->unit->name,
                'unit_price' => (float)$itemUnitModel->selling_price,
                'available_quantity' => $itemModel->Total_Available_Quantity,
            ];
        }


        $finalPrice = $this->calcPriceForOffer($offerModel, $originalPrice, $requestedQty);

        return [
            'cart_offer_id' => $cartOffer->id,
            'line_total' => (float)$finalPrice,
            'savings' => (float)($originalPrice - $finalPrice),
            'offer' => [
                'requested_quantity' => $requestedQty,
                'originalPrice ' => $originalPrice,
                'finalPrice'=>$finalPrice,
                'id' => $offerModel->id,
                'discount_type' => $offerModel->discount_type,
                'discount_value' => (float)$offerModel->discount_value,
                'description' => $offerModel->description,
                'starts_at' => $offerModel->starts_at,
                'ends_at' => $offerModel->ends_at,
                'offer_items' => $offerItemsArr,
            ],


        ];
    }

    public function calcPriceForOffer(SpecialOffer $offer ,$originalPrice ,$requestedQty){
        if($offer->discount_type==="fixed_price"){
            return $offer->discount_value * $requestedQty;

        }
        else{
            $percentage =$offer->discount_value *0.01;
            $discount_value= $percentage * $originalPrice ;
           return ($originalPrice -$discount_value) ;
        }
    }

}
