<?php

namespace App\Http\Requests\WarehouseKeeperRequests\ItemStorageRequests;

use Illuminate\Foundation\Http\FormRequest;

class SuggestCabinetsRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'zone_id' => 'required|integer|exists:zones,id',
            'unit_id' => 'required|integer|exists:units,id',
        ];
    }
}
