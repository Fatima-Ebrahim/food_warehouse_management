<?php

namespace App\Http\Resources\Items;

use App\Models\Item;
use App\Models\ItemUnit;
use App\Models\Supplier;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
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
            'name'=>$this->name,
            'image' => $this->image,
            'description'=>$this->description,
            'Total_Available_Quantity'=>$this->Total_Available_Quantity,
            'code' => $this->code,
            'base_unit_id'=>$this->base_unit_id,
            'minimum_stock_level' => $this->minimum_stock_level,
            'maximum_stock_level'=>$this->maximum_stock_level,
            'storage_conditions'=>$this->storage_conditions,
            'barcode'=>$this->barcode,
            'supplier_id'=>$this->supplier_id,
            'supplier_name'=>Supplier::query()->find($this->supplier_id)->name,
            'base_unit_name'=>Unit::query()->find($this->base_unit_id)->name,
            'selling_price'=>ItemUnit::query()->where('item_id',$this->id)
                ->where('unit_id',$this->base_unit_id)->value('selling_price'),
        ];
    }
}
