<?php
namespace App\Repositories;
use App\Models\Customer;

class CustomerRepository{

    public function create(array $data)
    {
        return Customer::query()->create($data);
    }


    public function getPoints($id)
    {
        return Customer::query()->where('user_id',$id)->first()->points;
    }


    public function updatePoints(int $userId, int $numberOfPoints, string $operation): void
    {
        $customer = Customer::where('user_id', $userId)->firstOrFail();

        if ($operation === 'add') {
            $customer->increment('points', $numberOfPoints);
        } elseif ($operation === 'subtract') {
            $customer->decrement('points', $numberOfPoints);
        } else {
            throw new \InvalidArgumentException("نوع العملية غير صالح. استخدم 'add' أو 'subtract'.");
        }
    }

    public function subtractPoints(int $userId, int $points): void
    {
        $this->updatePoints($userId, $points, 'subtract');
    }

    public function addPoints(int $userId, int $points): void
    {
        $this->updatePoints($userId, $points, 'add');
    }

    public function getByUserId(int $userId): Customer
    {
        return Customer::where('user_id', $userId)->firstOrFail();
    }

}



