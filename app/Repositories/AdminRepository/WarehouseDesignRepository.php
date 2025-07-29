<?php

namespace App\Repositories\AdminRepository;

use App\Models\Cabinet;
use App\Models\Shelf;
use App\Models\WarehouseCoordinate;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class WarehouseDesignRepository
{
    public function create($data)
    {
        return WarehouseCoordinate::create($data);
    }

    public function all()
    {
        return WarehouseCoordinate::all();
    }

    public function createZone($data)
    {
        return Zone::create($data);
    }

    public function updateZone($id, $data)
    {
        $zone = Zone::findOrFail($id);
        $zone->update($data);
        return $zone;
    }

    public function attachCoordinatesToZone($zoneId, $coordinateIds)
    {
        WarehouseCoordinate::whereIn('id', $coordinateIds)->update(['zone_id' => $zoneId]);
    }

    public function checkCoordinatesForZone($coordinateIds)
    {
        return WarehouseCoordinate::whereIn('id', $coordinateIds)->whereNotNull('zone_id')->doesntExist();
    }

    public function detachAllCoordinatesFromZone($zoneId)
    {
        WarehouseCoordinate::where('zone_id', $zoneId)->update(['zone_id' => null]);
    }

    public function getAllZones()
    {
        $zones = Zone::with('coordinates')->get();
        return $this->transformCoordinatesForCollection($zones);
    }

    public function getZoneWithCoordinates($id)
    {
        $zone = Zone::with('coordinates')->findOrFail($id);
        return ['zone' => $zone->toArray(), 'coordinates' => $zone->coordinates->toArray()];
    }

    public function deleteZone($id)
    {
        Zone::findOrFail($id)->delete();
    }

    public function createCabinetWithShelves($data)
    {
        return DB::transaction(function () use ($data) {
            $cabinetData = [
                'code' => $data['code'] ?? null,
                'width' => $data['width'],
                'length' => $data['length'],
                'height' => $data['height'],
                'shelves_count' => count($data['shelves']),
            ];
            $cabinet = Cabinet::create($cabinetData);

            $shelvesToInsert = [];
            $sharedShelfData = $data['shelf_defaults'];
            foreach ($data['shelves'] as $individualShelfData) {
                $shelvesToInsert[] = array_merge($sharedShelfData, $individualShelfData, [
                    'cabinet_id' => $cabinet->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if (!empty($shelvesToInsert)) {
                Shelf::insert($shelvesToInsert);
            }
            if (!empty($data['coordinate_ids'])) {
                $this->attachCoordinatesToCabinet($cabinet->id, $data['coordinate_ids']);
            }
            return $cabinet->load('shelves', 'coordinates');
        });
    }

    public function getAllCabinets()
    {
        $cabinets = Cabinet::with('coordinates')->get();
        return $this->transformCoordinatesForCollection($cabinets);
    }

    public function getCabinetWithCoordinates($id)
    {
        $cabinet = Cabinet::with('coordinates')->findOrFail($id);
        return ['cabinet' => $cabinet->toArray(), 'coordinates' => $cabinet->coordinates->toArray()];
    }

    public function updateCabinet($id, $data)
    {
        $cabinet = Cabinet::findOrFail($id);
        $cabinet->update($data);
        return $cabinet;
    }

    public function checkCoordinatesForCabinet($coordinateIds)
    {
        return WarehouseCoordinate::whereIn('id', $coordinateIds)->whereNotNull('cabinet_id')->doesntExist();
    }

    public function detachAllCoordinatesFromCabinet($cabinetId)
    {
        WarehouseCoordinate::where('cabinet_id', $cabinetId)->update(['cabinet_id' => null]);
    }

    public function attachCoordinatesToCabinet($cabinetId, $coordinateIds)
    {
        WarehouseCoordinate::whereIn('id', $coordinateIds)->update(['cabinet_id' => $cabinetId]);
    }

    public function deleteCabinet($id)
    {
        Cabinet::findOrFail($id)->delete();
    }
    // TODO
    public function assignZoneToCoordinate($coordinateId, $zoneId)
    {
        $coordinate = WarehouseCoordinate::findOrFail($coordinateId);
        $coordinate->zone_id = $zoneId;
        $coordinate->save();
        return $coordinate;
    }

    public function getZoneById($id)
    {
        return Zone::with('coordinates')->findOrFail($id);
    }

    public function getCabinetById($id)
    {
        return Cabinet::findOrFail($id);
    }

    public function createCabinet($data)
    {
        return Cabinet::create($data);
    }

    public function createShelf($data)
    {
        return Shelf::create($data);
    }

    public function getShelfById($id)
    {
        return Shelf::findOrFail($id);
    }


    private function transformCoordinatesForCollection(Collection $collection)
    {
        return $collection->each(function ($item) {
            if ($item->relationLoaded('coordinates')) {
                $item->coordinates->transform(function ($coordinate) {
                    return (object)['x' => $coordinate->x, 'y' => $coordinate->y, 'z' => $coordinate->z];
                });
            }
        });
    }
}
