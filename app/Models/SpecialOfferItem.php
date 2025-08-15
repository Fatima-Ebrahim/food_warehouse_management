<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SpecialOfferItem extends Model
{
    protected $fillable =[
        'offer_id',
        'required_quantity',
        'item_unit_id',
        'item_id'
    ];

    public function offer(){
        return $this->belongsTo(SpecialOffer::class,'offer_id');
    }

    public function item(){
        return $this->belongsTo(Item::class,'item_id');
    }

    public function itemUnit(){
        return $this->belongsTo(ItemUnit::class ,'item_unit_id');
    }

    public function OrderOfferItemBatchDetails()
    {
        return $this->hasMany(OrderBatchDetail::class,'order_offer_Items_id');
    }
}
