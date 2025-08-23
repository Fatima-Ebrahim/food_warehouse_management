<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Item extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'name',
        'category_id',
        'Total_Available_Quantity',
        'code',
        'description',
        'base_unit_id',
        'minimum_stock_level',
        'maximum_stock_level',
        'storage_conditions',
        'barcode',
        'image',
        'supplier_id',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
        'storage_conditions' => 'array',
        'minimum_stock_level' => 'decimal:2',
        'maximum_stock_level' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class,'category_id');
    }
    public function itemUnits()
    {
        return $this->hasMany(ItemUnit::class,'item_id');
    }
    public function baseUnit(){
        return $this->belongsTo(Unit::class,'base_unit_id');
    }
    public function storageConditions()
    {
        return $this->storage_conditions ?? [];
    }
    public function supplier()
    {
        return $this->belongsTo(Supplier::class,'supplier_id');
    }

    public function purchaseReceiptItems()
    {
        return $this->hasMany(PurchaseReceiptItem::class,'item_id');
    }

    public function offerItem(){
        return $this->hasOne(SpecialOfferItem::class,'item_id');
    }

}
