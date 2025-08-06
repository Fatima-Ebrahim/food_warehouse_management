<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemTotalQuantity extends Model
{
    protected $table = 'item_total_quantity_view';
    protected $primaryKey = 'item_id';
    public $timestamps = false;
}
