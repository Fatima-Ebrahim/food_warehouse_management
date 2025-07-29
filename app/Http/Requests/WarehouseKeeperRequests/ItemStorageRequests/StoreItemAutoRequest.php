<?php

namespace App\Http\Requests\WarehouseKeeperRequests\ItemStorageRequests;

use Illuminate\Foundation\Http\FormRequest;

class StoreItemAutoRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'purchase_receipt_item_id' => 'required|exists:purchase_receipt_items,id',
            'zone_id' => 'required|exists:zones,id',
            'unit_id' => 'required|exists:units,id'
        ];
    }
}
