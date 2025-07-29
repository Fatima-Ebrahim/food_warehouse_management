<?php


namespace App\Http\Resources\WarehouseDesignResources;

use Illuminate\Http\Resources\Json\JsonResource;

class CabinetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'width' => $this->width,
            'length' => $this->length,
            'height' => $this->height,
            'shelves_count' => $this->shelves_count,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'shelves' => ShelfResource::collection($this->whenLoaded('shelves')), 
            'coordinates' => $this->whenLoaded('coordinates'),
        ];
    }
}
