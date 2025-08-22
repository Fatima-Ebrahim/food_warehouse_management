<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesByPayment extends Model
{
     protected $table = 'sales_by_payment_view';
    protected $guarded = [];

    public $timestamps = false;

}
