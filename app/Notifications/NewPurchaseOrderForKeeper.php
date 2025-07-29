<?php
// app/Notifications/NewPurchaseOrderForKeeper.php

namespace App\Notifications;

use App\Broadcasting\FirebaseChannel;
use App\Models\PurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewPurchaseOrderForKeeper extends Notification
{
    use Queueable;

    protected $purchaseOrder;

    public function __construct(PurchaseOrder $purchaseOrder)
    {
        $this->purchaseOrder = $purchaseOrder;
    }


    public function via($notifiable): array
    {
        return [FirebaseChannel::class];
    }

    public function toFirebase($notifiable): array
    {
        return [
            'title' => 'طلبية شراء جديدة بانتظار المعالجة',
            'body' => "تم إنشاء طلبية شراء جديدة برقم {$this->purchaseOrder->po_number}.",
            'data' => [
                'order_id' => (string) $this->purchaseOrder->id,
                'screen' => 'PendingOrders'
            ]
        ];
    }
}
