<?php

namespace App\Http\Requests\WarehouseKeeperRequests\ItemStorageRequests;

use Illuminate\Foundation\Http\FormRequest;

class GetShelfStatusesRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'cabinet_id' => 'required|integer|exists:cabinets,id',
        ];
    }
}
