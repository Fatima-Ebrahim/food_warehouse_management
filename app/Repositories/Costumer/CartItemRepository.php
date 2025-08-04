<?php
namespace App\Repositories\Costumer;

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

    public function getCartItemForUser(int $cartItemId, int $userId)
    {
        return CartItem::with(['itemUnit.item', 'itemUnit.unit'])
            ->where('id', $cartItemId)
            ->whereHas('cart', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->firstOrFail();
    }

    public function checkIfItemExistsOnCart($itemUnitId ,$cart){
        return $cart->cartItems()
            ->where('item_unit_id', $itemUnitId)
            ->exists();
    }

    public function getCartItemForUserWithRelations(int $cartItemId, int $userId, array $relations): CartItem
    {
        return CartItem::with($relations)
            ->where('id', $cartItemId)
            ->whereHas('cart', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->firstOrFail();
    }
}
