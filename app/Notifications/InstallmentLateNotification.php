<?php

namespace App\Notifications;

use App\Broadcasting\FirebaseChannel;
use App\Models\Installment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class InstallmentLateNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $installment;

    public function __construct(Installment $installment)
    {
        $this->installment = $installment;
    }

    public function via($notifiable)
    {
        return [FirebaseChannel::class, 'database'];
    }

    public function toFirebase($notifiable)
    {
        return [
            'title' => 'قسط متأخر',
            'body' => 'لديك قسط بقيمة ' . $this->installment->amount . ' قد تجاوز تاريخ الاستحقاق.',
            'data' => [
                'installment_id' => (string)$this->installment->id,
                'type' => 'installment_late',
            ],
        ];
    }

    public function toArray($notifiable)
    {
        return [
            'installment_id' => $this->installment->id,
            'amount' => $this->installment->amount,
            'message' => 'لديك قسط متأخر بقيمة ' . $this->installment->amount,
        ];
    }
}
