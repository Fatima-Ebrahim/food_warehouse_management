<?php

namespace App\Http\Resources\WarehouseDesignResources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WarehouseCoordinateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'x' => $this->x,
            'y' => $this->y,
            'z' => $this->z,
            'zone' => $this->whenLoaded('zone'),
            'cabinet'=> $this->whenLoaded('cabinet'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
