<?php

namespace Database\Seeders\testing;

use Illuminate\Database\Seeder;
use App\Models\OrderBatchDetail;
use App\Models\OrderItem;
use App\Models\PurchaseReceiptItem;

class OrderBatchDetailsSeeder extends Seeder
{
    public function run(): void
    {
        $orderItems = OrderItem::all();
        $receiptItems = PurchaseReceiptItem::all();

        foreach ($orderItems as $orderItem) {
            $receiptItem = $receiptItems->random();
            OrderBatchDetail::create([
                'order_item_id' => $orderItem->id,
                'purchase_receipt_item_id' => $receiptItem->id,
                'quantity' => rand(1, 10),
            ]);
        }
    }
}
