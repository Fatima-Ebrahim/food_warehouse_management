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

    protected $offer;

    public function __construct(SpecialOffer $offer)
    {
        $this->offer = $offer;
    }

    public function via($notifiable)
    {
        return [FirebaseChannel::class, 'database'];
    }

    private function getStatusMessage($status)
    {
        switch ($status) {
            case true:
                return 'فعّال';
            case false:
                return 'غير فعّال';
            default:
                return $status;
        }
    }

    public function toFirebase($notifiable)
    {
        $statusMessage = $this->getStatusMessage($this->offer->status);
        return [
            'title' => 'تحديث حالة عرض في سلتك',
            'body' => "تم تغيير حالة العرض '{$this->offer->name}' إلى: {$statusMessage}",
            'data' => [
                'offer_id' => (string)$this->offer->id,
                'status' => $this->offer->status,
                'type' => 'offer_status_update',
            ],
        ];
    }

    public function toArray($notifiable)
    {
        $statusMessage = $this->getStatusMessage($this->offer->status);
        return [
            'offer_id' => $this->offer->id,
            'offer_name' => $this->offer->name,
            'status' => $this->offer->status,
            'message' => "تم تغيير حالة العرض '{$this->offer->name}' في سلتك إلى: {$statusMessage}.",
        ];
    }
}
