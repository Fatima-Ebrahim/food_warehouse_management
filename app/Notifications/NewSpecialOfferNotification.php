<?php

namespace App\Notifications;

use App\Broadcasting\FirebaseChannel;
use App\Models\SpecialOffer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewSpecialOfferNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $offer;

    public function __construct(SpecialOffer $offer)
    {
        $this->offer = $offer;
    }

    public function via($notifiable)
    {
        return [FirebaseChannel::class, 'database'];
    }

    public function toFirebase($notifiable)
    {
        return [
            'title' => 'عرض خاص جديد!',
            'body' => "لا تفوت العرض الجديد  " . $this->offer->name,
            'data' => [
                'offer_id' => (string)$this->offer->id,
                'type' => 'new_special_offer',
            ],
        ];
    }

    public function toArray($notifiable)
    {
        return [
            'offer_id' => $this->offer->id,
            'offer_name' => $this->offer->name,
            'message' => "تمت إضافة عرض خاص جديد" . $this->offer->name,
        ];
    }
}
