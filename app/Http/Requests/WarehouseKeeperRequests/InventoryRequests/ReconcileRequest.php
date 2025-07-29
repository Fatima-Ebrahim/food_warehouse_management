<?php

namespace App\Http\Requests\WarehouseKeeperRequests\InventoryRequests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReconcileRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'items' => 'required|array|min:1',
            'items.*.item_id' => [
                'required',
                'integer',
                Rule::exists('items', 'id')
            ],
            'items.*.counted_quantity' => 'required|numeric|min:0'
        ];
    }
}
