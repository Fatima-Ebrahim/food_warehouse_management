<?php
namespace App\Services\Orders;

use App\Models\Item;
use App\Models\ItemUnit;
use App\Models\Order;
use App\Models\User;
use App\Repositories\Costumer\CartRepository;
use App\Repositories\Costumer\InstallmentRepository;
use App\Repositories\Costumer\OrderRepository;
use App\Repositories\CustomerRepository;
use App\Repositories\UserRepository;
use App\Settings\InstallmentSettings;
use function PHPUnit\Framework\throwException;

class InstallmentService{


    protected CustomerRepository $customerRepository;
    protected InstallmentSettings $settings;

    public function __construct(CustomerRepository $customerRepository,
                                InstallmentSettings $settings,
                        protected InstallmentRepository $installmentRepository,
                        protected OrderRepository $orderRepository, )
    {
        $this->customerRepository = $customerRepository;
        $this->settings = $settings;
    }

    /**
     * يتحقق من صلاحية الطلب بالتقسيط ويُرجع حالة الطلب
     * - confirmed: في حال الشروط محققة.
     * - pending: في حال لم تتحقق الشروط ولكن الإعداد لا يفرض الرفض.
     * - Exception: في حال لم تتحقق الشروط والإعداد يفرض الرفض.
     */
    public function validateInstallment(int $userId, float $totalPrice): string
    {
        if (! $this->settings->enable_installments) {
            throw new \Exception('نظام التقسيط غير مفعل حالياً.');
        }

        // حالة الطلب الافتراضية
        $status = 'confirmed';

        // 1. تحقق من الحد الأقصى للمبلغ المسموح
        if ($this->settings->enforce_amount_limit && $totalPrice > $this->settings->max_installment_amount) {
            if ($this->settings->reject_if_insufficient_amount) {
                throw new \Exception("المبلغ يتجاوز الحد الأعلى المسموح للتقسيط.");
            } else {
                $status = 'pending';
            }
        }


        return $status;
    }


    public function createInitialInstallment(Order $order,  $paidAmount): void
    {
        $settings = app(InstallmentSettings::class);

        $firstPaymentPercentage = $settings->first_payment_percentage;

        $finalPrice = $order->final_price;

        $expectedFirstPayment = $finalPrice * $firstPaymentPercentage;

        if ($paidAmount < $expectedFirstPayment) {
            throw new \Exception("الدفعة الأولى أقل من النسبة المطلوبة: ".$expectedFirstPayment );
        }

        $remaining = $finalPrice - $paidAmount;
        if ($remaining<0) {
            throw new \Exception("الدفع اكتر من المبلغ المطلوب وهو : " .$finalPrice);
        }

        // 🧾 أول دفعة
        $this->installmentRepository->create([
            'order_id' => $order->id,
            'paid_amount' => $paidAmount,
            'remaining_amount' => $remaining,
            'due_date' => now(),
            'paid_at' => now(),
            'note' => 'الدفعة الأولى',
            'status' => 'paid',
        ]);

        // 📌 الدفعة القادمة (إن وُجد مبلغ متبقٍ)
        if ($remaining > 0) {
            $this->createNextInstallment($order->id, $remaining);
        }
        else{
            $this->orderRepository->makeOrderPaid($order);
        }
    }

    public function createNextInstallment(int $orderId, float $remaining): void
    {
        $nextDueDate = now()->addDays($this->settings->payment_interval_days);

        $this->installmentRepository->create([
            'order_id' => $orderId,
            'paid_amount' => 0,
            'remaining_amount' => $remaining,
            'due_date' => $nextDueDate,
            'status' => 'pending',
            'note' => 'دفعة جديدة مجدولة',
        ]);
    }

    public function payNextInstallment(Order $order,  $paidAmount) :float
    {
        $settings = app(InstallmentSettings::class);
        $intervalDays = $settings->payment_interval_days;

        $miniPaymentPercentage = $settings->minimum_payment_percentage;

        $finalPrice = $order->final_price;

        $expectedFirstPayment = $finalPrice * $miniPaymentPercentage;
        $installment = $this->installmentRepository->getOrderInstallments($order->id)
            ->where('status', 'pending')
            ->orderBy('due_date')
            ->first();
        $remaining = $installment->remaining_amount;
        if (!$installment) {
            throw new \Exception("لا توجد دفعات مستحقة حاليًا.");
        }
        if ($paidAmount < $expectedFirstPayment &&
            $remaining > $expectedFirstPayment) {
            throw new \Exception("الدفعة الأولى أقل من النسبة المطلوبة: ".$expectedFirstPayment );
        }

        if ($paidAmount > $remaining) {
            throw new \Exception("المبلغ المدفوع أكبر من المبلغ المتبقي.");
        }

        $isLate = now()->greaterThan($installment->due_date);
        $newStatus =  'paid' ;
        $newRemaining = $remaining - $paidAmount;

        if ($isLate) {
            $newStatus = 'late';
        }

        $installment->update([
            'paid_amount' => $paidAmount,
            'remaining_amount' => $newRemaining,
            'paid_at' => now(),
            'status' => $newStatus,
            'note' => $isLate ? 'تم الدفع بعد تاريخ الاستحقاق' : null,
        ]);


        if ($newRemaining > 0) {
            $nextDueDate = now()->addDays($intervalDays);
            $this->installmentRepository->createNextInstallment([
                'order_id' => $order->id,
                'remaining_amount' => $newRemaining,
                'due_date' => $nextDueDate,
            ]);
        }
        else{
            $this->orderRepository->makeOrderPaid($order);
        }
        return $newRemaining ;

    }

    public function getUserUnpaidInstallments(User $user)
    {
        return $this->installmentRepository->getUnpaidInstallmentsByUser($user);
    }

    public function getInstallmentsPlanForOrder($orderId): array
    {
        $settings = app(InstallmentSettings::class);
        $order = $this->orderRepository->getWithInstallments($orderId);

        if (!$order) {
            throw new \Exception("الطلب غير موجود.");
        }

        $nextInstallment = $order->installments()
            ->where('status', 'pending')
            ->orderBy('due_date')
            ->first();

        if (!$nextInstallment) {
            return [
                'message' => 'لا توجد دفعات قادمة، يبدو أن الطلب مدفوع بالكامل.'
            ];
        }

        $totalRemaining=$nextInstallment->remaining_amount;

        $finalDueDate = $order->created_at->copy()->addDays($settings->max_duration_days);

        $minPaymentAmount = $settings->minimum_payment_percentage * $order->final_price;



        return [
            'next_minimum_payment' =>min($minPaymentAmount, $totalRemaining),
            'next_due_date' => $nextInstallment->due_date->toDateString(),
            'next_installment_status' => $nextInstallment->status,
            'total_remaining_amount' =>$totalRemaining,
            'max_due_date' => $finalDueDate->toDateString(),
         ];
    }

    public function getOrderInstallmentsBatchs($orderId){
        return $this->orderRepository->getWithInstallments($orderId);
    }

    public function payNextInstallmentAmount(array $data){
        $decoded = json_decode($data['qr_data'], true);

        if (!$decoded || !isset($decoded['order_id'], $decoded['user_id'])) {
            throw new \Exception('QR code غير صالح.');
        }

        $order = $this->orderRepository->get($decoded['order_id']);
        if ($this->orderRepository->isPaid($order) ) {
            throw new \Exception("تم تأكيد دفع كامل المبلغ مسبقاً.");
        }
        $newRemaining=$this->payNextInstallment($order ,$data['paidAmount']);
        return [
            'order_id' => $order->id,
            'status' => $order->status,
            'message' => 'تم تأكيد الدفع بنجاح , المبلغ المتبقي لدفعه هو' .$newRemaining,
        ];


    }

}

