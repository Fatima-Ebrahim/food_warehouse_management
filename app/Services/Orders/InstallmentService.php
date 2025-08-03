<?php
namespace App\Services\Orders;

use App\Models\Item;
use App\Models\ItemUnit;
use App\Repositories\Costumer\CartRepository;
use App\Repositories\CustomerRepository;
use App\Settings\InstallmentSettings;

class InstallmentService{


    protected CustomerRepository $customerRepository;
    protected InstallmentSettings $settings;

    public function __construct(CustomerRepository $customerRepository, InstallmentSettings $settings)
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

//        // 2. تحقق من النقاط المطلوبة
//        $userPoints = $this->customerRepository->getPoints($userId);
//        if ($this->settings->enforce_points_limit && $userPoints < $this->settings->min_points_required) {
//            if ($this->settings->reject_if_insufficient_points) {
//                throw new \Exception("لا تملك عدد كافٍ من النقاط لتفعيل التقسيط.");
//            } else {
//                $status = 'pending';
//            }
//        }

        return $status;
    }
}

