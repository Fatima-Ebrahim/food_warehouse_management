<?php

namespace App\Http\Resources;

use App\Services\Orders\OrderService;
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
            'item_id'=>$this->itemUnit->item_id,
            'item_name'=>$this->itemUnit->item->name,
            'quantity'=>$this->quantity,
            'price'=>$this->price,
            'item_unit_id'=>$this->item_unit_id,
            'unit_name'=>$this->itemUnit->unit->name,
            'unit_type'=>$this->itemUnit->unit->type,
            'total_requested_quantity_in_base_unit'=>app(OrderService::class)->calculateQuantityInBaseUnit(
              $this->itemUnit->item_id,
                $this->itemUnit->unit->id,
               $this->itemUnit->conversion_factor,
                $this->quantity
            ),
        ];
    }
}
