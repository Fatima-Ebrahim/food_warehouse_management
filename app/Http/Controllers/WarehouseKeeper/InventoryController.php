<?php

namespace App\Http\Controllers\WarehouseKeeper;

use App\Http\Controllers\Controller;
use App\Http\Requests\WarehouseKeeperRequests\InventoryRequests\StoreStocktakeRequest;
use App\Http\Requests\WarehouseKeeperRequests\InventoryRequests\SubmitStocktakeRequest;
use App\Services\WarehouseKeeperService\InventoryService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Exception;

class InventoryController extends Controller
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function requestStocktake(StoreStocktakeRequest $request)
    {
        try {
            $stocktake = $this->inventoryService->createStocktakeRequest($request->validated());
            return response()->json(['success' => true, 'message' => 'Stocktake request created successfully.', 'data' => $stocktake], 201);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to create request: ' . $e->getMessage()], 400);
        }
    }

    public function submitStocktake(SubmitStocktakeRequest $request, int $stocktakeId)
    {
        try {
            $discrepancies = $this->inventoryService->processStocktakeSubmission($stocktakeId, $request->validated()['items']);
            return response()->json([
                'success' => true,
                'data' => $discrepancies,
                'message' => 'Stocktake processed successfully. Found ' . count($discrepancies) . ' discrepancies.'
            ], 200);
        } catch(Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to process stocktake: ' . $e->getMessage()], 400);
        }
    }

    public function getStocktakeReports()
    {
        $reports = $this->inventoryService->getReports();
        return response()->json(['success' => true, 'data' => $reports]);
    }

    public function getStocktakeReportDetails(int $id)
    {
        try {
            $details = $this->inventoryService->getReportDetails($id);
            return response()->json(['success' => true, 'data' => $details]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Report not found.'], 404);
        }
    }

    public function updateScheduledStocktake(Request $request, int $id)
    {
        $validatedData = $request->validate([
            'notes' => 'sometimes|nullable|string',
            'schedule_frequency' => 'sometimes|required|in:days,weeks,months,years',
            'schedule_interval' => 'sometimes|required|integer|min:1',
        ]);

        try {
            $stocktake = $this->inventoryService->updateScheduledStocktake($id, $validatedData);
            return response()->json(['success' => true, 'message' => 'Scheduled stocktake updated successfully.', 'data' => $stocktake]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update scheduled stocktake: ' . $e->getMessage()], 400);
        }
    }

    public function cancelScheduledStocktake( $id)
    {
        try {
            $this->inventoryService->cancelScheduledStocktake($id);
            return response()->json(['success' => true, 'message' => 'Scheduled stocktake cancelled successfully.']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }
}
