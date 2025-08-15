<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminRequests\WarehouseDesignRequests\AssignZoneToCoordinateRequest;
use App\Http\Requests\AdminRequests\WarehouseDesignRequests\StoreCabinetRequest;
use App\Http\Requests\AdminRequests\WarehouseDesignRequests\StoreCabinetWithShelvesRequest;
use App\Http\Requests\AdminRequests\WarehouseDesignRequests\StoreCoordinateRequest;
use App\Http\Requests\AdminRequests\WarehouseDesignRequests\StoreShelfRequest;
use App\Http\Requests\AdminRequests\WarehouseDesignRequests\StoreZoneRequest;
use App\Http\Requests\AdminRequests\WarehouseDesignRequests\UpdateCabinetRequest;
use App\Http\Requests\AdminRequests\WarehouseDesignRequests\UpdateZoneRequest;
use App\Http\Resources\WarehouseDesignResources\CabinetResource;
use App\Http\Resources\WarehouseDesignResources\ShelfResource;
use App\Services\AdminServices\WarehouseDesignService;
use App\Settings\DesignSettings;

class WarehouseDesignController extends Controller
{
    protected $warehouseDesignService;

    public function __construct(WarehouseDesignService $warehouseDesignService)
    {
        $this->warehouseDesignService = $warehouseDesignService;
    }

    public function storeCoordinate(StoreCoordinateRequest $request)
    {
        $coordinates = $this->warehouseDesignService->createCoordinates($request->validated());
        return response()->json([
            'success' => true,
            'data' => $coordinates,
            'message' => 'Coordinates created successfully'
        ], 201);
    }

    public function indexCoordinate()
    {
        $coordinates = $this->warehouseDesignService->getAllCoordinates();
        return response()->json([
            'success' => true,
            'data' => $coordinates,
            'message' => $coordinates->isEmpty() ? 'No coordinates found' : 'Coordinates retrieved successfully'
        ]);
    }

    public function storeZone(StoreZoneRequest $request)
    {
        $zone = $this->warehouseDesignService->createZoneWithCoordinates($request->validated());
        return response()->json(['message' => 'Zone created successfully', 'data' => $zone], 201);
    }

    public function updateZone(UpdateZoneRequest $request, $id)
    {
        $zone = $this->warehouseDesignService->updateZoneWithCoordinates($id, $request->validated());
        return response()->json(['message' => 'Zone updated successfully', 'data' => $zone]);
    }

    public function indexZones()
    {
        return $this->warehouseDesignService->getAllZones();
    }

    public function getZoneWithCoordinates($id)
    {
        $zone = $this->warehouseDesignService->getZoneWithCoordinates($id);
        return response()->json(['data' => $zone]);
    }

    public function deleteZone($id)
    {
        $this->warehouseDesignService->deleteZone($id);
        return response()->json(['message' => 'Zone deleted successfully']);
    }

    public function storeCabinetWithShelves(StoreCabinetWithShelvesRequest $request)
    {
        $cabinet = $this->warehouseDesignService->createCabinetWithShelves($request->validated());
        return response()->json(['success' => true, 'message' => 'Cabinet and shelves created successfully', 'data' => $cabinet], 201);
    }

    public function indexCabinets()
    {
        return $this->warehouseDesignService->getAllCabinets();
    }

    public function getCabinetWithCoordinates($id)
    {
        $cabinet = $this->warehouseDesignService->getCabinetWithCoordinates($id);
        return response()->json(['data' => $cabinet]);
    }

    public function updateCabinet(UpdateCabinetRequest $request, $id)
    {
        $cabinet = $this->warehouseDesignService->updateCabinetWithCoordinates($id, $request->validated());
        return response()->json(['message' => 'Cabinet updated successfully', 'data' => $cabinet]);
    }

    public function deleteCabinet($id)
    {
        $this->warehouseDesignService->deleteCabinet($id);
        return response()->json(['message' => 'Cabinet deleted successfully']);
    }

    public function setComplete()
    {
        $this->warehouseDesignService->setComplete();
        return response()->json(['message' => 'Design marked as complete']);
    }

    public function getStatus()
    {
        return response()->json([
            'complete' =>  $this->warehouseDesignService->getComplete()
        ]);
    }
    // TODO
    public function assignZone(AssignZoneToCoordinateRequest $request, $id)
    {
        $coordinate = $this->warehouseDesignService->assignZone($id, $request->zone_id);
        return response()->json($coordinate);
    }

    public function showZone($id)
    {
        $zone = $this->warehouseDesignService->getZoneById($id);
        return response()->json(['success' => true, 'data' => $zone, 'message' => 'Zone retrieved successfully']);
    }

    public function showCabinet($id)
    {
        $cabinet = $this->warehouseDesignService->getCabinetById($id);
        return new CabinetResource($cabinet);
    }

    public function storeCabinet(StoreCabinetRequest $request)
    {
        $cabinet = $this->warehouseDesignService->createCabinetWithCoordinates($request->validated());
        return response()->json(['message' => 'Cabinet created successfully', 'data' => $cabinet], 201);
    }

    public function storeShelf(StoreShelfRequest $request)
    {
        $shelf = $this->warehouseDesignService->createShelf($request->validated());
        return response()->json($shelf, 201);
    }

    public function showShelf($id)
    {
        $shelf = $this->warehouseDesignService->getShelfById($id);
        return new ShelfResource($shelf);
    }
}
