<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInstallmentsSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // أو طبق صلاحياتك حسب الحاجة
    }

    public function rules(): array
    {
        return [
            'enable_installments' => 'bool',
            'first_payment_percentage'   => 'numeric',
            'minimum_payment_percentage'   =>"numeric",
            'payment_interval_days' => "integer",
            'max_duration_days'   => "integer",
            'enforce_amount_limit'   => "bool",
            'max_installment_amount' => "numeric",
            'reject_if_insufficient_amount'   => 'bool',
            'enforce_points_limit'   => 'bool',
            'min_points_required' => "integer",
            'reject_if_insufficient_points'   => 'bool',
        ];
    }
}
