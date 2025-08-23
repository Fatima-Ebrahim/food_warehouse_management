<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesByProduct extends Model
{
    protected $table = 'sales_by_product_view';
    protected $guarded = [];

    public $timestamps = false;
}
