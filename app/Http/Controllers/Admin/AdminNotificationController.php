<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\FCMService;
use Illuminate\Http\Request;

class AdminNotificationController extends Controller
{
    protected $notificationService;

    public function __construct(FCMService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function sendNotification()
    {
        // Fetch only users with role = null
        $users = User::whereNull('role')->get();
        return view('admin.NotificationSettings.sendNotifications', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'users' => 'required|array',
        ], [
            'title.required' => 'Please enter a notification title.',
            'title.max' => 'The notification title cannot be longer than 255 characters.',
            'description.required' => 'Please enter the notification description.',
            'users.required' => 'Please select at least one user to send the notification.',
            'users.array' => 'Invalid users selection format.',
        ]);

        $response = $this->notificationService->sendAdminNotification(
            $request->title,
            $request->description,
            $request->users
        );

        return back()->with('success', 'Notification successfully sent!');
    }
    

}
