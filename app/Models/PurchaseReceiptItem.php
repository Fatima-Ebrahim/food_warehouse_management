<?php

// app/Models/PurchaseReceiptItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseReceiptItem extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'item_id',
        'unit_id',
        'quantity',
        'price',
        'notes',
        'available_quantity',
        'notes',
        'total_price',
        'unit_weight',
        'total_weight',
        'expiry_date',
        'production_date'
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'production_date' => 'date',
    ];
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class,'purchase_order_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class,'item_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class,'unit_id');
    }
    public function orderBatchDetails()
    {
        return $this->hasMany(OrderBatchDetail::class,'purchase_receipt_item_id');
    }

    public function OrderOfferItemBatchDetails()
    {
        return $this->hasMany(OrderBatchDetail::class,'purchase_receipt_item_id');
    }
    public function storageLocation()
    {
        return $this->hasMany(BatchStorageLocation::class, 'purchase_receipt_items_id');
    }

    public function itemUnit()
    {
        return $this->belongsTo(ItemUnit::class);
    }
}
