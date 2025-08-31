<?php

namespace App\Console\Commands;

use App\Notifications\OrderCanceledNotification;
use App\Notifications\OrderDelayedNotification;
use App\Repositories\Costumer\OrderRepository;
use Illuminate\Console\Command;
use App\Models\Order;
use App\Settings\OrderSettings;
use Carbon\Carbon;

class CheckConfirmedOrders extends Command
{
    protected $signature = 'orders:check-confirmed';
    protected $description = 'تفحص الطلبات المؤكدة وتطبق إعدادات التأخير (إشعارات / إلغاء تلقائي)';

    public function handle()
    {
        $settings = app(OrderSettings::class);

        $orders = Order::where('status', 'confirmed')->get();

        foreach ($orders as $order) {

            $daysSinceConfirmed = Carbon::now()->diffInDays($order->updated_at);


            if ($settings->notify_on_delay &&
                $daysSinceConfirmed >= $settings->days_to_sent_notification) {

                $order->cart->user->notify(new OrderDelayedNotification($order));

                $this->info("تم إرسال إشعار للطلب رقم {$order->id}");
            }

            // إلغاء تلقائي عند تجاوز الحد
            if ($settings->auto_cancel_delayed_orders &&
                $daysSinceConfirmed >= $settings->delayed_days_limit) {

                foreach ($order->orderItems as $orderItem) {
                    $item = $orderItem->itemUnit->item;
                    $item->increment('total_available_quantity', $orderItem->quantity);
                }
                $order->cart->user->notify(new OrderCanceledNotification($order));
                $this->warn("تم إلغاء الطلب رقم {$order->id} تلقائياً");
                app(OrderRepository::class)->delete($order->id);
            }
        }

        $this->info('✅ انتهى فحص الطلبات المؤكدة.');
    }
}
