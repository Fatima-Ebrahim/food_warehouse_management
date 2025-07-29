<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Broadcasting\FirebaseChannel;
use App\Models\Stocktake;

class StocktakeCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $stocktake;

    public function __construct(Stocktake $stocktake)
    {
        $this->stocktake = $stocktake;
    }

    public function via($notifiable)
    {
        return [FirebaseChannel::class, 'database'];
    }

    public function toFirebase($notifiable)
    {
        return [
            'title' => 'اكتملت عملية الجرد',
            'body' => "تم إكمال عملية الجرد رقم {$this->stocktake->id} بنجاح.",
            'data' => [
                'stocktake_id' => (string)$this->stocktake->id,
                'type' => 'stocktake_completed',
            ],
        ];
    }

    public function toArray($notifiable)
    {
        return [
            'stocktake_id' => $this->stocktake->id,
            'message' => "اكتملت عملية الجرد رقم {$this->stocktake->id}.",
        ];
    }
}
