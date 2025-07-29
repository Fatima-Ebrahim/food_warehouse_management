<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = ['cart_id', 'item_unit_id', 'quantity'];

    protected $casts = [
        'quantity' => 'decimal:3',
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class,'cart_id');
    }

    public function itemUnit()
    {
        return $this->belongsTo(ItemUnit::class,'item_unit_id');
    }
}
