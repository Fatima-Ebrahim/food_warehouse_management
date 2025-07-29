<?php

namespace App\Http\Resources\Items;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'item_unit_id' => $this->item_unit_id,
            'quantity' => $this->quantity,
            'how_many_each_one_has' => optional($this->itemUnit)->conversion_factor,
            'selling_price_for_one' => optional($this->itemUnit)->selling_price,
            'item_id' => optional($this->itemUnit)->item_id,
            'item_image'=>optional($this->itemUnit->item)->image,
            'unit_id' => optional($this->itemUnit)->unit_id,
        ];
    }
}
