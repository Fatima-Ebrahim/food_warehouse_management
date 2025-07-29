<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePointsSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // أو طبق صلاحياتك حسب الحاجة
    }

        public function rules(): array
        {
            return [
                'sy_lira_per_point' => 'numeric|min:1000',
                'invoice_threshold_amount'   => 'numeric|min:1000',
                'points_per_threshold'=>'integer|min:1'
            ];
        }
}
