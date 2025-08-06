<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminServices\LowStockService;

class LowStockReportController extends Controller
{
    protected $reportService;

    public function __construct(LowStockService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function getReport()
    {
        $lowStockItems = $this->reportService->generateReport();

        return response()->json(['data' => $lowStockItems]);
    }
}
