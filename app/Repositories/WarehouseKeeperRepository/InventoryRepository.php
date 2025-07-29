<?php

namespace App\Repositories\WarehouseKeeperRepository;

use App\Models\PurchaseReceiptItem;
use App\Models\Item;
use App\Models\Stocktake;
use App\Models\StocktakeDetail;

class InventoryRepository
{
    public function getExpectedQuantityForItem(int $itemId): float
    {
        $totalAvailable = PurchaseReceiptItem::query()->where('item_id', $itemId)
            ->sum('available_quantity');

        return (float) $totalAvailable;
    }

    public function getItemsDetails(array $itemIds)
    {
        return Item::query()->whereIn('id', $itemIds)->get()->keyBy('id');
    }

    public function createStocktake(array $data): Stocktake
    {
        return Stocktake::query()->create($data);
    }

    public function findStocktake(int $id): ?Stocktake
    {
        return Stocktake::query()->find($id);
    }

    public function saveStocktakeDiscrepancies(int $stocktakeId, array $discrepancies): void
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

    public function update(int $id, array $data): ?Stocktake
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

    public function getReportDetails(int $id)
    {
        return Stocktake::with('details.item:id,name,code')->findOrFail($id);
    }
}
