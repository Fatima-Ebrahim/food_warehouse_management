<?php

namespace App\Http\Controllers\Admin;


use App\Http\Requests\Reports\BatchesStatusRequest;
use App\Http\Requests\Reports\MovementsRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\PeriodReportsRequest;
use App\Services\ReportsService\InventoryReportService;

class InventoryReportController extends Controller
{
    public function __construct(private InventoryReportService $service) {}


    public function currentStock()
    {
        $data = $this->service->currentStock();

        return response()->json(['status'=>true,'data'=>$data]);
    }

    public function lowStock()
    {
        $lowStockItems = $this->service->lowStock();

        return response()->json(['data' => $lowStockItems]);
    }


    public function getStockMovements(MovementsRequest $request)
    {
        $data = $this->service->getStockMovements($request->validated());
        return response()->json(['success' => true, 'data' => $data->items()]);
    }


    public function batchesStatus(BatchesStatusRequest $request)
    {
        $filters = $request->validated();
        $data = $this->service->batchesStatus($filters);
        return response()->json(['success' => true, 'data' => $data]);
    }

    public function topMoving(PeriodReportsRequest $request)
    {
        $filters = $request->validated();
        $data = $this->service->getTopMoving($filters);

        return response()->json([
            'status' => true,
            'data' => $data,
        ]);
    }


    public function slowMoving(PeriodReportsRequest $request)
    {
        $filters = $request->validated();
        $data = $this->service->getSlowMoving($filters);

        return response()->json([
            'status' => true,
            'data' => $data,
        ]);
    }
}
