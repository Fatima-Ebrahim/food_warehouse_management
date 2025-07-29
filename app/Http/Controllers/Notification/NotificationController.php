<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Services\FirebaseNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class NotificationController extends Controller
{
    protected $firebase;

    public function __construct(FirebaseNotificationService $firebase)
    {
        $this->firebase = $firebase;
    }

    public function storeUserDeviceToken(Request $request)
    {
        $request->validate([
            'device_token' => 'required|string',
        ]);
        $user = Auth::user();
        $user->device_token = $request->device_token;
        $user->save();

        return response()->json(['message' => 'Device token updated successfully.'], 200);
    }

    public function sendToUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'data' => 'nullable|array',
        ]);

        $result = $this->firebase->sendToUser(
            $request->user_id,
            $request->title,
            $request->body,
            $request->data ?? []
        );

        if ($result['status'] === 'success') {
            return response()->json($result);
        } else {
            return response()->json($result, 500);
        }
    }

    public function index()
    {
        $notifications = Notification::where('receiver_id', Auth::id())->where('seen', false)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($notifications);
    }


    public function markAsSeen($notificationId)
    {
        $notification = Notification::where('id', $notificationId)
            ->where('receiver_id', Auth::id())
            ->first();

        if (!$notification) {
            return response()->json(['message' => 'Notification not found.'], 404);
        }

        $notification->seen = true;
        $notification->save();

        return response()->json(['message' => 'Notification marked as seen.']);
    }
}
