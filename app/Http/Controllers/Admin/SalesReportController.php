<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

use App\Services\ReportsService\SalesReportService;
use App\Http\Requests\Reports\{CustomerStatementRequest,
    PeriodReportsRequest,
    SalesByProductRequest,
    AggregateSalesRequest,
    BasePeriodRequest,
    SalesReportRequest};

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SalesReportController extends Controller
{
    public function __construct(protected SalesReportService $salesReportService)
    {}


    public function customerStatement($userId)
    {
        return response()->json([
            'success' => true,
            'data' => $this->salesReportService->customerStatement($userId)
        ]);
    }

    public function salesByCustomer(PeriodReportsRequest $request)

    {
        $validated=$request->validated();
        $data = $this->salesReportService->getSalesByCustomer(
            $validated['from']??null,
            $validated['to']??null);
        return response()->json($data);
    }

    public function salesByProduct(PeriodReportsRequest $request)
    {
        $validated = $request->validated();
        $data = $this->salesReportService->getSalesByProductReport(
            $validated['from'] ??null ,
            $validated['to']?? null ,
            $validated['sort'] ?? "desc"
        );
        return response()->json($data);
    }


    public function aggregateSales(AggregateSalesRequest $req)
    {
        return response()->json([
            'success' => true,
            'data' => $this->salesReportService->aggregateSales($req->validated())
        ]);
    }

    public function salesByPaymentType(PeriodReportsRequest $request)
    {
        $validate =$request->validated();
        $validate['from']= $validate['from'] ??null;
        $validate['to']= $validate['to'] ??null ;
        $validate['months']= $validate['months'] ??null ;
        $validate['paymentType']= $validate['paymentType'] ??null ;
        $data =$this->salesReportService->salesByPaymentType($validate);
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function ordersDeliveryStatus(PeriodReportsRequest $request )
    {
        $validated=$request->validated();
        $validated['status'] = $validated['status'] ?? 'all';
        $validated['from'] = $validated['from ']?? null ;
        $validated['to'] =$validated['from'] ??null ;
        $result=$this->salesReportService->ordersDeliveryStatus($validated);
        return response()->json([
            'success' => true,
            'filter_type' => $result['filter_type'] ,
            'data'=>$result['data'],
        ]);
    }

    public function topCustomers(PeriodReportsRequest $request)
    {
        $validated =$request->validated();
        $validated['getBy'] = $validated['getBy'] ?? "OrdersNumber";
        $validated['paymentType'] = $validated['paymentType'] ?? null;
        $validated['from'] = $validated['from ']?? null ;
        $validated['to'] =$validated['from'] ??null ;
        return response()->json([
            'success' => true,
            'data' => $this->salesReportService->topCustomers($validated)
        ]);
    }






}

