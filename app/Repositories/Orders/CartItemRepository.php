<?php
namespace App\Repositories\Orders;

use App\Models\CartItem;

class CartItemRepository{

    public function create(array $data)
    {
        return CartItem::create($data);
    }

    public function update(CartItem $cartItem, array $data)
    {
        $cartItem->update($data);
        return $cartItem;
    }

    public function delete(CartItem $cartItem)
    {
        return $cartItem->delete();
    }

    public function getByCartId($cartId)
    {
        return CartItem::where('cart_id', $cartId)->with('itemUnit')->get();
    }

    public function find($id): ?CartItem
    {
        return CartItem::findOrFail($id);
    }

    public function getCartItemForUser(int $cartItemId, int $userId): CartItem
    {
        return CartItem::with(['itemUnit.item', 'itemUnit.unit'])

            ->where('id', $cartItemId)
            ->whereHas('cart', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->firstOrFail();
    }
}
