<?php
namespace App\Repositories\Costumer;

use App\Models\Installment;
use App\Models\OrderBatchDetail;
use App\Models\PurchaseReceiptItem;
use App\Models\User;

class PurchaseReceiptItemRepository{

    public function create($data){
        return OrderBatchDetail::create($data);
    }

    public function getOlderPurchasOfItem($itemId){
       return  PurchaseReceiptItem::query()
            ->where('item_id', $itemId)
            ->where('available_quantity', '>', 0)
            ->orderBy('created_at', 'asc')
            ->lockForUpdate()
            ->get();

    }


}
