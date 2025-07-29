<?php

namespace App\Http\Requests\AdminRequests\PurchaseRequests;

use Illuminate\Foundation\Http\FormRequest;

class StorePurchaseOrderRequest extends FormRequest
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
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'required|date|after_or_equal:order_date',
            'order_notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.ordered_quantity' => 'required|numeric|min:0.001',
            'items.*.ordered_price' => 'required|numeric|min:0',
            'items.*.unit_id' => 'nullable|exists:units,id',
        ];
    }

    public function messages()
    {
        return [
            'items.required' => 'you must add one item at least',
        ];
    }
}
