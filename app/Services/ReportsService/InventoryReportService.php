<?php
namespace App\Services\ReportsService;


use App\Repositories\ReportsRepository\InventoryReportRepository;
use Carbon\Carbon;

class InventoryReportService
{
    public function __construct(private InventoryReportRepository $repo) {}

    public function currentStock()
    {
        return  $this->repo->getCurrentStock();

    }

    public function lowStock()
    {
        return $this->repo->getLowStockItems();
    }

    public function getStockMovements(array $filters)
    {
        $from = $filters['from'];
        $to = $filters['to'];
        $type = $filters['type'] ?? 'all';
        $perPage = (int) ($filters['per_page'] ?? 100);
        $itemId = $filters['item_id'] ?? null;

        return $this->repo->getMovements($from, $to, $type, $perPage, $itemId);
    }

//
//    public function getNetMovementForItem(int $itemId, string $from, string $to): float
//    {
//        return $this->repo->getNetMovementForItem($itemId, $from, $to);
//    }

    public function batchesStatus(array $filters)
    {
        $status = $filters['status'] ?? 'all';
        $asOf = $filters['as_of'] ?? now()->toDateString();
        $perPage = $filters['per_page'] ?? 50;

        return $this->repo->getBatchesStatus($status, $asOf, $perPage);
    }

    public function getTopMoving(array $filters)
    {
        return $this->repo->getTopMovingItems($filters);
    }

    public function getSlowMoving(array $filters)
    {
        return $this->repo->getSlowMovingItems($filters);
    }
}
