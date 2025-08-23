<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable=['name','type'];
    protected $casts = [
        'type' => 'string'
    ];
    protected $hidden=['created_at','updated_at'];

    public function itemUnits(){
        return $this->hasMany(ItemUnit::class,'unit_id');
    }

    public function items(){
        return $this->hasMany(Item::class,'base_unit_id')->withTrashed();
    }

    public function storageDimension()
    {
        return $this->hasOne(StorageDimension::class, 'unit_id');
    }

}
