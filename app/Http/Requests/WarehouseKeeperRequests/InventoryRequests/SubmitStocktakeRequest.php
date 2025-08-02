<?php

namespace App\Http\Requests\WarehouseKeeperRequests\InventoryRequests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitStocktakeRequest extends FormRequest
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
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer|exists:items,id',
            'items.*.counted_quantity' => 'required|numeric|min:0',
            'items.*.unit_id' => 'sometimes|integer|exists:units,id'
        ];
    }
}
