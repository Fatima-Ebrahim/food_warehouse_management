<?php
namespace Database\Seeders\testing;
use App\Models\CartItem;
use App\Models\Cart;
use App\Models\ItemUnit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CartItemSeeder extends Seeder
{
    public function run()
    {
        Cart::all()->each(function ($cart) {
            $itemUnits = ItemUnit::inRandomOrder()->take(rand(2, 5))->get();
            foreach ($itemUnits as $itemUnit) {
                CartItem::create([
                    'cart_id' => $cart->id,
                    'item_unit_id' => $itemUnit->id,
                    'quantity' => rand(1, 10),
                ]);
            }
        });
    }
}
