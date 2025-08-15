<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class DesignSettings extends Settings
{
    public bool $complete; // هذا المتغير رح يخزن القيمة

    public static function group(): string
    {
        return 'design'; // اسم المجموعة
    }
}
