<?php
namespace App\Repositories\Costumer;
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


    public function get($id)
    {
        return Order::findOrFail($id);
    }

    public function updateQRPath($path , $orderId){
        Order::query()->find($orderId)->update(['qr_code_path'=>$path]);
    }
    //    public function updateStatus(CartItem $cartItem, array $data)
//    {
//        $cartItem->update($data);
//        return $cartItem;
//    }
}
