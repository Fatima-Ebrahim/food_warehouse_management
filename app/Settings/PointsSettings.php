<?php
namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class PointsSettings extends Settings
{
    public int $sy_lira_per_point;           // كم تساوي كل نقطة بالليرة السورية
    public int $invoice_threshold_amount;    // المبلغ الذي يجب أن تبلغه الفاتورة لمنح نقاط
    public int $points_per_threshold;

    public static function group(): string
    {
        return 'points';
    }
}
