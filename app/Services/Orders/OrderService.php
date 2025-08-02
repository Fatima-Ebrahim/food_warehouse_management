<?php
namespace App\Services\Orders;


use App\Models\Cart;
use App\Models\PointTransaction;
use App\Models\User;
use App\Repositories\CustomerRepository;
use App\Repositories\ItemRepository;
use App\Repositories\ItemUnitRepository;
use App\Repositories\SettingsRepository;
use App\Repositories\Orders\CartItemRepository;
use App\Repositories\Orders\CartRepository;
use App\Repositories\Orders\OrderItemRepository;
use App\Repositories\Orders\OrderRepository;
use App\Repositories\Orders\PointTransactionRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OrderService{

    protected OrderRepository $orderRepository;
    protected OrderItemRepository $orderItemRepository;
    protected CartItemRepository $cartItemRepository;
    protected CustomerRepository $customerRepository;
    protected SettingsRepository $settingsRepository;
    protected ItemRepository $itemRepository;
    protected PointTransactionRepository $pointTransactionRepository ;

    public function __construct(
        OrderRepository $orderRepository,
        OrderItemRepository $orderItemRepository,
        CartItemRepository $cartItemRepository,
        CustomerRepository $customerRepository,
        SettingsRepository $settingsRepository,
        ItemRepository $itemRepository ,
        PointTransactionRepository $pointTransactionRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->cartItemRepository = $cartItemRepository;
        $this->customerRepository = $customerRepository;
        $this->settingsRepository = $settingsRepository;
        $this->itemRepository = $itemRepository;
        $this->pointTransactionRepository =$pointTransactionRepository;
    }

    public function confirmOrder(int $userId, string $paymentType, array $items, ?int $pointsUsed = 0)
    {
        return DB::transaction(function () use ($userId, $paymentType, $items, $pointsUsed) {
            $cart = User::findOrFail($userId)->cart;


            $availablePoints = $this->customerRepository->getPoints($userId);
            if ($pointsUsed > $availablePoints) {
                throw new \Exception("you do not have enough points , the number exist {$availablePoints}");
            }

            // حساب السعر
            $priceData = app(CartItemService::class)
                ->calculateSelectedItemsPrice($items, $userId, $pointsUsed);

            // إنشاء الطلب
            $order = $this->orderRepository->create([
                'cart_id' => $cart->id,
                'payment_type' => $paymentType,
                'total_price' => $priceData['total_before_discount'],
                'final_price' => $priceData['total_after_discount'],
                'used_points' => $pointsUsed ?? 0,
            ]);

            foreach ($priceData['items'] as $itemData) {
                $cartItem = $this->cartItemRepository->getCartItemForUser($itemData['cart_item_id'], $userId);
                $itemUnit = $cartItem->itemUnit;
                $product = $itemUnit->item;

                // حساب الكمية بالوحدة الأساسية
                $qtyInBase = $this->calculateQuantityInBaseUnit(
                    $product->id,
                    $itemUnit->unit->id,
                    $itemUnit->conversion_factor,
                    $itemData['requested_quantity']
                );

                // خصم الكمية من المخزون
                $product->decrement('Total_Available_Quantity', $qtyInBase);

                // إنشاء العنصر في الطلب
                $this->orderItemRepository->create([
                    'order_id' => $order->id,
                    'item_unit_id' => $itemUnit->id,
                    'quantity' => $itemData['requested_quantity'],
                    'price' => $itemUnit->selling_price,
                ]);

                // حذف من السلة
                $cartItem->delete();
            }

            // تسجيل حركة النقاط
            if ($pointsUsed > 0) {
                $customer = $this->customerRepository->getByUserId($userId);
                $this->customerRepository->subtractPoints($userId, $pointsUsed);

                $this->pointTransactionRepository->create([
                    'customer_id' => $customer->id,
                    'type' => 'subtract',
                    'points' => $pointsUsed,
                    'order_id' => $order->id,
                    'reason' => 'خصم نقاط عند تأكيد الطلب',
                ]);
            }
            return $order;
        });

    }

    public function delete($id){
        $this->orderRepository->delete($id);
    }

    public function updatePayementStatus(array $data){

    }

    public function calculateQuantityInBaseUnit(int $itemId, int $selectedUnitId, float $conversionFactor, float $requestedQty): float
    {
        $baseUnitId = $this->itemRepository->getBaseUnitId($itemId);
        return ($baseUnitId !== $selectedUnitId)
            ? $requestedQty * $conversionFactor
            : $requestedQty;
    }
}
