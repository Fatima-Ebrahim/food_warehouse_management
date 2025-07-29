<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shelf extends Model
{
    protected $fillable = [
        'code',
        'cabinet_id',
        'height',
        'current_weight',
        'max_weight',
        'current_length',
        'max_length',
        'levels',
    ];

    public function cabinet(): BelongsTo
    {
        return $this->belongsTo(Cabinet::class);
    }

public function coordinate(): BelongsTo
{
return $this->belongsTo(WarehouseCoordinate::class, 'warehouse_coordinate_id');
}


    public function storageUnits()

    {
        return $this->hasMany(StorageUnit::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function getAvailableVolumeAttribute(): float
    {
        return ($this->width * $this->length * $this->height) -
            $this->storageUnits->sum('used_volume');
    }
    public function warehouseCoordinate()
    {
        return $this->belongsTo(WarehouseCoordinate::class);
    }



    public function getAvailableWeightCapacityAttribute(): float
    {
        return $this->storageUnits->sum('max_weight') -
            $this->storageUnits->sum('used_weight');
    }
//    public function getAvailableVolumeAttribute()
//    {
//        return $this->storageUnits->where('is_active', true)
//            ->sum(function($unit) {
//                return $unit->volume - $unit->current_volume_used;
//            });
//    }

    public function getAvailableWeightAttribute()
    {
        return $this->storageUnits->where('is_active', true)
            ->sum(function($unit) {
                return $unit->max_weight - $unit->current_weight_used;
            });
    }
}
