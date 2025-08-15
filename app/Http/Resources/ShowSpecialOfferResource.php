<?php

namespace App\Http\Resources;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShowSpecialOfferResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request)
    {
        return [
          'offer_id'=>$this->id ,
          'discount_type'=> $this->discount_type ,
          'discount_value' =>$this->discount_value ,
          'description'=>$this->description,
          'starts_at'=>$this->starts_at,
          'ends_at'=>$this->ends_at,
          'offer_items' =>$this->items->map(function ($item) {
              return [
                    'offer_item_id'=>$item->id ,
                    'original_item_id'=>$item->item_id ,
                    'required_quantity'=>$item->required_quantity ,
                    'item_id'=>$item->item_id,
                    'item'=>Item::query()->find($item->item_id)->name,
                    'item_unit_id'=> $this->when(!is_null($item->item_unit_id), $item->item_unit_id),
                  ];
          }),
        ];
    }
}
