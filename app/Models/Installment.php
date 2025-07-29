<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Installment extends Model
{
    protected $fillable=[
        'order_id',
        'amount',
        'due_date',
        'paid_at',
        'note'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class,'order_id');
    }

    public function isPaid()
    {
        return !is_null($this->paid_at);
    }
}
