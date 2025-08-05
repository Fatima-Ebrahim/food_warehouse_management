<?php
namespace App\Services\Orders;


use App\Models\Order;
use App\Models\User;
use App\Repositories\CustomerRepository;
use App\Repositories\ItemRepository;
use App\Repositories\SettingsRepository;
use App\Repositories\Costumer\CartItemRepository;
use App\Repositories\Costumer\OrderItemRepository;
use App\Repositories\Costumer\OrderRepository;
use App\Repositories\Costumer\PointTransactionRepository;
use Illuminate\Support\Facades\DB;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;

class OrderService{

    protected OrderRepository $orderRepository;
       protected OrderItemRepository $orderItemRepository;
       protected CartItemRepository $cartItemRepository;
       protected  CustomerRepository $customerRepository;
       protected  SettingsRepository $settingsRepository;
       protected ItemRepository $itemRepository ;
       protected PointTransactionRepository $pointTransactionRepository ;
       protected  InstallmentService $installmentService;
//        protected FifoStockDeductionService $fifoService;
    public function __construct(
        OrderRepository $orderRepository,
        OrderItemRepository $orderItemRepository,
        CartItemRepository $cartItemRepository,
         CustomerRepository $customerRepository,
         SettingsRepository $settingsRepository,
        ItemRepository $itemRepository ,
        PointTransactionRepository $pointTransactionRepository ,
//         CartItemService $cartItemService ,
//         InstallmentService $installmentService,
//         FifoStockDeductionService $fifoService
    ) {
        $this->itemRepository=$itemRepository;
        $this->cartItemRepository=$cartItemRepository;
        $this->customerRepository=$customerRepository;
        $this->settingsRepository=$settingsRepository;
        $this->orderRepository=$orderRepository;
        $this->orderItemRepository=$orderItemRepository;
        $this->pointTransactionRepository=$pointTransactionRepository;
//        $this->cartItemService=$cartItemService;
//        $this->installmentService=$installmentService;
//        $this->fifoService=$fifoService;



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
            $priceData = app(CartItemService::class)->calculateSelectedItemsPrice($items, $userId, $pointsUsed);

            $status = 'confirmed';
            if ($paymentType === 'installment') {
                $status = $this->installmentService
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


            if($status==="confirmed"){
                $this->generateQr($order ,$userId);
            }
             foreach ($priceData['items'] as $itemData) {
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

    public function calculateQuantityInBaseUnit(int $itemId, int $selectedUnitId, float $conversionFactor, float $requestedQty): float
    {
        $baseUnitId = $this->itemRepository->getBaseUnitId($itemId);
        return ($baseUnitId !== $selectedUnitId)
            ? $requestedQty * $conversionFactor
            : $requestedQty;
    }

    public function generateQr(Order $order, $userId)
    {

        $qrData = json_encode([
            'order_id' => $order->id,
            'user_id' => $userId,
            'timestamp' => now()->timestamp,
        ]);

        // حماية من حجم البيانات الكبير
        if (strlen($qrData) > 1024) {
            throw new \Exception('QR data is too large');
        }

        $qrCode = new QrCode($qrData);
        $qrCode->setSize(300);

        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        $path = "qrcodes/order_{$order->id}.png";
        Storage::disk('public')->put($path, $result->getString());

        $order->update(['qr_code_path' => $path]);
    }

    public function getOrderDetails($orderId)
    {
        return $this->orderRepository->get($orderId);
    }

    public function getOrderQrPath($orderId)
    {
      $order= $this->orderRepository->get($orderId);
      return $order->qr_code_path;
    }

    public function processQr(string $qrData): array
    {
        $decoded = json_decode($qrData, true);

        if (!$decoded || !isset($decoded['order_id'], $decoded['user_id'])) {
            throw new \Exception('QR code غير صالح.');
        }

        $order = $this->orderRepository->getWithItems($decoded['order_id']);

        if ($this->orderRepository->isPaid($order)||$this->orderRepository->isPartiallyPaid($order) ) {
            throw new \Exception("تم تأكيد استلام هذا الطلب مسبقاً.");
        }

        DB::transaction(function () use ($order) {
            app(FifoStockDeductionService::class)->deductStockFromBatches($order);
            $order->update([
                'status' => $order->payment_type === 'cash' ? 'paid' : 'partially_paid'
            ]);
        });

        return [
            'order_id' => $order->id,
            'status' => $order->status,
            'message' => 'تم تأكيد استلام الطلب بنجاح.',
        ];
    }


}
