<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreItemRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:items,code',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'base_unit_id' => 'required|exists:units,id',
            'minimum_stock_level' => 'required|decimal:0,2|min:0',
            'maximum_stock_level' => 'nullable|decimal:0,2|gt:minimum_stock_level',

            'storage_conditions' => 'nullable|array',
            'storage_conditions.temperature' => 'nullable|array',
            'storage_conditions.temperature.min' => 'nullable|numeric',
            'storage_conditions.temperature.max' => 'nullable|numeric|gte:storage_conditions.temperature.min',

            'storage_conditions.humidity' => 'nullable|array',
            'storage_conditions.humidity.min' => 'nullable|numeric',
            'storage_conditions.humidity.max' => 'nullable|numeric|gte:storage_conditions.humidity.min',

            'storage_conditions.environment' => 'nullable|array',
            'storage_conditions.environment.requires_ventilation' => 'nullable|boolean',
            'storage_conditions.environment.requires_shade' => 'nullable|boolean',
            'storage_conditions.environment.requires_darkness' => 'nullable|boolean',

            'Total_Available_Quantity' => 'integer',
            'barcode' => 'nullable|string|unique:items,barcode',
            'selling_price'=>'nullable|decimal:0,2|min:0',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ];
    }


}
