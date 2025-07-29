<?php

namespace Database\Seeders\testing;
use App\Models\Customer;
use Illuminate\Database\Seeder;
use App\Models\PointTransaction;
use App\Models\User;
use App\Models\Order;

class PointTransactionsSeeder extends Seeder
{
    public function run(): void
    {
        $users = Customer::all();
        $orders = Order::all();

        foreach ($users as $user) {
            PointTransaction::create([
                'Customer_id' => $user->id,
                'type' => 'subtract',
                'points' => rand(10, 100),
                'order_id' => $orders->random()->id ?? null,
                'reason' => 'خصم على الطلب',
            ]);

            PointTransaction::create([
                'Customer_id' => $user->id,
                'type' => 'add',
                'points' => rand(10, 50),
                'reason' => 'مكافأة شراء',
            ]);
        }
    }
}
