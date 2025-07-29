<?php

namespace App\Http\Requests\WarehouseKeeperRequests\PurchaseOrderRequests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExpiryDateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'date' => 'required|date|after:today'
        ];
    }
}
