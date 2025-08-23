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

    public function batchStorageLocation()
    {
        return $this->belongsTo(BatchStorageLocation::class,'shelf_id');
    }

    public function getAvailableVolumeAttribute(): float
    {
        return ($this->width * $this->length * $this->height) -
            $this->storageUnits->sum('used_volume');
    }


    public function getAvailableWeightCapacityAttribute(): float
    {
        return $this->storageUnits->sum('max_weight') -
            $this->storageUnits->sum('used_weight');
    }

    public function getAvailableWeightAttribute()
    {
        return $this->storageUnits->where('is_active', true)
            ->sum(function($unit) {
                return $unit->max_weight - $unit->current_weight_used;
            });
    }
}
