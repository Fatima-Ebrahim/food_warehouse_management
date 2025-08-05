<?php

namespace App\Http\Controllers\Orders;
use App\Http\Controllers\Controller;

use App\Http\Requests\ConfirmOrderRequest;
use App\Http\Requests\QrScannerRequest;
use App\Services\Orders\OrderService;
use Illuminate\Support\Facades\Storage;

class  OrderController extends Controller
{

    public function __construct( protected  OrderService $orderService) {

    }

    public function confirm(ConfirmOrderRequest $request)
    {
        $userId = auth()->id();
        $validated = $request->validated();

        try {
            $order = $this->orderService->confirmOrder(
                $userId,
                $validated['payment_type'],
                $validated['items'],
                $validated['points_used'] ?? 0
            );

            return response()->json([
                'status' => true,
                'message' => 'order confirmed successfully ',
                'data' => $order
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'error while confirm the order',
                'error' => $e->getMessage()
            ], 400);
        }
    }


    public function getOrderDetails($orderId){
       $order= $this->orderService->getOrderDetails($orderId);
       return response()->json($order);
    }

    public function getOrderQr($orderId){

            $path = $this->orderService->getOrderQrPath($orderId);

            if (!Storage::disk('public')->exists($path)) {
                return response()->json(['message' => 'File not found'], 404);
            }

            return response()->file(storage_path("app/public/{$path}"));
    }

    public function scanQr(QrScannerRequest $request)
    {
        try {
            $result = $this->orderService->processQr($request->qr_data);
            return response()->json(['status' => true, 'data' => $result]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 400);
        }
    }



}
