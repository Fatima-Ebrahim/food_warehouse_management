<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderOfferItemBatchDetails extends Model
{
    protected $fillable=[
        'order_offer_id',
        'order_offer_Items_id',
        'purchase_receipt_item_id',
        'quantity'
    ];


    public function orderOfferItem()
    {
        return $this->belongsTo(SpecialOfferItem::class,'order_offer_Items_id');
    }
    public function orderOffer()
    {
        return $this->belongsTo(OrderOffer::class,'order_offer_id');
    }

    public function purchaseReceiptItem()
    {
        return $this->belongsTo(PurchaseReceiptItem::class,'purchase_receipt_item_id');
    }
}
