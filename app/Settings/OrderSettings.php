<?php
namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class OrderSettings extends Settings
{
    public bool $auto_cancel_delayed_orders;     // هل يتم إلغاء الطلب تلقائياً عند التأخير؟
    public int $delayed_days_limit;              // كم عدد الأيام المسموحة قبل اعتبار الطلب متأخرًا
    public bool $notify_on_delay;                // هل يتم إرسال إشعار عند التأخير؟
    public int $days_to_sent_notification;

    public static function group(): string
    {
        return 'orders';
    }
}
