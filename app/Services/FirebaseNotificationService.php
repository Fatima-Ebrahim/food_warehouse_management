<?php

namespace App\Services;

use App\Models\User;
// 1. هذا المودل الخاص بك سيبقى باسمه الأساسي
use App\Models\Notification;
// 2. هذا الكلاس الخاص بـ Firebase سنعطيه اسماً مستعاراً وواضحاً
use Kreait\Firebase\Messaging\Notification as FirebaseNotificationPayload;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class FirebaseNotificationService
{
    protected $messaging;

    public function __construct()
    {
        $firebase = (new Factory)->withServiceAccount(config('services.firebase.credentials'));
        $this->messaging = $firebase->createMessaging();
    }

    public function sendToUser(int $receiverId, string $title, string $body, array $data = [])
    {
        $user = User::find($receiverId);

        if (!$user || !$user->device_token) {
            Log::warning("User not found or has no device token. UserID: {$receiverId}");
            return ['status' => 'error', 'message' => 'User not found or has no device token.'];
        }

        // عند التخزين، نستخدم المودل الخاص بنا (Notification)
        $this->storeNotificationInDb($receiverId, $title, $body, $data);

        $token = $user->device_token;

        // 3. عند تجهيز الرسالة لـ Firebase، نستخدم الاسم المستعار
        $notificationPayload = FirebaseNotificationPayload::create($title, $body);

        $message = CloudMessage::withTarget('token', $token)
            ->withNotification($notificationPayload)
            ->withData($data);

        try {
            $this->messaging->send($message);
            return ['status' => 'success', 'message' => 'Notification sent and stored successfully.'];
        } catch (\Exception $e) {
            Log::error("FCM Send Error to UserID {$receiverId}: " . $e->getMessage());
            return ['status' => 'error', 'message' => 'Failed to send notification to device.', 'error_details' => $e->getMessage()];
        }
    }

    private function storeNotificationInDb(int $receiverId, string $title, string $body, array $data = [])
    {
        // هنا نستخدم مودل قاعدة البيانات الخاص بنا، واسمه `Notification`
        Notification::create([
            'receiver_id' => $receiverId,
            'title'       => $title,
            'body'        => $body,
            'data'        => $data,
            'seen'        => false,
        ]);
    }
}
