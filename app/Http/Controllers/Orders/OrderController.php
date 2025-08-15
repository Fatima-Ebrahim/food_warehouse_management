<?php

namespace App\Http\Controllers\Orders;
use App\Http\Controllers\Controller;

use App\Http\Requests\ConfirmOrderRequest;
use App\Http\Requests\DeliverOrderRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Models\SpecialOffer;
use App\Repositories\SpecialOfferRepository;
use App\Services\Orders\OrderService;
use Illuminate\Support\Facades\Storage;

class  OrderController extends Controller
{

    public function __construct( protected  OrderService $orderService ,
    protected SpecialOfferRepository $offerRepository) {

    }



    public function confirm(ConfirmOrderRequest $request)
    {
        $userId = auth()->id();
        $validated = $request->validated();

        try {
            $order = $this->orderService->confirmOrder(
                $userId,
                $validated['payment_type'],
                $validated['items']??null,
                $validated['offers']??null,
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

    public function getPendingOrders(){
        try{

        $pendOrders=$this->orderService->getPendingOrders();
        return response()->json([
            'pend_orders '=>$pendOrders

        ],200);}
        catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function updateOrderStatus(UpdateOrderStatusRequest $request){
        try{
            $validated = $request->validated();
        $pendOrders=$this->orderService->updateOrderStatus($validated['order_id'] ,$validated['status']);
        return response()->json([
            'pend_orders '=>$pendOrders

        ],200);}
        catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

//تأكيد اشتلام الطلبية
//  تأكيد الدفع اذا كان كاش وتأكيد الدفعة الاولى للتقسيط
    public function deliverOrder(DeliverOrderRequest $request)
    {
        try {
            $validated = $request->validated();
            $result = $this->orderService->deliverOrder($validated);
            return response()->json(['status' => true, 'data' => $result]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 400);
        }


    }
    public function getUserActiveOrders()
    {
        try {
           $user=auth()->user();
            $result = $this->orderService->getUserActiveOrders($user);
            return response()->json(['status' => true, 'orders' => $result]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function getUserPendingOrders()
    {
        try {
            $user=auth()->user();
            $result = $this->orderService->getUserPendingOrders($user);
            return response()->json(['status' => true, 'pending_orders' => $result]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function getOrderBatches( $orderId){
       return $this->orderService->getOrderBatches($orderId);

    }


}
