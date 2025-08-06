<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Installment extends Model
{
    protected $fillable=[
        'order_id',
        'paid_amount',
        'remaining_amount',
        'due_date',
        'paid_at',
        'note',
        'status'
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_at' => 'datetime',
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
