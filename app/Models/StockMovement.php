<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
class StockMovement extends Model
{
    protected $table = 'stock_movements_view';
    public $timestamps = false;

    // إضافة العلاقات إذا لزم الأمر
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }


    public function scopeBetweenDates(Builder $q, string $from, string $to): Builder
    {
        $fromDt = Carbon::parse($from)->startOfDay();
        $toDt = Carbon::parse($to)->endOfDay();
        return $q->whereBetween('movement_date', [$fromDt, $toDt]);
    }

    public function scopeOfType(Builder $q, ?string $type): Builder
    {
        if ($type && in_array($type, ['incoming','outgoing'])) {
            return $q->where('type', $type);
        }
        return $q;
    }

    public function scopeForItem(Builder $q, ?int $itemId): Builder
    {
        return $itemId ? $q->where('item_id', $itemId) : $q;
    }

}
