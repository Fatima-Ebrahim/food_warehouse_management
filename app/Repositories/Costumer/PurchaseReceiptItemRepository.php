<?php
namespace App\Repositories\Costumer;

use App\Models\Installment;
use App\Models\Item;
use App\Models\OrderBatchDetail;
use App\Models\PurchaseReceiptItem;
use App\Models\User;

class PurchaseReceiptItemRepository{

    public function create($data){
        return OrderBatchDetail::create($data);
    }

    public function find($id){
        return PurchaseReceiptItem::find($id);
    }

    public function getOlderPurchasOfItem($itemId){
       return  PurchaseReceiptItem::query()
            ->where('item_id', $itemId)
            ->where('available_quantity', '>', 0)
            ->orderBy('created_at', 'asc')
            ->lockForUpdate()
            ->get();

    }

    public function getOlderPurchasesOfItemWithLimit($itemId, $neededQty)
    {
        $purchases = PurchaseReceiptItem::query()
            ->where('item_id', $itemId)
            ->where('available_quantity', '>', 0)
            ->orderBy('created_at', 'asc')
            ->lockForUpdate()
            ->get();

        $result = [];
        $collectedQty = 0;

        foreach ($purchases as $purchase) {
            $result[] = $purchase;
            $collectedQty += $purchase->available_quantity;

            if ($collectedQty >= $neededQty) {
                break;
            }
        }

        return collect($result);
    }

    public function getOlderPurchasesForMultipleItems(array $itemsWithQty)
    {
        $result = [];

        foreach ($itemsWithQty as $itemId => $neededQty) {
            $result[$itemId] = $this->getOlderPurchasesOfItemWithLimit($itemId, $neededQty);
        }

        return $result;
    }


    public function getAllReceiptItemForItem($itemId)
    {
        $item = Item::findOrFail($itemId);

        return $item->purchaseReceiptItems()
            ->where('available_quantity', '>', 0)
            ->orderBy('expiry_date', 'asc')
            ->limit(20)
            ->get();

    }




}
