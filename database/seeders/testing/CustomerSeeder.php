<?php

namespace Database\Seeders\testing;
use App\Models\Customer;
use Illuminate\Database\Seeder;
use App\Models\PointTransaction;
use App\Models\User;
use App\Models\Order;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            Customer::create([
                'user_id' => $user->id,
                'phone_number'=>'000000000'
            ]);

        }
    }
}
