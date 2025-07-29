<?php

namespace App\Http\Requests\AdminRequests\WarehouseDesignRequests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateZoneRequest extends FormRequest
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
            'name' => 'nullable|string|max:255',
            'type' => 'nullable|in:dry,cold,frozen,loading,receiving,processing,ventilated,shaded,humid,aisle',
            'min_temperature' => 'nullable|numeric',
            'max_temperature' => 'nullable|numeric',
            'humidity_min' => 'nullable|numeric',
            'humidity_max' => 'nullable|numeric',
            'is_ventilated' => 'boolean',
            'is_shaded' => 'boolean',
            'is_dark' => 'boolean',
            'coordinate_ids' => 'nullable|array',
            'coordinate_ids.*' => 'required|exists:warehouse_coordinates,id',
        ];
    }
}
