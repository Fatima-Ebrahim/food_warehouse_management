<?php
namespace App\Repositories\Orders;
use App\Models\Order;

class OrderRepository{


    public function create(array $data)
    {
        return Order::query()->create($data);
    }

    public function delete($id)
    {
        Order::query()->find($id)->delete();
    }


    public function find($id)
    {
        return Order::findOrFail($id);
    }

    //    public function updateStatus(CartItem $cartItem, array $data)
//    {
//        $cartItem->update($data);
//        return $cartItem;
//    }
}
