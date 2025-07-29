<?php

namespace App\Http\Requests\WarehouseKeeperRequests\ItemStorageRequests;

use Illuminate\Foundation\Http\FormRequest;

class StoreItemRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'purchase_receipt_item_id' => 'required|exists:purchase_receipt_items,id',
            'shelf_id' => 'required|exists:shelves,id',
            'quantity' => 'required|numeric|min:0.01'
        ];
    }
}
