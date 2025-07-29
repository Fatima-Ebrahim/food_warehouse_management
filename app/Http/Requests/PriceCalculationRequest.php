<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PriceCalculationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'items' => 'required|array|min:1',
            'items.*.cart_item_id' => 'required|exists:cart_items,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'points_used' => 'nullable|integer|min:0',
        ];
    }
}
