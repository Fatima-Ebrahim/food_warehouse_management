<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Zone extends Model
{
protected $fillable = [
'name',
'type',
'min_temperature',
'max_temperature',
'humidity_min',
'humidity_max',
'is_ventilated',
'is_shaded',
'is_dark'
];

public function coordinates()
{
return $this->hasMany(WarehouseCoordinate::class);
}



    public function shelves(): HasMany
    {
        return $this->hasMany(Shelf::class);
    }

    public function getOccupancyRateAttribute(): float
    {
        $totalVolume = $this->shelves->sum(function($shelf) {
            return $shelf->width * $shelf->length * $shelf->height;
        });

        $usedVolume = $this->shelves->sum(function($shelf) {
            return $shelf->storageUnits->sum('used_volume');
        });

        return $totalVolume > 0 ? ($usedVolume / $totalVolume) * 100 : 0;
    }
}
