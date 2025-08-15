<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeliverOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return True;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'order_id' => 'required|exists:orders,id',
            'paidAmount'=>'numeric|nullable' ,
            'batchesData'=>'array|required' ,
            'batchesData.*.batch_id'=>'exists:purchase_receipt_items,id' ,
            'batchesData.*.order_offer_id'=>'exists:order_offers,id',
            'batchesData.*.order_offer_item_id'=>'exists:special_offer_items,id',
            'batchesData.*.order_item_id'=>'exists:order_items,id',
            'batchesData.*.quantity'=>'numeric|min:0.1'
        ];
    }
}
