<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BatchStorageLocation extends Model
{
    protected $fillable = [
        'shelf_id',
        'purchase_receipt_items_id',
        'quantity',
    ];
    public function purchaseReceiptItem()
    {
              return $this->belongsTo(PurchaseReceiptItem::class, 'purchase_receipt_items_id');
    }
    public function shelf()
    {
        return $this->belongsTo(Shelf::class);
    }
}
