<?php

namespace App\Notifications;

use App\Broadcasting\FirebaseChannel;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class OrderStatusUpdateNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $order;
    protected $status;

    public function __construct(Order $order, $status)
    {
        $this->order = $order;
        $this->status = $status;
    }

    public function via($notifiable)
    {
        return [FirebaseChannel::class, 'database'];
    }

    private function getStatusMessage($status)
    {
        switch ($status) {
            case 'pending':
                return 'قيد الانتظار';
            case 'rejected':
                return 'تم الرفض';
            case 'confirmed':
                return 'تم التأكيد';
            case 'paid':
                return 'مدفوع بالكامل';
            case 'partially_paid':
                return 'مدفوع جزئياً';
            default:
                return $status;
        }
    }

    public function toFirebase($notifiable)
    {
        $statusMessage = $this->getStatusMessage($this->status);
        $title = 'تحديث حالة الطلب';
        $body = "طلبك رقم {$this->order->order_number} الآن بحالة: {$statusMessage}";

        return [
            'title' => $title,
            'body' => $body,
            'data' => [
                'order_id' => (string)$this->order->id,
                'status' => $this->status,
                'type' => 'order_status_update',
            ],
        ];
    }

    public function toArray($notifiable)
    {
        $statusMessage = $this->getStatusMessage($this->status);

        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'status' => $this->status,
            'message' => "تم تحديث حالة طلبك رقم {$this->order->order_number} إلى: {$statusMessage}",
        ];
    }
}
