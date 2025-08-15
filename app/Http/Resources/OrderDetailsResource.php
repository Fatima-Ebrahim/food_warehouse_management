<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'order_id' => $this->id,
            'payment_type'=>$this->payment_type,
            'status'=>$this->status,
            'total_price'=>$this->total_price,
            'used_points'=>$this->used_points,
            'final_price'=>$this->final_price,
//            'amount_paid'=>,
            'qr_code_url' => $this->qr_code_path
                ? asset('storage/' . $this->qr_code_path)
                : null,
            'items' =>OrderItemResource::collection(optional($this->orderItems)),
            'offers' => $this->whenLoaded('orderOffer', function () {
                return $this->orderOffer->map(function ($orderOffer) {
                    return new ShowSpecialOfferResource($orderOffer->offer);
                });
            }),

        ];
    }
}
