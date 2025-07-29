<?php

namespace App\Http\Resources\WarehouseDesignResources;

use Illuminate\Http\Resources\Json\JsonResource;

class ShelfResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'cabinet_id' => $this->cabinet_id,
            'height' => $this->height,
            'current_weight' => $this->current_weight,
            'max_weight' => $this->max_weight,
            'current_length' => $this->current_length,
            'max_length' => $this->max_length,
            'levels' => $this->levels,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
