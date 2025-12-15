<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Announcement;
use App\Services\FCMService;
use Illuminate\Http\Request;
use App\Models\Notification;


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
             'announcement_date' => 'required|date',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'user_group' => 'required|in:all,drivers,passengers',
            'type' => 'required|in:1,2',

        ], [
            'title.required' => 'Please enter a notification title.',
            'title.max' => 'The notification title cannot be longer than 255 characters.',
            'description.required' => 'Please enter the notification description.',
            'announcement_date.required' => 'Please select an announcement date.',
            'announcement_date.date' => 'Please enter a valid date.',
            'user_group.required' => 'Please select a user group.',
            'type.required' => 'Please select type (Announcement / News).',
        ]);
       
        // Upload image with original name
        $imageName = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $originalName = $file->getClientOriginalName();

            $destinationPath = public_path('assets/banner');

            // Check if file with same name exists
            if (file_exists($destinationPath.'/'.$originalName)) {
                // Add a timestamp to avoid overwriting
                $imageName = time().'_'.$originalName;
            } else {
                $imageName = $originalName;
            }

            $file->move($destinationPath, $imageName);
        }


        // SAVE ANNOUNCEMENT
        $announcement = Announcement::create([
            'title'            => $request->title,
            'description'      => $request->description,
            'announcement_date'=> $request->announcement_date,
            'image'            => $imageName,
            'type'              => $request->type,
        ]);

        // --------------------------------------------------
        // 🔥 FETCH USERS BASED ON YOUR REAL SYSTEM LOGIC
        // --------------------------------------------------
        if ($request->user_group === 'all') {

            // All users where role is null
            $userIds = User::whereNull('role')->pluck('id')->toArray();
        }

        elseif ($request->user_group === 'drivers') {

            // Drivers based on your logic
            $userIds = User::where(function ($q) {

                $q->whereHas('rides')
                ->orWhereHas('passengerRequestsAsDriver')
                ->orWhereHas('driverInterests')
                ->orWhereHas('rideBookings', function ($sub) {
                    $sub->whereNotNull('request_id');   // Driver received request
                });

            })->pluck('id')->toArray();
        }

        else { // passengers

            // Passengers based on your logic
            $userIds = User::where(function ($q) {

                $q->whereHas('passengerRequests')
                ->orWhereHas('rideBookings', function ($sub) {
                    $sub->whereNotNull('ride_id');  // passenger booked ride
                });

            })->pluck('id')->toArray();
        }
 
       //  Send notification via FCM
            $this->notificationService->sendAdminNotification(
                $request->title,
                $request->description,
                $userIds,                        // Correct userIds
                $request->announcement_date,
                $imageName,// send image to service
                $request->type 
            );
 
       // ✅ Redirect to announcements listing page with success message
        return redirect()->route('dashboard.admin.announcement-listing')
                        ->with('success', 'Notification successfully sent!');
    }


    public function announcementListing(Request $request)
    {
        $search = $request->query('search');

        $announcements = Announcement::when($search, function ($query) use ($search) {
            $query->where('title', 'LIKE', "%$search%")
                ->orWhere('description', 'LIKE', "%$search%");
        })
        ->orderBy('created_at', 'desc') // Latest First
        ->paginate(10);

        return view('admin.NotificationSettings.announcementsListing', compact('announcements'));
    }


}
