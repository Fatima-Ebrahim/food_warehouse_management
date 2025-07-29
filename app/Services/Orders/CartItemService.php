<?php
namespace App\Services\Orders;

use App\Http\Resources\Items\CartItemsResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Repositories\CustomerRepository;
use App\Repositories\ItemRepository;
use App\Repositories\SettingsRepository;
use App\Repositories\Orders\CartItemRepository;

class CartItemService{

    protected CustomerRepository $customerRepository;
    protected CartItemRepository $cartItemRepository;
    protected ItemRepository     $itemRepository;
    protected SettingsRepository $settingsRepository;

    public function __construct(CustomerRepository $customerRepository,
                                CartItemRepository $cartItemRepository,
                                SettingsRepository $settingsRepository,
                                ItemRepository     $itemRepository)
    {
        $this->cartItemRepository = $cartItemRepository;
        $this->customerRepository = $customerRepository;
        $this->settingsRepository = $settingsRepository;
        $this->itemRepository =     $itemRepository;
    }

    public function addToCart( array $data ,$user)
    {

        $cart = $user->cart ?? Cart::create(['user_id' => $user->id]);

        if($this->cartItemRepository->checkIfItemExistsOnCart($data['item_unit_id'] , $cart))
        {
            return "this item already exist on cart with this unit" ;
        }
        return $this->cartItemRepository->create([
            'cart_id' => $cart->id,
            'item_unit_id' => $data['item_unit_id'],
            'quantity' => $data['quantity'],
        ]);
    }

    public function updateQuantity(CartItem $cartItem, array $data)
    {
        return $this->cartItemRepository->update($cartItem, ['quantity' => $data['quantity']]);
    }

    public function removeFromCart(CartItem $cartItem)
    {
        return $this->cartItemRepository->delete($cartItem);
    }

    public function getUserCartItems($user)
    {

        $cart = $user->cart;
        if (!$cart) {
            return CartItemsResource::collection(collect());
        }

        $cartItems = $this->cartItemRepository->getByCartId($cart->id);
        return CartItemsResource::collection($cartItems);
    }

    public function calculateSelectedItemsPrice(array $items, int $userId, ?int $pointsUsed = 0): array
    {
        $totalBeforeDiscount = 0;
        $details = [];

        foreach ($items as $item) {
            $cartItem = $this->cartItemRepository->getCartItemForUser(
                $item['cart_item_id'],
                $userId
            );

            $requestedQty = $item['quantity'];

            $itemModel = $cartItem->itemUnit->item;
            $availableQuantityInBaseUnit = $itemModel->Total_Available_Quantity;
            $selectedUnitId = $cartItem->itemUnit->unit->id;
            $conversionFactor = $cartItem->itemUnit->conversion_factor;

            //حساب الكمية بالواحدة الاساسية
            $requestedQtyInBaseUnit = app(OrderService::class)->calculateQuantityInBaseUnit(
                $itemModel->id,
                $selectedUnitId,
                $conversionFactor,
                $requestedQty
            );

            // تحقق من توفر الكمية
            if ($requestedQtyInBaseUnit > $availableQuantityInBaseUnit) {
                throw new \Exception(
                    "الكمية المطلوبة ({$requestedQtyInBaseUnit}) للمنتج '{$itemModel->name}' " .
                    "أكبر من الكمية المتاحة ({$availableQuantityInBaseUnit})."
                );
            }

            // حساب السعر الإجمالي للعنصر
            $unitPrice = $cartItem->itemUnit->selling_price;
            $lineTotal = $requestedQty * $unitPrice;

            $totalBeforeDiscount += $lineTotal;

            // إضافة تفاصيل العنصر للنتيجة
            $details[] = [
                'cart_item_id' => $cartItem->id,
                'product' => $itemModel->name,
                'unit' => $cartItem->itemUnit->unit->name,
                'unit_price' => $unitPrice,
                'requested_quantity' => $requestedQty,
                'available_quantity' => $availableQuantityInBaseUnit,
                'line_total' => $lineTotal,
            ];
        }

        // جلب نقاط المستخدم
        $availablePoints = $this->customerRepository->getPoints($userId);

        // تحقق من رصيد النقاط
        if ($pointsUsed > $availablePoints) {
            throw new \Exception("ليس لديك عدد كافٍ من النقاط. المتاح: {$availablePoints}");
        }

        // جلب إعدادات قيمة النقاط
        $pointsSetting = $this->settingsRepository->getPointsSettings();
        $moneyPerPoint = $pointsSetting['sy_lira_per_point'];

        // حساب الخصم وإجمالي السعر بعد الخصم
        $discountAmount = $pointsUsed * $moneyPerPoint;
        $totalAfterDiscount = max($totalBeforeDiscount - $discountAmount, 0);

        return [
            'total_before_discount' => $totalBeforeDiscount,
            'points_used' => $pointsUsed,
            'discount_amount' => $discountAmount,
            'total_after_discount' => $totalAfterDiscount,
            'available_points' => $availablePoints,
            'items' => $details,
        ];
    }


}
