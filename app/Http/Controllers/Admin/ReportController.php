<?php

namespace App\Http\Controllers\Admin;
 use App\Http\Controllers\Controller;
 use App\Services\AdminServices\ReportService;
 use App\Http\Requests\SalesReportRequest;


 class ReportController extends Controller
 {

     public function __construct(protected ReportService $reportService)
     {}

     public function salesReport(SalesReportRequest $request)
     {
         $report = $this->reportService->generateReport(
             $request->type,
             $request->from,
             $request->to
         );

         return response()->json([
             'status' => true,
             'data' => $report
         ]);
     }
 }
