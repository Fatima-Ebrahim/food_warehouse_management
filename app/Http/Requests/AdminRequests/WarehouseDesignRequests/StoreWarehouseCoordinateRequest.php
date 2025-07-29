<?php

namespace App\Http\Requests\AdminRequests\WarehouseDesignRequests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWarehouseCoordinateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'x' => 'required|numeric',
            'y' => 'required|numeric',
            'z' => 'required|numeric'
        ];
    }
}
