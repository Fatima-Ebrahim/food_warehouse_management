<?php

namespace App\Http\Requests\WarehouseKeeperRequests\InventoryRequests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStocktakeRequest extends FormRequest
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
            'type' => 'required|in:immediate,scheduled',
            'notes' => 'nullable|string',
            'schedule_frequency' => 'required_if:type,scheduled|in:days,weeks,months',
            'schedule_interval' => 'required_if:type,scheduled|integer|min:1',
        ];
    }
}
