<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderBatchDetail extends Model
{
    protected $fillable = [
        'order_item_id',
        'purchase_receipt_item_id',
        'quantity'];

    protected $casts = [
        'quantity' => 'decimal:3',
    ];

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class,'order_item_id');
    }

    public function purchaseReceiptItem()
    {
        return $this->belongsTo(PurchaseReceiptItem::class,'purchase_receipt_item_id');
    }
}
