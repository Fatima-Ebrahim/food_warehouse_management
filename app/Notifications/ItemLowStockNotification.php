<?php

namespace App\Notifications;

use App\Broadcasting\FirebaseChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ItemLowStockNotification extends Notification implements ShouldQueue
{
    use Queueable;


    protected  $lowStockItems;


    public function __construct(array $lowStockItems)
    {
        $this->lowStockItems = $lowStockItems;
    }

    public function via($notifiable)
    {
        return [FirebaseChannel::class, 'database'];
    }


    public function toFirebase($notifiable)
    {
        $itemCount = count($this->lowStockItems);
        $title = 'تنبيه انخفاض المخزون';
        $body = "يوجد {$itemCount} مادة وصلت إلى الحد الأدنى للمخزون.";

        return [
            'title' => $title,
            'body' => $body,
            'data' => [
                'item_count' => (string)$itemCount,
                'type' => 'low_stock_alert',
            ],
        ];
    }


    public function toArray($notifiable)
    {
        return [
            'message' => 'يوجد ' . count($this->lowStockItems) . ' مادة وصلت للحد الأدنى.',
            'items' => array_column($this->lowStockItems, 'item_name', 'item_id'),
        ];
    }
}
