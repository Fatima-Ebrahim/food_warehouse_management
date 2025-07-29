<?php
// app/Channels/FirebaseChannel.php

namespace App\Broadcasting;

use App\Services\FirebaseNotificationService;
use Illuminate\Notifications\Notification;

class FirebaseChannel
{

    /**
     * The Firebase service instance.
     *
     * @var \App\Services\FirebaseNotificationService
     */
    protected $firebase;

    /**
     * Create a new channel instance.
     *
     * @param  \App\Services\FirebaseNotificationService  $firebase
     * @return void
     */
    public function __construct(FirebaseNotificationService $firebase)
    {
        $this->firebase = $firebase;
    }

    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {

        if (! method_exists($notification, 'toFirebase')) {
            return;
        }

        $data = $notification->toFirebase($notifiable);

        $this->firebase->sendToUser(
            $notifiable->id,
            $data['title'],
            $data['body'],
            $data['data'] ?? []
        );
    }
}
