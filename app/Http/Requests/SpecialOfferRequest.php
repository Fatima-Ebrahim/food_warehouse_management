<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SpecialOfferRequest extends FormRequest
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
            'discount_type' => ['required','in:fixed_price,percentage'],
            'discount_value' => ['required', 'numeric'],
            'starts_at' => ['required', 'date'],
            'description'=>['string'],
            'ends_at' => ['required', 'date', 'after_or_equal:starts_at'],
            'items' => ['required', 'array'],
            'items.*.item_id' => ['required', 'exists:items,id'],
            'items.*.item_unit_id' => ['nullable', 'exists:item_units,id'],
            'items.*.required_quantity' => ['required', 'numeric', 'min:1']
        ];
    }
}
