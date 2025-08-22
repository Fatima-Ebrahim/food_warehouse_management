<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\PeriodReportsRequest;
use App\Services\ReportsService\FinancialReportService;
use Illuminate\Http\Request;

class FinancialReportsController extends Controller
{
    public function __construct(protected FinancialReportService $financialReportService)
    {}


    public function netProfitPerItem(PeriodReportsRequest $request)
    {
        $validated=$request->validated();
        $from = $validated['from'] ?? null;
        $to =$validated['to'] ??null ;
        $data = $this->financialReportService->netProfitPerItem($from ,$to);
        return response()->json(['success'=>true,'data'=>$data]);
    }

    public function netProfit(PeriodReportsRequest $request)
    {
        $validated=$request->validated();
        $from = $validated['from'] ?? null;
        $to =$validated['to'] ??null ;
        $data = $this->financialReportService->netProfit($from ,$to);
        return response()->json(['success'=>true,'data'=>$data]);
    }

    public function getNetProfitPerOffers(){
        $data = $this->financialReportService->getNetProfitPerOffers();
        return response()->json(['status'=>true ,'data'=>$data]);
    }

    public function accountsReceivable()
    {
        $data = $this->financialReportService->accountsReceivable();
        return response()->json(['success'=>true,'data'=>$data]);
    }



}
