<?php

namespace App\Http\Controllers\WarehouseKeeper;

use App\Http\Controllers\Controller;
use App\Http\Requests\WarehouseKeeperRequests\ItemStorageRequests\GetShelfCapacityRequest;
use App\Http\Requests\WarehouseKeeperRequests\ItemStorageRequests\GetShelfStatusesRequest;
use App\Http\Requests\WarehouseKeeperRequests\ItemStorageRequests\StoreItemAutoRequest;
use App\Http\Requests\WarehouseKeeperRequests\ItemStorageRequests\StoreItemRequest;
use App\Http\Requests\WarehouseKeeperRequests\ItemStorageRequests\SuggestCabinetsRequest;
use App\Http\Requests\WarehouseKeeperRequests\ItemStorageRequests\SuggestShelvesRequest;
use App\Services\WarehouseKeeperService\ItemStorageService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ItemStorageController extends Controller
{
    protected $service;

    public function __construct(ItemStorageService $service)
    {
        $this->service = $service;
    }

    public function getItemDetails($purchaseReceiptItemId)
    {
        try {
            $details = $this->service->getItemDetailsForStorage($purchaseReceiptItemId);
            return response()->json($details);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Item not found.'], 404);
        }
    }

    public function suggestedZonesWithCabinets($itemId)
    {
        return response()->json($this->service->suggestZonesWithCabinetsForItem($itemId));
    }

    public function suggestedShelves(SuggestShelvesRequest $request, $itemId)
    {
        $validated = $request->validated();
        return response()->json($this->service->suggestShelvesForItem(
            $itemId,
            $validated['cabinet_id'],
            $validated['unit_id']
        ));
    }

    public function storeItem(StoreItemRequest $request)
    {
        try {
            $result = $this->service->storeItemOnShelf(
                $request->validated()['purchase_receipt_item_id'],
                $request->validated()['shelf_id'],
                $request->validated()['quantity']
            );
            return response()->json(['success' => true, 'data' => $result]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Storage process failed: ' . $e->getMessage()], 400);
        }
    }

    public function getShelfCapacity(GetShelfCapacityRequest $request)
    {
        try {
            $capacity = $this->service->getSingleShelfCapacity(
                $request->validated()['purchase_receipt_item_id'],
                $request->validated()['shelf_id']
            );
            return response()->json(['success' => true, 'data' => $capacity]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to calculate capacity: ' . $e->getMessage(), 'data' => ['can_store_quantity' => 0]], 400);
        }
    }

    public function getShelfStatuses(GetShelfStatusesRequest $request, $purchaseReceiptItemId)
    {
        $statuses = $this->service->getShelfStatusesForCabinet(
            $purchaseReceiptItemId,
            $request->validated()['cabinet_id']
        );
        return response()->json($statuses);
    }

    public function getCabinetSummary($cabinetId)
    {
        $summary = $this->service->getCabinetShelvesSummary($cabinetId);
        return response()->json($summary);
    }

    public function getShelfDetails($shelfId)
    {
        $details = $this->service->getDetailedShelfInfo($shelfId);
        return response()->json($details);
    }

    // TODO
    public function suggestedZones($itemId)
    {
        return response()->json($this->service->suggestZonesForItem($itemId));
    }

    public function suggestedCabinets(SuggestCabinetsRequest $request, $itemId)
    {
        $validated = $request->validated();
        return response()->json($this->service->suggestCabinetsForItem(
            $itemId,
            $validated['zone_id'],
            $validated['unit_id']
        ));
    }

    public function storeItemAuto(StoreItemAutoRequest $request)
    {
        try {
            $log = $this->service->storeItemRecursively(
                $request->validated()['purchase_receipt_item_id'],
                $request->validated()['zone_id'],
                $request->validated()['unit_id']
            );
            return response()->json(['success' => true, 'message' => 'Auto-storage process completed.', 'data' => $log]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
}
