<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WarehouseCoordinate extends Model
{
use SoftDeletes;

protected $fillable = [
'zone_id',
'cabinet_id',
'x',
'y',
'z'
];

public function zone()
{
return $this->belongsTo(Zone::class);
}

    public function cabinet()
    {
        return $this->belongsTo(Cabinet::class);
    }
}
