<?php

namespace App\Http\Requests\WarehouseKeeperRequests\PurchaseOrderRequests;

use Illuminate\Foundation\Http\FormRequest;

class ProcessPartialReceiptRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'status' => 'required|in:partial,completed,over_received',
            'notes' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.id' => 'required|exists:purchase_receipt_items,id',
            'items.*.quantity' => 'nullable|numeric|min:0',
            'items.*.price' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string'
        ];
    }
}
