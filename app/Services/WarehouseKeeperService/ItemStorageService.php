<?php

namespace App\Services\WarehouseKeeperService;

use App\Repositories\WarehouseKeeperRepository\ItemStorageRepository;
use Exception;
use InvalidArgumentException;

class ItemStorageService
{
    protected $repo;

    public function __construct(ItemStorageRepository $repo)
    {
        $this->repo = $repo;
    }

    public function getItemDetailsForStorage($purchaseReceiptItemId)
    {
        $details = $this->repo->getPurchaseReceiptItemDetails($purchaseReceiptItemId);
        $purchaseReceiptItem = $details['item_details'];

        return [
            'item_name' => $purchaseReceiptItem->item->name,
            'unit_name' => $purchaseReceiptItem->unit->name,
            'item_id' => $purchaseReceiptItem->item->id,
            'unit_id' => $purchaseReceiptItem->unit->id,
            'remaining_quantity' => $details['remaining_quantity'],
            'stored_quantity' => $details['stored_quantity']
        ];
    }

    public function suggestZonesWithCabinetsForItem($itemId)
    {
        return $this->repo->getSuggestedZonesWithCabinets($itemId);
    }

    public function suggestShelvesForItem($itemId, $cabinetId, $unitId)
    {
        return $this->repo->getSuggestedShelves($itemId, $cabinetId, $unitId);
    }

    public function storeItemOnShelf($purchaseReceiptItemId, $shelfId, $quantity)
    {
        $details = $this->repo->getPurchaseReceiptItemDetails($purchaseReceiptItemId);
        $item = $details['item_details'];
        $remainingQuantityForBatch = $details['remaining_quantity'];
        $shelf = $this->repo->findShelf($shelfId);

        $maxQuantityOnShelf = $this->calculateMaxQuantity($item, $shelf);
        $quantityToStore = min($quantity, $maxQuantityOnShelf, $remainingQuantityForBatch);

        if ($quantityToStore <= 0) {
            throw new Exception('Not enough capacity on the shelf or no remaining quantity to store.');
        }

        $this->repo->createOrUpdateStorageLocation($purchaseReceiptItemId, $shelfId, $quantityToStore);
        $this->repo->incrementShelfUsage(
            $shelf,
            $item->unit_weight * $quantityToStore,
            $item->unit->storageDimension->length * $quantityToStore
        );

        return [
            'stored_quantity' => $quantityToStore,
            'remaining_quantity_in_batch' => $remainingQuantityForBatch - $quantityToStore
        ];
    }

    public function calculateMaxQuantity($item, $shelf)
    {
        if ($item->unit_weight <= 0) {
            throw new InvalidArgumentException('Item weight per unit must be positive.');
        }
        if (!isset($item->unit->storageDimension) || $item->unit->storageDimension->length <= 0) {
            throw new InvalidArgumentException('Unit length from storage dimension must be positive.');
        }

        $availableWeight = $shelf->max_weight - $shelf->current_weight;
        $availableLength = $shelf->max_length - $shelf->current_length;

        $maxByWeight = floor($availableWeight / $item->unit_weight);
        $maxByLength = floor($availableLength / $item->unit->storageDimension->length);

//        return max(0, floor(min($maxByWeight, $maxByLength)));
        return  floor(min($maxByWeight, $maxByLength));

    }

    public function getSingleShelfCapacity($purchaseReceiptItemId, $shelfId)
    {
        $item = $this->repo->findPurchaseReceiptItem($purchaseReceiptItemId);
        $shelf = $this->repo->findShelf($shelfId);
        $maxQuantityOnShelf = $this->calculateMaxQuantity($item, $shelf);
        $details = $this->repo->getPurchaseReceiptItemDetails($purchaseReceiptItemId);
        $remainingQuantity = $details['remaining_quantity'];

        return ['can_store_quantity' => min($maxQuantityOnShelf, $remainingQuantity)];
    }

    public function getShelfStatusesForCabinet($purchaseReceiptItemId, $cabinetId)
    {
        $allShelves = $this->repo->getAllShelvesInCabinet($cabinetId);
        if ($allShelves->isEmpty()) {
            return collect();
        }

        $itemDetails = $this->repo->getPurchaseReceiptItemDetails($purchaseReceiptItemId);
        $item = $itemDetails['item_details'];
        $remainingQuantity = $itemDetails['remaining_quantity'];

        $shelfIds = $allShelves->pluck('id');
        $storedItemsByShelf = $this->repo->getStoredLocationsOnShelves($shelfIds)
            ->groupBy('shelf_id');

        return $allShelves->map(function ($shelf) use ($item, $remainingQuantity, $storedItemsByShelf) {
            $maxCapacityOnShelf = $this->calculateMaxQuantity($item, $shelf);
            $storableQuantity = min($maxCapacityOnShelf, $remainingQuantity);
            $isStorable = $storableQuantity > 0;
            $storedItemsOnThisShelf = isset($storedItemsByShelf[$shelf->id]) ? $storedItemsByShelf[$shelf->id] : collect();
            $storedItemsList = $this->formatStoredItemsList($storedItemsOnThisShelf);

            return [
                'shelf_id' => $shelf->id,
                'code' => $shelf->code,
                'is_storable_for_this_item' => $isStorable,
                'storable_quantity_for_this_item' => $storableQuantity,
                'status_message' => $isStorable ? 'Storage is possible' : 'No capacity for this item on this shelf.',
                'current_weight' => $shelf->current_weight,
                'max_weight' => $shelf->max_weight,
                'weight_usage_percentage' => $shelf->weight_usage_percentage,
                'current_length' => $shelf->current_length,
                'max_length' => $shelf->max_length,
                'length_usage_percentage' => $shelf->length_usage_percentage,
                'levels' => $shelf->levels,
                'stored_items' => $storedItemsList,
            ];
        });
    }

    protected function formatStoredItemsList($locations)
    {
        return $locations->map(function ($location) {
            if (!isset($location->purchaseReceiptItem) || !$location->purchaseReceiptItem->item) {
                return null;
            }
            return [
                'item_id' => $location->purchaseReceiptItem->item->id,
                'item_name' => $location->purchaseReceiptItem->item->name,
            ];
        })->filter()->unique('item_id')->values();
    }

    public function getCabinetShelvesSummary($cabinetId)
    {
        return $this->repo->getAllShelvesInCabinet($cabinetId)->map(function ($shelf) {
            return [
                'id' => $shelf->id,
                'code' => $shelf->code,
                'current_weight' => $shelf->current_weight,
                'max_weight' => $shelf->max_weight,
                'weight_usage_percentage' => $shelf->weight_usage_percentage,
                'current_length' => $shelf->current_length,
                'max_length' => $shelf->max_length,
                'length_usage_percentage' => $shelf->length_usage_percentage,
            ];
        });
    }

    public function getDetailedShelfInfo($shelfId)
    {
        $shelf = $this->repo->findShelf($shelfId);
        $storedLocations = $this->repo->getStoredLocationsOnShelf($shelfId);

        $items = $storedLocations->map(function ($location) {
            if (!isset($location->purchaseReceiptItem) || !$location->purchaseReceiptItem->item) {
                return null;
            }
            return [
                'item_id' => $location->purchaseReceiptItem->item->id,
                'item_name' => $location->purchaseReceiptItem->item->name,
                'item_code' => $location->purchaseReceiptItem->item->code,
                'unit_name' => $location->purchaseReceiptItem->unit->name,
                'stored_quantity' => $location->quantity,
                'purchase_receipt_item_id' => $location->purchase_receipt_items_id
            ];
        })->filter()->values();

        return [
            'shelf_info' => [
                'id' => $shelf->id,
                'code' => $shelf->code,
                'max_weight' => $shelf->max_weight,
                'max_length' => $shelf->max_length,
            ],
            'stored_items' => $items
        ];
    }

    // TODO
    public function suggestZonesForItem($itemId)
    {
        return $this->repo->getSuggestedZones($itemId);
    }

    public function suggestCabinetsForItem($itemId, $zoneId, $unitId)
    {
        return $this->repo->getSuggestedCabinets($itemId, $zoneId, $unitId);
    }

    public function storeItemRecursively($purchaseReceiptItemId, $zoneId, $unitId)
    {
        $itemDetails = $this->repo->getPurchaseReceiptItemDetails($purchaseReceiptItemId);
        $remainingQuantity = $itemDetails['remaining_quantity'];
        $storageLog = [];

        while ($remainingQuantity > 0) {
            $cabinets = $this->suggestCabinetsForItem($itemDetails['item_details']->item_id, $zoneId, $unitId);
            if ($cabinets->isEmpty()) {
                throw new Exception('No suitable cabinets found to continue automatic storage.');
            }

            $storedInCycle = false;
            foreach ($cabinets as $cabinet) {
                $shelves = $this->suggestShelvesForItem($itemDetails['item_details']->item_id, $cabinet->id, $unitId);
                if ($shelves->isEmpty()) {
                    continue;
                }

                $shelfToUse = $shelves->first();
                $result = $this->storeItemOnShelf($purchaseReceiptItemId, $shelfToUse->id, $remainingQuantity);

                $storageLog[] = [
                    'cabinet_id' => $cabinet->id,
                    'shelf_id' => $shelfToUse->id,
                    'stored_quantity' => $result['stored_quantity']
                ];
                $remainingQuantity = $result['remaining_quantity_in_batch'];
                $storedInCycle = true;

                if ($remainingQuantity <= 0) {
                    break;
                }
            }

            if (!$storedInCycle) {
                throw new Exception('Could not store any more items. No suitable shelves with capacity found.');
            }
        }
        return $storageLog;
    }
}
