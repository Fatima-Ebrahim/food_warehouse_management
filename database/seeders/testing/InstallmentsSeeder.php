<?php

namespace Database\Seeders\testing;

use Illuminate\Database\Seeder;
use App\Models\Installment;
use App\Models\Order;
use Carbon\Carbon;

class InstallmentsSeeder extends Seeder
{
    public function run(): void
    {
        $orders = Order::where('payment_type', 'installment')->get();

        foreach ($orders as $order) {
            $firstDate = Carbon::now();
            $percentages = [0.4, 0.3, 0.3];
            foreach ($percentages as $i => $percent) {
                Installment::create([
                    'order_id' => $order->id,
                    'amount' => $order->final_price * $percent,
                    'due_date' => $firstDate->copy()->addDays(30 * $i),
                    'paid_at' => $i === 0 ? Carbon::now() : null,
                    'note' => 'دفعة رقم ' . ($i + 1),
                ]);
            }
        }
    }
}
