<?php
namespace Database\Seeders\testing;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Database\Seeder;

class CartSeeder extends Seeder
{
    public function run()
    {
        User::all()->each(function ($user) {
            Cart::create([
                'user_id' => $user->id,
            ]);
        });
    }
}
