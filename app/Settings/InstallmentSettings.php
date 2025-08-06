<?php
namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class InstallmentSettings extends Settings
{
    public bool $enable_installments;             // 1) تفعيل أو إلغاء التقسيط
    public float $first_payment_percentage;       // 2) نسبة الدفعة الأولى
    public float $minimum_payment_percentage;       //3)الحد الادنى للدفعات
    public int $payment_interval_days;            // 4) المدة الزمنية بين كل دفعة (بالأيام)
    public int $max_duration_days;                // 5) أقصى مدة لإنهاء كامل الأقساط (بالأيام)
    public bool $enforce_amount_limit;            // 6) هل يتم التحقق من تجاوز الحد الأعلى؟
    public float $max_installment_amount;         // 7) أكبر مبلغ يُسمح بالتقسيط له
    public bool $reject_if_insufficient_amount; // 8) الإجراء عند عدم توفر المبلغ (مثلاً "reject", "waiting")
    public bool $enforce_points_limit;            // 9) هل يتم التحقق من تجاوز الحد الأعلى؟
    public int $min_points_required;              // 10) الحد الأدنى من النقاط المطلوبة للتقسيط
    public bool $reject_if_insufficient_points; // 11) الإجراء عند عدم توفر النقاط  (مثلاً "reject", "waiting")


    public static function group(): string
    {
        return 'Installments';
    }


}
