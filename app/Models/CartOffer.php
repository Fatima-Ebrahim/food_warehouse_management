<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartOffer extends Model
{
    protected $fillable=[
        'cart_id',
        'offer_id' ,
        'quantity'
    ];

    public function cart(){
        return $this->belongsTo(Cart::class,'cart_id');
    }
    public function offer(){
        return $this->belongsTo(SpecialOffer::class,'offer_id');
    }
}
