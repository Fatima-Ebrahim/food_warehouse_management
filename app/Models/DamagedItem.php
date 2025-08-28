<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DamagedItem extends Model
{

    use HasFactory;

    protected $fillable = [
        'purchase_receipt_item_id',
        'quantity',
        'reason',
        'reported_at',
    ];

    public function purchaseReceiptItem()
    {
        return $this->belongsTo(PurchaseReceiptItem::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}
