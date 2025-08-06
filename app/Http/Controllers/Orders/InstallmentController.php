<?php

namespace App\Http\Controllers\Orders;

use App\Http\Controllers\Controller;
use App\Http\Requests\QrScannerRequest;
use App\Services\Orders\InstallmentService;
use App\Services\Orders\OrderService;
use App\Services\Orders\QrService;
use Illuminate\Http\Request;

class InstallmentController extends Controller
{
    public function __construct(protected InstallmentService $installmentService ,
                                protected OrderService $orderService ,
                                protected QrService  $qrService)
    {
    }

    public function getUserUnpaidInstallments(){
        $user=auth()->user();
        $installments=$this->installmentService->getUserUnpaidInstallments($user);
        return response()->json(['installments'=>$installments]);
    }

    public function getOrderInstallmentPlan($orderId)
    {
        try {
            $data = $this->installmentService->getInstallmentsPlanForOrder($orderId);
            return response()->json([
                'status' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }


    public function getOrderInstallmentsBatchs($orderId){
        try {
            $data = $this->installmentService->getOrderInstallmentsBatchs($orderId);
            return response()->json([
                'status' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function payNextInstallment(QrScannerRequest $request){
        try {
            $validated = $request->validated();
            $result = $this->installmentService->payNextInstallmentAmount($validated);
            return response()->json(['status' => true, 'data' => $result]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 400);
        }
    }
}
