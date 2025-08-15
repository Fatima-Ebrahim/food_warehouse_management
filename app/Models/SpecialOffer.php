<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpecialOffer extends Model
{
    protected $fillable=[
        'discount_type',
        'starts_at',
        'discount_value',
        'ends_at' ,
        'is_valid' ,
        'description'
    ];

    public function Items(){
        return $this->hasMany(SpecialOfferItem::class,'offer_id');
    }

    public function orderOffer(){
        return $this->hasMany(OrderOffer::class,'offer_id');
    }

    public function cartOffer(){
        return $this->hasMany(CartOffer::class,'cart_id');
    }
}
