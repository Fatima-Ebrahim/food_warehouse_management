<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesByCustomer extends Model
{
    protected $table = 'customer_sales_item_view';
    protected $guarded = [];

    public $timestamps = false;

    // علاقات إذا لزم الأمر
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }
}
