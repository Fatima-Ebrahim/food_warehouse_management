<?php

namespace App\Repositories\WarehouseKeeperRepository;

use App\Models\ItemUnit;
use App\Models\PurchaseReceiptItem;
use App\Models\Item;
use App\Models\Stocktake;
use App\Models\StocktakeDetail;

class InventoryRepository
{
    /*public function getExpectedQuantityForItem( $itemId)
    {
        $totalAvailable = PurchaseReceiptItem::query()->where('item_id', $itemId)
            ->sum('available_quantity');

        return (float) $totalAvailable;
    }*/
    public function getExpectedQuantityForItem($itemId, $targetUnitId = null)
    {
        $item = Item::with('baseUnit')->find($itemId);
        if (!$item) {
            return 0.0;
        }

        $finalTargetUnitId = $targetUnitId ?? $item->base_unit_id;

        $baseUnitConversionFactor = ItemUnit::where('item_id', $itemId)->where('unit_id', $item->base_unit_id)->first()->conversion_factor ?? 1;
        $targetUnitConversionFactor = ItemUnit::where('item_id', $itemId)->where('unit_id', $finalTargetUnitId)->first()->conversion_factor ?? $baseUnitConversionFactor;

        $purchaseReceiptItems = PurchaseReceiptItem::with('itemUnit')
            ->where('item_id', $itemId)
            ->get();

        $totalConvertedQuantity = 0.0;
        foreach ($purchaseReceiptItems as $receiptItem) {
            $originalQuantity = $receiptItem->available_quantity;
            $originalUnitConversionFactor = $receiptItem->itemUnit->conversion_factor;

            if ($originalUnitConversionFactor > 0) {
                $quantityInBaseUnit = $originalQuantity / $originalUnitConversionFactor;
                $convertedQuantity = $quantityInBaseUnit * $targetUnitConversionFactor;
                $totalConvertedQuantity += $convertedQuantity;
            }
        }

        return (float) $totalConvertedQuantity;
    }

    public function getItemsDetails(array $itemIds)
    {
        return Item::query()->whereIn('id', $itemIds)->get()->keyBy('id');
    }

    public function createStocktake(array $data)
    {
        return Stocktake::query()->create($data);
    }

    public function findStocktake( $id)
    {
        return Stocktake::query()->find($id);
    }

    public function saveStocktakeDiscrepancies( $stocktakeId, array $discrepancies)
    {
        foreach ($discrepancies as $item) {
            StocktakeDetail::query()->create([
                'stocktake_id' => $stocktakeId,
                'item_id' => $item['item_id'],
                'expected_quantity' => $item['expected_quantity'],
                'counted_quantity' => $item['counted_quantity'],
                'discrepancy' => $item['discrepancy'],
            ]);
        }
    }

    public function update( $id, array $data)
    {
        $stocktake = $this->findStocktake($id);
        if ($stocktake) {
            $stocktake->update($data);
        }
        return $stocktake;
    }

    public function getReports()
    {
        return Stocktake::query()->select('id', 'requested_at', 'type', 'status', 'completed_at')
            ->orderBy('requested_at', 'desc')
            ->get();
    }

    public function getReportDetails( $id)
    {
        return Stocktake::with('details.item:id,name,code')->findOrFail($id);
    }
}
