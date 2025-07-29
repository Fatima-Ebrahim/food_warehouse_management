<?php

namespace App\Http\Requests\WarehouseKeeperRequests\PurchaseOrderRequests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductionDateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'date' => 'required|date'
        ];
    }
}
