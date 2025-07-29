<?php

namespace App\Http\Controllers\Orders;
use App\Http\Controllers\Controller;

use App\Http\Requests\ConfirmOrderRequest;
use App\Services\Orders\OrderService;

class  OrderController extends Controller
{
    protected $orderService;
    public function __construct( OrderService $service) {
        $this->orderService= $service;
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





}
