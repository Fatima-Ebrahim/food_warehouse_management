<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrdersSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // أو طبق صلاحياتك حسب الحاجة
    }

        public function rules(): array
        {
            return [
                'auto_cancel_delayed_orders' => 'bool',
                'delayed_days_limit'   => 'integer|min:1',
                'notify_on_delay'=>'bool',
                'days_to_sent_notification'=>'Integer|min:1'
            ];
        }
}
