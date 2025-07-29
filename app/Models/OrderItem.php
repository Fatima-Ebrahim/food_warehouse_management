<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'item_unit_id',
        'quantity',
        'price'];

    protected $casts = [
        'quantity' => 'decimal:3',
        'price' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class,'order_id');
    }

    public function itemUnit()
    {
        return $this->belongsTo(ItemUnit::class,'item_unit_id');
    }

    public function orderBatchDetails()
    {
        return $this->hasMany(OrderBatchDetail::class,'order_item_id');
    }
}
