<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'payment_type' => 'required|in:cash,installment',
            'points_used' => 'nullable|integer|min:0',
            'items' => 'array|min:1',
            'items.*.cart_item_id' => 'exists:cart_items,id',
            'items.*.quantity' => 'numeric|min:0.1',
            'offers'=>'array|min:1',
            'offers.*.cart_offer_id'=>'exists:cart_offers,id',
            'offers.*.quantity'=>'numeric|min:0.1'

        ];
    }
}
