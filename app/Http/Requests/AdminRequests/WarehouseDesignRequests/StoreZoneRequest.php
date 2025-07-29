<?php

namespace App\Http\Requests\AdminRequests\WarehouseDesignRequests;

use Illuminate\Foundation\Http\FormRequest;

class StoreZoneRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'nullable|string|max:255',
            'type' => 'nullable|in:storage,loading,receiving,processing,aisle',
            'min_temperature' => 'nullable|numeric',
            'max_temperature' => 'nullable|numeric',
            'humidity_min' => 'nullable|numeric|between:0,100',
            'humidity_max' => 'nullable|numeric|between:0,100',
            'is_ventilated' => 'boolean',
            'is_shaded' => 'boolean',
            'is_dark' => 'boolean',
            'coordinate_ids' => 'nullable|array',
            'coordinate_ids.*' => 'required|exists:warehouse_coordinates,id'
        ];
    }
}
