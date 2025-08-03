<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'item_id'=> optional($this->itemUnit)->item_id,
            'item_name'=>optional($this->itemUnit->item)->name,
            'quantity'=>$this->quantity,
            'price'=>$this->price,
            'item_unit_id'=>$this->item_unit_id,
            'unit_name'=>optional($this->itemUnit->unit)->name,
            'unit_type'=>optional($this->itemUnit->unit)->type,
        ];
    }
}
