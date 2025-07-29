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

    public function addToCart( array $data)
    {
        $user=auth()->user();
        $cart = $user->cart ?? Cart::create(['user_id' => $user->id]);
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

    public function getUserCartItems()
    {
        $user = auth()->user();
        $cart = $user->cart;

        if (!$cart) {
            return CartItemsResource::collection(collect());
        }

        $cartItems = $this->cartItemRepository->getByCartId($cart->id);

        return CartItemsResource::collection($cartItems);
    }

    /**
     * حساب السعر للمنتجات المحددة
     */
    public function calculateSelectedItemsPrice(array $items, int $userId, ?int $pointsUsed = 0): array
    {
        $totalBeforeDiscount = 0;
        $details = [];

        foreach ($items as $item) {
            // جلب العنصر من السلة
            $cartItem = $this->cartItemRepository->getCartItemForUser(
                $item['cart_item_id'],
                $userId
            );

            $requestedQty = $item['quantity'];

            // الكمية المتوفرة بالوحدة الأساسية
            $availableQuantityInBaseUnit = $cartItem->itemUnit->item->Total_Available_Quantity;

            // الوحدة المختارة من المستخدم
            $selectedUnitId = $cartItem->itemUnit->unit->id;

            // الوحدة الأساسية للمنتج
            $baseUnitId = $this->itemRepository->getBaseUnitId($cartItem->itemUnit->item->id);

            // حساب الكمية المطلوبة بوحدة التخزين الأساسية
            $requestedQtyInBaseUnit = ($baseUnitId !== $selectedUnitId)
                ? ($cartItem->itemUnit->conversion_factor * $requestedQty)
                : $requestedQty;

            // تحقق من توفر الكمية
            if ($requestedQtyInBaseUnit > $availableQuantityInBaseUnit) {
                throw new \Exception(
                    "الكمية المطلوبة ({$requestedQtyInBaseUnit}) للمنتج '{$cartItem->itemUnit->item->name}' " .
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
                'product' => $cartItem->itemUnit->item->name,
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

        // إعادة النتيجة
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
