<?php

namespace App\Http\Requests\AdminRequests\WarehouseDesignRequests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCabinetRequest extends FormRequest
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
            'code' => 'nullable|string|max:255',
            'width' => 'sometimes|numeric|min:0',
            'length' => 'sometimes|numeric|min:0',
            'height' => 'sometimes|numeric|min:0',
            'shelves_count' => 'sometimes|integer|min:0',
            'coordinate_ids' => 'nullable|array',
            'coordinate_ids.*' => 'required|exists:warehouse_coordinates,id',
        ];
    }
}
