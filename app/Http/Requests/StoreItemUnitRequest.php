<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreItemUnitRequest extends FormRequest
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
            'item_id'=>'required|exists:items,id',
            'unit_id'=>'required|exists:units,id',
            'is_default'=>'boolean',
            'selling_price'=>'nullable|decimal:0,2|min:1',
            'conversion_factor'=>'nullable|decimal:0,2'
        ];
    }
}
