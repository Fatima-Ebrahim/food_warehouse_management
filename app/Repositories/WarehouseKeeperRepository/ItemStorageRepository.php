<?php

namespace App\Repositories\WarehouseKeeperRepository;

use App\Models\BatchStorageLocation;
use App\Models\Cabinet;
use App\Models\Item;
use App\Models\PurchaseReceiptItem;
use App\Models\Shelf;
use App\Models\StorageDimension;
use App\Models\WarehouseCoordinate;
use App\Models\Zone;
use Illuminate\Support\Facades\DB;

class ItemStorageRepository
{
    public function getPurchaseReceiptItemDetails($purchaseReceiptItemId)
    {
        $item = $this->findPurchaseReceiptItem($purchaseReceiptItemId);
        $storedQuantity = BatchStorageLocation::where('purchase_receipt_items_id', $purchaseReceiptItemId)->sum('quantity');
        $remainingQuantity = $item->quantity - $storedQuantity;

        return [
            'item_details' => $item,
            'remaining_quantity' => max(0, $remainingQuantity),
            'stored_quantity' => $storedQuantity
        ];
    }

    public function findPurchaseReceiptItem($id)
    {
        return PurchaseReceiptItem::with('item', 'unit.storageDimension')->findOrFail($id);
    }

//    public function getSuggestedZonesWithCabinets($itemId)
//    {
//        $suggestedZones = $this->getSuggestedZones($itemId);
//        $zoneIds = $suggestedZones->pluck('id');
//
//        $cabinetsInZones = WarehouseCoordinate::whereIn('zone_id', $zoneIds)
//            ->whereNotNull('cabinet_id')
//            ->get(['zone_id', 'cabinet_id'])
//            ->groupBy('zone_id')
//            ->map(function ($group) {
//                return $group->pluck('cabinet_id')->unique();
//            });
//
//        $allCabinetIds = $cabinetsInZones->flatten()->unique();
//        $cabinets = Cabinet::with('coordinates')->whereIn('id', $allCabinetIds)->get()->keyBy('id');
//
//        return $suggestedZones->map(function ($zone) use ($cabinetsInZones, $cabinets) {
//            $cabinetIdsForZone = isset($cabinetsInZones[$zone->id]) ? $cabinetsInZones[$zone->id] : collect();
//            $zone->cabinets = $cabinets->whereIn('id', $cabinetIdsForZone)->values();
//            return $zone;
//        });
//    }
    public function getSuggestedZonesWithCabinets($itemId)
    {
        $suggestedZones = $this->getSuggestedZones($itemId);
        if ($suggestedZones->isEmpty()) {
            return collect();
        }
        $zoneIds = $suggestedZones->pluck('id');

        $coordinatesInZones = WarehouseCoordinate::with('cabinet')
            ->whereIn('zone_id', $zoneIds)
            ->get();

        $groupedCoordinates = $coordinatesInZones->groupBy('zone_id');

        return $suggestedZones->map(function ($zone) use ($groupedCoordinates) {
            $zoneCoordinates = $groupedCoordinates->get($zone->id, collect());

            $cabinets = $zoneCoordinates
                ->whereNotNull('cabinet')
                ->pluck('cabinet')
                ->unique('id')
                ->values();

            $zone->cabinets = $cabinets;
            $zone->coordinates =  $zoneCoordinates->map(function ($coordinate) {
                return [
                    'x' => $coordinate->x,
                    'y' => $coordinate->y,
                    'z' => $coordinate->z,
                ];
            });

            return $zone;
        });
    }
    public function getSuggestedShelves($itemId, $cabinetId, $unitId)
    {
        $item = Item::findOrFail($itemId);
        $storageDimension = StorageDimension::where('unit_id', $unitId)->first();

        if (!$storageDimension || $storageDimension->length <= 0 || $item->unit_weight <= 0) {
            return collect();
        }

        return Shelf::query()
            ->where('cabinet_id', $cabinetId)
            ->whereRaw('max_weight - current_weight >= ?', [$item->unit_weight])
            ->whereRaw('max_length - current_length >= ?', [$storageDimension->length])
            ->orderBy('current_weight', 'asc')
            ->get();
    }

    public function createOrUpdateStorageLocation($purchaseReceiptItemId, $shelfId, $quantity)
    {
        return BatchStorageLocation::updateOrCreate(
            ['purchase_receipt_items_id' => $purchaseReceiptItemId, 'shelf_id' => $shelfId],
            ['quantity' => DB::raw("quantity + $quantity")]
        );
    }

    public function incrementShelfUsage($shelf, $weight, $length)
    {
        $shelf->increment('current_weight', $weight);
        $shelf->increment('current_length', $length);
    }

    public function findShelf($id)
    {
        return Shelf::findOrFail($id);
    }

    public function getAllShelvesInCabinet($cabinetId)
    {
        return Shelf::where('cabinet_id', $cabinetId)->get();
    }

    public function getStoredLocationsOnShelf($shelfId)
    {
        return BatchStorageLocation::where('shelf_id', $shelfId)
            ->with([
                'purchaseReceiptItem.item:id,name,code',
                'purchaseReceiptItem.unit:id,name'
            ])
            ->get();
    }


    public function getStoredLocationsOnShelves( $shelfIds)
    {
        if (empty($shelfIds)) {
            return collect();
        }

        return BatchStorageLocation::whereIn('shelf_id', $shelfIds)
            ->with(['purchaseReceiptItem.item:id,name,code', 'purchaseReceiptItem.unit:id,name'])
            ->get();
    }


    public function getSuggestedZones($itemId)
    {
        $item = Item::findOrFail($itemId);
        $conditions = $item->storage_conditions;

        return Zone::query()
            ->where('type', 'storage')
            ->when(isset($conditions['temperature']), function ($q) use ($conditions) {
                $temp = $conditions['temperature'];
                $minTemp = isset($temp['min']) ? $temp['min'] : 0;
                $maxTemp = isset($temp['max']) ? $temp['max'] : 25;
                $q->where(function ($tempQuery) use ($minTemp) {
                    $tempQuery->where('min_temperature', '<=', $minTemp)
                        ->orWhereNull('min_temperature');
                })->where(function ($tempQuery) use ($maxTemp) {
                    $tempQuery->where('max_temperature', '>=', $maxTemp)
                        ->orWhereNull('max_temperature');
                });
            })
            ->when(isset($conditions['humidity']), function ($q) use ($conditions) {
                $humidity = $conditions['humidity'];
                $minHumidity = isset($humidity['min']) ? $humidity['min'] : 0;
                $maxHumidity = isset($humidity['max']) ? $humidity['max'] : 100;
                $q->where(function ($humidityQuery) use ($minHumidity) {
                    $humidityQuery->where('humidity_min', '<=', $minHumidity)
                        ->orWhereNull('humidity_min');
                })->where(function ($humidityQuery) use ($maxHumidity) {
                    $humidityQuery->where('humidity_max', '>=', $maxHumidity)
                        ->orWhereNull('humidity_max');
                });
            })
            ->when(isset($conditions['environment']), function ($q) use ($conditions) {
                $env = $conditions['environment'];
                $q->where('is_ventilated', isset($env['requires_ventilation']) ? $env['requires_ventilation'] : false)
                    ->where('is_shaded', isset($env['requires_shade']) ? $env['requires_shade'] : false)
                    ->where('is_dark', isset($env['requires_darkness']) ? $env['requires_darkness'] : false);
            })
            ->orderByRaw('CASE WHEN min_temperature IS NOT NULL AND max_temperature IS NOT NULL THEN 1 ELSE 2 END')
            ->get();
    }
// TODO
    public function getSuggestedCabinets($itemId, $zoneId, $unitId)
    {
        $cabinetIds = WarehouseCoordinate::where('zone_id', $zoneId)
            ->whereNotNull('cabinet_id')
            ->pluck('cabinet_id')
            ->unique();

        return Cabinet::whereIn('id', $cabinetIds)->get();
    }
}
