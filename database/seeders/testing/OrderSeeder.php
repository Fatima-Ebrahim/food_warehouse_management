<?php
namespace Database\Seeders\testing;
use App\Models\Order;
use App\Models\Cart;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run()
    {
        Cart::all()->each(function ($cart) {
            $usedPoints = rand(0, 500);
            $total = rand(5000, 15000);
            $final = $usedPoints ? ($total - $usedPoints * 0.5) : $total;

            Order::create([
                'cart_id' => $cart->id,
                'payment_type' => ['cash', 'installment'][rand(0, 1)],
                'payment_status' => ['confirmed', 'paid', 'partially_paid'][rand(0, 2)],
                'total_price' => $total,
                'used_points' => $usedPoints,
                'final_price' => $final,
            ]);
        });
    }
}
