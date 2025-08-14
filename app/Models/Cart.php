<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = ['user_id'];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class,'cart_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class,'cart_id');
    }

    public function cartOffer(){
        return $this->hasMany(CartOffer::class,'cart_id');
    }
}
