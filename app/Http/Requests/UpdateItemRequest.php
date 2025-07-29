<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateItemRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'base_unit_id' => 'exists:units,id',
            'minimum_stock_level' => 'decimal:0,2|min:0',
            'maximum_stock_level' => 'nullable|decimal:0,2|gt:minimum_stock_level',
            'storage_conditions' => 'nullable|json',
            'weight_per_unit' => 'decimal:0,2|min:0',
            'volume_per_unit' => 'decimal:0,2|min:0',
            'Total_Available_Quantity' => 'integer|min:0',
        ];
    }
}
