<?php

namespace App\Services\WarehouseKeeperService;

use App\Notifications\StocktakeCompletedNotification;
use App\Repositories\WarehouseKeeperRepository\InventoryRepository;
use App\Models\User;
use App\Notifications\StocktakeRequestNotification;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Notification;

class InventoryService
{
    protected $inventoryRepository;

    public function __construct(InventoryRepository $inventoryRepository)
    {
        $this->inventoryRepository = $inventoryRepository;
    }

    public function createStocktakeRequest(array $data)
    {
        $requestData = [
            'type' => $data['type'],
            'status' => 'pending',
            'notes' => isset($data['notes']) ? $data['notes'] : null,
        ];

        if ($data['type'] === 'scheduled') {
            $requestData['schedule_frequency'] = $data['schedule_frequency'];
            $requestData['schedule_interval'] = $data['schedule_interval'];
            $requestData['scheduled_at'] = Carbon::now()->add(
                $data['schedule_frequency'],
                $data['schedule_interval']
            );
        }

        $stocktake = $this->inventoryRepository->createStocktake($requestData);

        if ($stocktake->type === 'immediate') {

            $this->notifyWarehouseKeepers($stocktake);

        }

        return $stocktake;
    }

    /*public function processStocktakeSubmission( $stocktakeId, array $stocktakeData)
    {
        $stocktake = $this->inventoryRepository->findStocktake($stocktakeId);
        if (!$stocktake || $stocktake->status !== 'pending') {
            throw new Exception('Stocktake request is not valid or has already been processed.');
        }

        $this->inventoryRepository->update($stocktakeId, ['status' => 'in_progress']);

        $discrepancies = [];
        $itemIds = array_column($stocktakeData, 'item_id');
        $itemsDetails = $this->inventoryRepository->getItemsDetails($itemIds);

        foreach ($stocktakeData as $countedItem) {
            $itemId = $countedItem['item_id'];
            $countedQuantity = (float) $countedItem['counted_quantity'];
            $expectedQuantity = $this->inventoryRepository->getExpectedQuantityForItem($itemId);

            if (abs($expectedQuantity - $countedQuantity) > 0.001) {
                $itemDetail = $itemsDetails->get($itemId);
                $discrepancies[] = [
                    'item_id' => $itemId,
                    'item_name' => isset($itemDetail->name) ? $itemDetail->name : 'Unknown',
                    'expected_quantity' => $expectedQuantity,
                    'counted_quantity' => $countedQuantity,
                    'discrepancy' => $countedQuantity - $expectedQuantity,
                ];
            }
        }

        $this->inventoryRepository->saveStocktakeDiscrepancies($stocktakeId, $discrepancies);
        $this->inventoryRepository->update($stocktakeId, ['status' => 'completed', 'completed_at' => now()]);
        $this->notifyManagers($stocktake);

        return $discrepancies;
    }*/

    public function processStocktakeSubmission($stocktakeId, array $stocktakeData)
    {
        $stocktake = $this->inventoryRepository->findStocktake($stocktakeId);
        if (!$stocktake || $stocktake->status !== 'pending') {
            throw new Exception('Stocktake request is not valid or has already been processed.');
        }

        $this->inventoryRepository->update($stocktakeId, ['status' => 'in_progress']);

        $discrepancies = [];
        $itemIds = array_column($stocktakeData, 'item_id');
        $itemsDetails = $this->inventoryRepository->getItemsDetails($itemIds);

        foreach ($stocktakeData as $countedItem) {
            $itemId = $countedItem['item_id'];
            $countedQuantity = (float)$countedItem['counted_quantity'];
            $unitId = $countedItem['unit_id'] ?? null;

            $expectedQuantity = $this->inventoryRepository->getExpectedQuantityForItem($itemId, $unitId);

            if (abs($expectedQuantity - $countedQuantity) > 0.001) {
                $itemDetail = $itemsDetails->get($itemId);
                $discrepancies[] = [
                    'item_id' => $itemId,
                    'item_name' => isset($itemDetail->name) ? $itemDetail->name : 'Unknown',
                    'expected_quantity' => $expectedQuantity,
                    'counted_quantity' => $countedQuantity,
                    'discrepancy' => $countedQuantity - $expectedQuantity,
                ];
            }
        }

        $this->inventoryRepository->saveStocktakeDiscrepancies($stocktakeId, $discrepancies);
        $this->inventoryRepository->update($stocktakeId, ['status' => 'completed', 'completed_at' => now()]);
        $this->notifyManagers($stocktake);

        return $discrepancies;
    }

    protected function notifyWarehouseKeepers($stocktake)
    {
        $keepers = User::query()->where('user_type', 'warehouse_keeper')->get();
        foreach ($keepers as $keeper) {
//            $keeper->notify(new StocktakeRequestNotification($stocktake));
            Notification::send($keeper,new StocktakeRequestNotification($stocktake));
        }
    }

    protected function notifyManagers($stocktake)
    {
        $managers = User::query()->where('user_type', 'admin')->get();
        foreach ($managers as $manager) {
            $manager->notify(new StocktakeCompletedNotification($stocktake));
        }
    }

    public function getReports($status = null)
    {
        return $this->inventoryRepository->getReports($status);
    }

    public function getReportDetails( $id)
    {
        return $this->inventoryRepository->getReportDetails($id);
    }
    public function getScheduledStocktakes()
    {
        return $this->inventoryRepository->getScheduledStocktakes();
    }
    public function updateScheduledStocktake( $id, array $data)
    {
        $stocktake = $this->inventoryRepository->findStocktake($id);

        if (!$stocktake || $stocktake->type !== 'scheduled') {
            throw new ModelNotFoundException('Scheduled stocktake request not found.');
        }

        if (isset($data['schedule_frequency']) || isset($data['schedule_erval'])) {
            $frequency = isset($data['schedule_frequency']) ? $data['schedule_frequency'] : $stocktake->schedule_frequency;
            $erval = isset($data['schedule_erval']) ? $data['schedule_erval'] : $stocktake->schedule_erval;
            $data['scheduled_at'] = Carbon::now()->add($frequency, $erval);
        }

        return $this->inventoryRepository->update($id, $data);
    }

    public function cancelScheduledStocktake( $id)
    {
        $stocktake = $this->inventoryRepository->findStocktake($id);
        if (!$stocktake || $stocktake->type !== 'scheduled') {
            throw new ModelNotFoundException('Scheduled stocktake request not found.');
        }

        return $this->inventoryRepository->update($id, ['is_active' => false, 'status' => 'cancelled']);
    }
}
