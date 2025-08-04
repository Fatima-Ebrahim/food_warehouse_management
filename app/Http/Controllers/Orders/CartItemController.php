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

    public function index()
    {

        $user = auth()->user();
        $items = $this->service->getUserCartItems($user);
        return response()->json($items);
    }

    public function store(CartItemRequest $request)
    {
        $user=auth()->user();
        $item = $this->service->addToCart( $request->validated() ,$user);
        return response()->json($item, 201);
    }

    public function update(CartItemRequest $request, CartItem $cartItem)
    {
        $item = $this->service->updateQuantity($cartItem, $request->validated());
        return response()->json($item);
    }

    public function destroy(CartItem $cartItem)
    {
        $this->service->removeFromCart($cartItem);
        return response()->json(['message' => 'Item removed']);
    }

    public function previewSelectedItemsPrice(PriceCalculationRequest $request)
    {
        $userId = auth()->user()->id;
        $pointsUsed = $request->points_used ?? 0;

        try {
            $result = $this->service->calculateSelectedItemsPrice(
                $request->items,
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
