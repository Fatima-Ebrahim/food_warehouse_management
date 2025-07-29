<?php

namespace App\Http\Requests\WarehouseKeeperRequests\PurchaseOrderRequests;

use Illuminate\Foundation\Http\FormRequest;

class GetItemsBySupplierRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'category_id' => 'nullable|integer|exists:categories,id',
            'min_stock' => 'nullable|numeric',
        ];
    }
}
