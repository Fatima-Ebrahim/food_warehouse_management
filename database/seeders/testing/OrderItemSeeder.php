<?php
namespace Database\Seeders\testing;
use App\Models\OrderItem;
use App\Models\Order;
use App\Models\ItemUnit;
use Illuminate\Database\Seeder;

class OrderItemSeeder extends Seeder
{
    public function run()
    {
        Order::all()->each(function ($order) {
            $itemUnits = ItemUnit::inRandomOrder()->take(rand(2, 5))->get();
            foreach ($itemUnits as $itemUnit) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'item_unit_id' => $itemUnit->id,
                    'quantity' => rand(1, 3),
                    'price' => rand(1000, 3000),
                ]);
            }
        });
    }
}
