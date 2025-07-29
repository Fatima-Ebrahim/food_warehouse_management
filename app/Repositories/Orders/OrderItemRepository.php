<?php
namespace App\Repositories\Orders;


use App\Models\OrderItem;

class OrderItemRepository{


    public function create(array $data)
    {
        return OrderItem::query()->create($data);
    }

    public function delete($id)
    {
        OrderItem::query()->find($id)->delete();
    }


    public function find($id)
    {
        return OrderItem::findOrFail($id);
    }

}
