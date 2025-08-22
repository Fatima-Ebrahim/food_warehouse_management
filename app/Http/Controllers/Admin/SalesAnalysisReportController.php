<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\PeriodReportsRequest;
use App\Services\ReportsService\SalesAnalysisReportService;

class SalesAnalysisReportController extends Controller
{
    public function __construct(protected SalesAnalysisReportService $service)
    {}

    public function compareSales(PeriodReportsRequest $request)
    {
        $validated=$request->validated();
        $data = $this->service->compareSales(
            $validated['from'],
            $validated['to']
        );

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function getInventoryTurnover(PeriodReportsRequest $request)
    {
        $validated=$request->validated();
        $data = $this->service->getInventoryTurnover(
            $validated['from'],
            $validated['to']
        );

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function getOffersImpactWithIncrease(PeriodReportsRequest $request)
    {
        $validated=$request->validated();
        $data = $this->service->getOffersImpactWithIncrease(
            $validated['daysBefore']?? 7,
            $validated['daysAfter']?? 7
        );

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }


}
