<?php
namespace App\Repositories\Costumer;

use App\Models\CartItem;
use App\Models\PointTransaction;

class PointTransactionRepository{

    public function create(array $data): PointTransaction
    {
        return PointTransaction::create($data);
    }


    public function getByCustomer(int $customerId)
    {
        return PointTransaction::where('customer_id', $customerId)->latest()->get();
    }

    public function delete(int $id): void
    {
        PointTransaction::findOrFail($id)->delete();
    }




}
