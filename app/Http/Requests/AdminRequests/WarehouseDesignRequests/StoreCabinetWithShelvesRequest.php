<?php

namespace App\Http\Requests\AdminRequests\WarehouseDesignRequests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCabinetWithShelvesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'code' => 'nullable|string|max:255|unique:cabinets,code',
            'width' => 'required|numeric|min:0',
            'length' => 'required|numeric|min:0',
            'height' => 'required|numeric|min:0',
            'coordinate_ids' => 'nullable|array',
            'coordinate_ids.*' => 'integer|exists:warehouse_coordinates,id',

            'shelf_defaults' => 'required|array',
            'shelf_defaults.height' => 'required|numeric|min:0',
            'shelf_defaults.max_weight' => 'required|numeric|min:0',
            'shelf_defaults.max_length' => 'required|numeric|min:0',

            'shelves' => 'required|array|min:1',
            'shelves.*.code' => 'nullable|string|max:255|unique:shelves,code',
            'shelves.*.levels' => 'required|integer|min:1',
            'shelves.*.current_weight' => 'nullable|numeric|min:0',
            'shelves.*.current_length' => 'nullable|numeric|min:0',
        ];
    }
}
