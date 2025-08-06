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

    public function makeOrderPaid(Order $order){
        $order->update(['status'=>'paid']);
    }
    public function get($id)
    {
        return Order::findOrFail($id);
    }

    public function getWithItems(int $id): Order
    {
        return Order::with(['orderItems.itemUnit.item'])->findOrFail($id);
    }

    public function updateQRPath($path , $orderId){
        Order::query()->find($orderId)->update(['qr_code_path'=>$path]);
    }


    public function isPaid(Order $order): bool
    {
        return $order->status === 'paid';
    }

    public function isPartiallyPaid(Order $order): bool
    {
        return $order->status === 'partially_paid';
    }

    public function updateStatus(Order $order ,$status)
    {
     return   $order->update(['status'=>$status]);

    }

    public function getAllPendingOrders(){
        return Order::query()->where('status','=','pending')->get();
    }

    public function getWithInstallments($orderId)
    {
        return Order::with('installments')->findOrFail($orderId);
    }


}
