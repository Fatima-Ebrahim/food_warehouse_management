<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request)
    {
        return [
            'id' => $this->id,
            'po_number' => $this->po_number,
            'supplier' => [
                'id' => $this->supplier->id,
                'name' => $this->supplier->name,
            ],
            'order_date' => $this->order_date,
            'expected_delivery_date' => $this->expected_delivery_date,
            'receipt_status' => $this->receipt_status,
            'order_notes' => $this->order_notes,
            'created_by' => [
                'id' => $this->createdBy->id,
                'name' => $this->createdBy->name,
            ],
            'items' => $this->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'item_id' => $item->item_id,
                    'item_name' => $item->item->name,
                    'ordered_quantity' => $item->ordered_quantity,
                    'ordered_price' => $item->ordered_price,
                    'unit' => [
                        'id' => $item->unit->id,
                        'name' => $item->unit->name,
                    ],
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
