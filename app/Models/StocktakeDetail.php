<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StocktakeDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'stocktake_id', 'item_id', 'expected_quantity',
        'counted_quantity', 'discrepancy'
    ];

    public function stocktake()
    {
        return $this->belongsTo(Stocktake::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
