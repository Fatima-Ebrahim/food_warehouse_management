<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PriceCalculationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'items' => 'array|min:1',
            'items.*.cart_item_id' => 'exists:cart_items,id',
            'items.*.quantity' => 'numeric|min:0.1',
            'offers' => 'array|min:1',
            'offers.*.cart_offer_id' => 'exists:cart_offers,id',
            'offers.*.quantity' => 'numeric|min:0.1',
            'points_used' => 'nullable|integer|min:0',
        ];
    }
}
