<?php

namespace App\Services\AdminServices;

use App\Http\Resources\WarehouseDesignResources\CabinetResource;
use App\Http\Resources\WarehouseDesignResources\ShelfResource;
use App\Http\Resources\WarehouseDesignResources\WarehouseCoordinateResource;
use App\Http\Resources\WarehouseDesignResources\ZoneResource;
use App\Repositories\AdminRepository\WarehouseDesignRepository;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class WarehouseDesignService
{
    protected $warehouseDesignRepository;

    public function __construct(WarehouseDesignRepository $warehouseDesignRepository)
    {
        $this->warehouseDesignRepository = $warehouseDesignRepository;
    }

    public function createCoordinates($data)
    {
        $createdCoordinates = [];
        foreach ($data['coordinates'] as $coordinateData) {
            $coordinate = $this->warehouseDesignRepository->create($coordinateData);
            $createdCoordinates[] = new WarehouseCoordinateResource($coordinate);
        }
        return $createdCoordinates;
    }

    public function getAllCoordinates()
    {
        return $this->warehouseDesignRepository->all();
    }

    public function createZoneWithCoordinates($data)
    {
        return DB::transaction(function () use ($data) {
            $zoneData = Arr::except($data, ['coordinate_ids']);
            $zone = $this->warehouseDesignRepository->createZone($zoneData);
            $this->manageCoordinatesOnCreate('zone', $zone->id, $data);
            return $this->getZoneWithCoordinates($zone->id);
        });
    }

    public function updateZoneWithCoordinates($id, $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $zoneData = Arr::except($data, ['coordinate_ids']);
            $this->warehouseDesignRepository->updateZone($id, $zoneData);
            $this->manageCoordinatesOnUpdate('zone', $id, $data);
            return $this->getZoneWithCoordinates($id);
        });
    }

    public function getAllZones()
    {
        return $this->warehouseDesignRepository->getAllZones();
    }

    public function getZoneWithCoordinates($id)
    {
        return $this->warehouseDesignRepository->getZoneWithCoordinates($id);
    }

    public function deleteZone($id)
    {
        DB::transaction(function () use ($id) {
            $this->warehouseDesignRepository->detachAllCoordinatesFromZone($id);
            $this->warehouseDesignRepository->deleteZone($id);
        });
    }

    public function createCabinetWithShelves($data)
    {
        $cabinet = $this->warehouseDesignRepository->createCabinetWithShelves($data);
        return new CabinetResource($cabinet);
    }

    public function getAllCabinets()
    {
        return $this->warehouseDesignRepository->getAllCabinets();
    }

    public function getCabinetWithCoordinates($id)
    {
        return $this->warehouseDesignRepository->getCabinetWithCoordinates($id);
    }

    public function updateCabinetWithCoordinates($id, $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $cabinetData = Arr::except($data, ['coordinate_ids']);
            $this->warehouseDesignRepository->updateCabinet($id, $cabinetData);
            $this->manageCoordinatesOnUpdate('cabinet', $id, $data);
            return $this->getCabinetWithCoordinates($id);
        });
    }

    public function deleteCabinet($id)
    {
        DB::transaction(function () use ($id) {
            $this->warehouseDesignRepository->detachAllCoordinatesFromCabinet($id);
            $this->warehouseDesignRepository->deleteCabinet($id);
        });
    }
    // TODO
    public function assignZone($coordinateId, $zoneId)
    {
        $coordinate = $this->warehouseDesignRepository->assignZoneToCoordinate($coordinateId, $zoneId);
        return new WarehouseCoordinateResource($coordinate);
    }

    public function getZoneById($id)
    {
        $zone = $this->warehouseDesignRepository->getZoneById($id);
        return new ZoneResource($zone);
    }

    public function getCabinetById($id)
    {
        return $this->warehouseDesignRepository->getCabinetById($id);
    }

    public function createCabinetWithCoordinates($data)
    {
        return DB::transaction(function () use ($data) {
            $cabinetData = Arr::except($data, ['coordinate_ids']);
            $cabinet = $this->warehouseDesignRepository->createCabinet($cabinetData);
            $this->manageCoordinatesOnCreate('cabinet', $cabinet->id, $data);
            return $this->getCabinetWithCoordinates($cabinet->id);
        });
    }

    public function createShelf($data)
    {
        $shelf = $this->warehouseDesignRepository->createShelf($data);
        return new ShelfResource($shelf);
    }

    public function getShelfById($id)
    {
        return $this->warehouseDesignRepository->getShelfById($id);
    }


    private function manageCoordinatesOnCreate(string $entityType, int $entityId, array $data)
    {
        $coordinateIds = $data['coordinate_ids'] ?? [];
        if (!empty($coordinateIds)) {
            $checkMethod = 'checkCoordinatesFor' . ucfirst($entityType);
            if (!$this->warehouseDesignRepository->$checkMethod($coordinateIds)) {
                throw new Exception("Some coordinates are already assigned to another " . $entityType);
            }
            $attachMethod = 'attachCoordinatesTo' . ucfirst($entityType);
            $this->warehouseDesignRepository->$attachMethod($entityId, $coordinateIds);
        }
    }

    private function manageCoordinatesOnUpdate(string $entityType, int $entityId, array $data)
    {
        if (array_key_exists('coordinate_ids', $data)) {
            $coordinateIds = $data['coordinate_ids'] ?? [];
            $detachMethod = 'detachAllCoordinatesFrom' . ucfirst($entityType);
            $this->warehouseDesignRepository->$detachMethod($entityId);

            if (!empty($coordinateIds)) {
                $checkMethod = 'checkCoordinatesFor' . ucfirst($entityType);
                if (!$this->warehouseDesignRepository->$checkMethod($coordinateIds)) {
                    throw new Exception("Some coordinates are already assigned to another " . $entityType);
                }
                $attachMethod = 'attachCoordinatesTo' . ucfirst($entityType);
                $this->warehouseDesignRepository->$attachMethod($entityId, $coordinateIds);
            }
        }
    }
}
