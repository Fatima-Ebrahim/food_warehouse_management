<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stocktake extends Model
{
    use HasFactory;

    protected $fillable = [
        'requested_at', 'type', 'status', 'notes', 'schedule_frequency',
        'schedule_interval', 'scheduled_at', 'is_active', 'completed_at'
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function details()
    {
        return $this->hasMany(StocktakeDetail::class);
    }
}
