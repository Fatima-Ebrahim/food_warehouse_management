<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StorageDimension extends Model
{
    protected $table='storage_dimensions';
    protected $fillable=['unit_id',
        'length',
        'width',
        'height',
        'volume',
        'max_weight',
        'is_stackable'];

    public function unit(){
        return $this->belongsTo(Unit::class,'unit_id');
    }

}
