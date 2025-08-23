<?php

namespace App\Notifications;

use App\Broadcasting\FirebaseChannel;
use App\Models\SpecialOffer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class OfferStatusUpdateNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected  $offer;
    protected  $newStatus;

    public function __construct(SpecialOffer $offer,  $newStatus)
    {
        $this->offer = $offer;
        $this->newStatus = $newStatus;
    }

    public function via($notifiable)
    {
        return [FirebaseChannel::class, 'database'];
    }

    public function toFirebase($notifiable)
    {
        $title = 'تحديث على عرض في سلتك';
        $body = $this->newStatus
            ? "العرض '{$this->offer->name}' أصبح متاحاً مجدداً."
            : "نعتذر، العرض '{$this->offer->name}' لم يعد متاحاً وتمت إزالته من سلتك.";

        return [
            'title' => $title,
            'body' => $body,
            'data' => [
                'offer_id' => (string)$this->offer->id,
                'status' => $this->newStatus,
                'type' => 'offer_status_update',
            ],
        ];
    }

    public function toArray($notifiable)
    {
        return [
            'offer_id' => $this->offer->id,
            'offer_name' => $this->offer->name,
            'status' => $this->newStatus,
            'message' => $this->newStatus
                ? "تم تفعيل العرض '{$this->offer->name}' مجدداً."
                : "تم إلغاء العرض '{$this->offer->name}' وإزالته من سلتك.",
        ];
    }
}
