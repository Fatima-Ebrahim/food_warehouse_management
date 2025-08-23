<?php

namespace App\Http\Resources;

use App\Models\Item;
use App\Services\Orders\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderOfferResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request)
    {

        return [
            'order_offer_id'=>$this->id,
            "requested_quantity"=>$this->quantity,
            'offer_id'=>$this->offer->id ,
            'discount_type'=> $this->offer->discount_type ,
            'discount_value' =>$this->offer->discount_value ,
            'description'=>$this->offer->description,
            'starts_at'=>$this->offer->starts_at,
            'ends_at'=>$this->offer->ends_at,
            'offer_items' =>$this->offer->Items->map(function ($item) {
                return [
                    'offer_item_id'=>$item->id ,
                    'original_item_id'=>$item->item_id ,
                    'required_quantity'=>$item->required_quantity ,
                    'total_requested_quantity_in_base_unit'=>
                        app(OrderService::class)->calculateQuantityInBaseUnit(
                       $item->item_id,
                        optional($item->itemUnit)->unit_id,
                        optional($item->itemUnit)->conversion_factor,
                            $item->required_quantity*$this->quantity
                    ),
                    'item_id'=>$item->item_id,
                    'item'=>$item->item->name,
                    'item_unit_id'=> $item->item_unit_id ,

                ];
            }),
        ];
    }
}
