<?php

namespace App\Http\Resources\WarehouseDesignResources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ZoneResource extends JsonResource
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
            'name' => $this->name,
            'type' => $this->type,
            'temperature_range' => [
                'min' => $this->min_temperature,
                'max' => $this->max_temperature
            ],
            'humidity_range' => [
                'min' => $this->humidity_min,
                'max' => $this->humidity_max
            ],
            'conditions' => [
                'ventilated' => $this->is_ventilated,
                'shaded' => $this->is_shaded,
                'dark' => $this->is_dark
            ],
            'coordinates' => $this->whenLoaded('coordinates'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
