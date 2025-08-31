<?php

namespace App\Notifications;

use App\Broadcasting\FirebaseChannel;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class OrderDelayedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function via($notifiable)
    {
        return [FirebaseChannel::class, 'database'];
    }

    public function toFirebase($notifiable)
    {
        return [
            'title' => 'تنبيه: تأخر في الطلبية',
            'body'  => "طلبك رقم {$this->order->id} قد تأخر عن الموعد المتوقع.",
            'data'  => [
                'order_id' => (string)$this->order->id,
                'type'     => 'order_delayed',
            ],
        ];
    }

    public function toArray($notifiable)
    {
        return [
            'order_id'   => $this->order->id,
            'order_code' => $this->order->code ?? null,
            'message'    => "طلبك رقم {$this->order->id} متأخر عن الموعد المتوقع.",
        ];
    }
}
