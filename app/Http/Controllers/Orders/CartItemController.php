<?php

namespace App\Http\Controllers\Orders;
use App\Http\Controllers\Controller;

use App\Http\Requests\CartItemRequest;
use App\Http\Requests\PriceCalculationRequest;
use App\Models\CartItem;
use App\Services\Orders\CartItemService;
use Illuminate\Http\Request;

class CartItemController extends Controller
{
    protected $service;
    public function __construct(CartItemService $service) {
        $this->service=$service;
    }

    public function showAllCartItems()
    {
        $user = auth()->user();
        $items = $this->service->getUserCartItems($user);
        return response()->json($items);
    }

    public function addToCart(CartItemRequest $request)
    {
        $user=auth()->user();
        $item = $this->service->addToCart( $request->validated() ,$user);
        return response()->json($item, 201);
    }

    public function update($type, $id, CartItemRequest $request)
    {
        try {
            $item = $this->service->updateQuantity(
                $request->validated(),
                $type,
                $id
            );

            return response()->json([
                'success' => true,
                'data' => $item
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function destroy($type, $id)
    {
        try {
            $this->service->removeFromCart($type, $id);

            return response()->json(['message' => 'Item removed successfully'],200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);

    }

    }

    public function previewSelectedItemsPrice(PriceCalculationRequest $request)
    {
        $userId = auth()->user()->id;
        $pointsUsed = $request->points_used ?? 0;

        try {
            $result = $this->service->calculateSelectedItemsPrice(
                $request->offers??null,
                $request->items??null,
                $userId,
                $pointsUsed
            );

            return response()->json([
                'message' => 'تم احتساب السعر بنجاح',
                'summary' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
