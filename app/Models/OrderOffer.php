<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderOffer extends Model
{
    protected $fillable=[
        'order_id',
        'offer_id' ,
        'quantity' ,
        'price'
    ];

    public function order(){
        return $this->belongsTo(Order::class,'order_id');
    }
    public function offer(){
        return $this->belongsTo(SpecialOffer::class,'offer_id');
    }

    public function OrderOfferItemBatchDetails()
    {
        return $this->hasMany(OrderOfferItemBatchDetails::class,'order_offer_id');
    }
}
