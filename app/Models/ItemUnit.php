<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemUnit extends Model
{
    protected $fillable=['item_id',
        'unit_id',
        'conversion_factor',
        'is_default',
        'selling_price'];


    protected $hidden=[ 'created_at','updated_at'];

    public function item()
    {
        return $this->belongsTo(Item::class ,'item_id')->withTrashed();
    }
    public function unit()
    {
        return $this->belongsTo(Unit::class,'unit_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class,'item_unit_id');
    }
    public function cartItems()
    {
        return $this->hasMany(CartItem::class,'item_unit_id');
    }

    public function offerItems(){
        return $this->hasMany(SpecialOfferItem::class,'item_unit_id');
    }

}
