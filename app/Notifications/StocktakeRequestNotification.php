<?php

namespace App\Notifications;

use App\Broadcasting\FirebaseChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class StocktakeRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $stocktake;

    public function __construct($stocktake)
    {
        $this->stocktake = $stocktake;
    }

    public function via($notifiable)
    {
        return [FirebaseChannel::class, 'database'];
    }

    public function toFirebase($notifiable)
    {
        $title = 'طلب جرد جديد';
        $body = $this->stocktake->type === 'immediate'
            ? 'مطلوب منك إجراء جرد فوري للمخزون.'
            : 'تم جدولة طلب جرد جديد لك.';

        return [
            'title' => $title,
            'body' => $body,
            'data' => [
                'stocktake_id' => (string)$this->stocktake->id,
                'type' => 'stocktake_request',
            ],
        ];
    }

    public function toArray($notifiable)
    {
        return [
            'stocktake_id' => $this->stocktake->id,
            'message' => 'طلب جرد جديد.',
        ];
    }
}
