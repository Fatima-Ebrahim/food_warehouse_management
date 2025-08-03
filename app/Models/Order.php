<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'cart_id',
        'payment_type',
        'status',
        'final_price',
        'total_price',
        'used_points',
        'qr_code_path',


        ];

    protected $casts = [
        'total_price' => 'decimal:2',
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class,'cart_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class,'order_id');
    }

    public function isPaid()
    {
        return $this->payment_status === 'paid';
    }

    public function installments()
    {
        return $this->hasMany(Installment::class,'order_id');
    }

    public function pointTransactions()
    {
        return $this->hasMany(PointTransaction::class,'order_id');
    }




}
