<?php

namespace App\Actions;

use App\Models\Item;
use App\Models\ItemUnit;
use App\Models\PurchaseReceiptItem;

class UpdateItemQuantitiesAction
{

    public function handle(PurchaseReceiptItem $receiptItem): void
    {
        $itemUnit = ItemUnit::where('item_id', $receiptItem->item_id)
            ->where('unit_id', $receiptItem->unit_id)
            ->first();

        if (!$itemUnit) {
            return;
        }

        $baseQuantity = $receiptItem->quantity * $itemUnit->conversion_factor;

        $receiptItem->quantity_in_base_unit = $baseQuantity;
        $receiptItem->available_quantity = $baseQuantity;
        $receiptItem->save();

        $totalAvailableQuantity = PurchaseReceiptItem::where('item_id', $receiptItem->item_id)
            ->sum('available_quantity');

        $item = Item::find($receiptItem->item_id);
        if ($item) {
            $item->Total_Available_Quantity = $totalAvailableQuantity;
            $item->save();
        }
    }
}
