<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cabinet extends Model
{
    protected $fillable = [
        'code',
        'width',
        'length',
        'height',
        'shelves_count',
    ];

    public function warehouseCoordinate()
    {
        return $this->belongsTo(WarehouseCoordinate::class);
    }

    public function shelves()
    {
        return $this->hasMany(Shelf::class);
    }

    public function coordinates()
    {
        return $this->hasMany(WarehouseCoordinate::class, 'cabinet_id');
    }
}
