<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointTransaction extends Model
{
    protected $fillable=[
        'customer_id',
        'type',
        'points',
        'order_id',
        'reason'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class,'customer_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class,'order_id');
    }

    public function isAddition()
    {
        return $this->type === 'add';
    }

    public function isSubtraction()
    {
        return $this->type === 'subtract';
    }
}
