<?php

namespace App\Http\Controllers\Courier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CourierRequest;
use App\Models\CourierRequestDriverInterest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Services\FCMService;
use Illuminate\Support\Facades\Log;


class CourierRequestController extends Controller
{
    // ✅ Sender Create Courier Request (Only Offline)
    // public function create(Request $request)
    // {
    //     $user = Auth::guard('api')->user();
    //     if (!$user) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Unauthorized.'
    //         ], 401);
    //     }

    //     if ($user->is_online == 1) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'You are online as courier. Go offline to create courier request.'
    //         ],403);
    //     }

    //     // ✅ Validation with Custom Messages
    //     $validator = Validator::make($request->all(), [
    //         'pickup_location' => 'required|string',
    //         'drop_location' => 'required|string',
    //         'distance' => 'required|string',
    //         'time' => 'required|string',
    //         'trip_type' => 'required|in:incity,intercity',

    //         'sender_name' => 'required|string',
    //         'sender_phone' => 'required|string',
    //         'sender_landmark' => 'nullable|string',

    //         'receiver_name' => 'required|string',
    //         'receiver_phone' => 'required|string',
    //         'receiver_landmark' => 'nullable|string',

    //         'package_description' => 'nullable|string',
    //         'package_size' => 'required|in:small,medium,large',
    //         'instruction' => 'nullable|string',

    //         'suggested_price' => 'nullable|numeric',
    //         'payment_method' => 'required|in:cash,card',
    //         'paid_by' => 'required|in:sender,receiver',

    //         'drop_latitude' => 'required|numeric',
    //        'drop_longitude' => 'required|numeric',  
    //     ], [
    //         // Custom Messages
    //         'pickup_location.required' => 'Pickup location is required.',
    //         'drop_location.required' => 'Drop location is required.',
    //         'distance.required' => 'Distance is required.',
    //         'time.required' => 'Time is required.',
    //         'trip_type.required' => 'Trip type is required.',
    //         'trip_type.in' => 'Trip type must be incity or intercity.',

    //         'sender_name.required' => 'Sender name is required.',
    //         'sender_phone.required' => 'Sender phone is required.',

    //         'receiver_name.required' => 'Receiver name is required.',
    //         'receiver_phone.required' => 'Receiver phone is required.',

    //         'package_size.required' => 'Package size is required.',
    //         'package_size.in' => 'Package size must be small, medium or large.',

    //         'payment_method.required' => 'Payment method is required.',
    //         'payment_method.in' => 'Payment method must be cash or card.',

    //         'paid_by.required' => 'Paid by field is required.',
    //         'paid_by.in' => 'Paid by must be sender or receiver.',

    //         'suggested_price.numeric' => 'Suggested price must be a number.',

    //         'drop_latitude.required' => 'Drop latitude is required.',
    //         'drop_longitude.required' => 'Drop longitude is required.',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => $validator->errors()->first()
    //         ], status: 201);
    //     }

    //     $courier = CourierRequest::create([
    //         'user_id' => $user->id,
    //         'pickup_location' => $request->pickup_location,
    //         'drop_location' => $request->drop_location,
    //         'distance' => $request->distance,
    //         'time' => $request->time,
    //         'trip_type' => $request->trip_type,

    //         'sender_name' => $request->sender_name,
    //         'sender_phone' => $request->sender_phone,
    //         'sender_landmark' => $request->sender_landmark,

    //         'receiver_name' => $request->receiver_name,
    //         'receiver_phone' => $request->receiver_phone,
    //         'receiver_landmark' => $request->receiver_landmark,

    //         'package_description' => $request->package_description,
    //         'package_size' => $request->package_size,
    //         'instruction' => $request->instruction,

    //         'suggested_price' => $request->suggested_price,
    //         'payment_method' => $request->payment_method,
    //         'paid_by' => $request->paid_by,

    //         'status' => 'pending',

    //         // expiry after 30 minutes
    //         'expires_at' => now()->addMinutes(30),

    //         'drop_latitude' => $request->drop_latitude,
    //         'drop_longitude' => $request->drop_longitude,
    //     ]);

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Courier request created successfully.',
    //         'data' => $courier
    //     ]);
    // }

    // with notification

    public function create(Request $request)
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized.'
            ], 401);
        }

        if ($user->is_online == 1) {
            return response()->json([
                'status' => false,
                'message' => 'You are online as courier. Go offline to create courier request.'
            ],403);
        }

        // ✅ Validation with Custom Messages
        $validator = Validator::make($request->all(), [
            'pickup_location' => 'required|string',
            'drop_location' => 'required|string',
            'distance' => 'required|string',
            'time' => 'required|string',
            'trip_type' => 'required|in:incity,intercity',

            'sender_name' => 'required|string',
            'sender_phone' => 'required|string',
            'sender_landmark' => 'nullable|string',

            'receiver_name' => 'required|string',
            'receiver_phone' => 'required|string',
            'receiver_landmark' => 'nullable|string',

            'package_description' => 'nullable|string',
            'package_size' => 'required|in:small,medium,large',
            'instruction' => 'nullable|string',

            'suggested_price' => 'nullable|numeric',
            'payment_method' => 'required|in:cash,card',
            'paid_by' => 'required|in:sender,receiver',

            'drop_latitude' => 'required|numeric',
           'drop_longitude' => 'required|numeric',  
        ], [
            // Custom Messages
            'pickup_location.required' => 'Pickup location is required.',
            'drop_location.required' => 'Drop location is required.',
            'distance.required' => 'Distance is required.',
            'time.required' => 'Time is required.',
            'trip_type.required' => 'Trip type is required.',
            'trip_type.in' => 'Trip type must be incity or intercity.',

            'sender_name.required' => 'Sender name is required.',
            'sender_phone.required' => 'Sender phone is required.',

            'receiver_name.required' => 'Receiver name is required.',
            'receiver_phone.required' => 'Receiver phone is required.',

            'package_size.required' => 'Package size is required.',
            'package_size.in' => 'Package size must be small, medium or large.',

            'payment_method.required' => 'Payment method is required.',
            'payment_method.in' => 'Payment method must be cash or card.',

            'paid_by.required' => 'Paid by field is required.',
            'paid_by.in' => 'Paid by must be sender or receiver.',

            'suggested_price.numeric' => 'Suggested price must be a number.',

            'drop_latitude.required' => 'Drop latitude is required.',
            'drop_longitude.required' => 'Drop longitude is required.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], status: 201);
        }

        $courier = CourierRequest::create([
            'user_id' => $user->id,
            'pickup_location' => $request->pickup_location,
            'drop_location' => $request->drop_location,
            'distance' => $request->distance,
            'time' => $request->time,
            'trip_type' => $request->trip_type,

            'sender_name' => $request->sender_name,
            'sender_phone' => $request->sender_phone,
            'sender_landmark' => $request->sender_landmark,

            'receiver_name' => $request->receiver_name,
            'receiver_phone' => $request->receiver_phone,
            'receiver_landmark' => $request->receiver_landmark,

            'package_description' => $request->package_description,
            'package_size' => $request->package_size,
            'instruction' => $request->instruction,

            'suggested_price' => $request->suggested_price,
            'payment_method' => $request->payment_method,
            'paid_by' => $request->paid_by,

            'status' => 'pending',

            // expiry after 30 minutes
            'expires_at' => now()->addMinutes(30),

            'drop_latitude' => $request->drop_latitude,
            'drop_longitude' => $request->drop_longitude,
        ]);

             // ✅ Get Online Drivers (IMPORTANT)
       $drivers = User::where('is_online', 1)
        ->where('courier_doc_status', 'approved')
        ->whereNotNull('device_token')
        ->get();


        Log::info("🚗 Drivers Found: " . $drivers->count());

        if ($drivers->count() > 0) {

            $tokens = [];

            foreach ($drivers as $driver) {
                $tokens[] = [
                    'device_token' => $driver->device_token,
                    'device_type'  => $driver->device_type ?? 'android',
                    'user_id'      => $driver->id,
                ];
            }

            $fcmService = new FCMService();

            $imageUrl = null;

            if (!empty($user->image)) {
                $imageUrl = asset('assets/profile_image/' . $user->image);
            }

            $fcmService->sendCourierNotification($tokens, [
                'notification_type' => 15,
                'title' => 'New Courier Request',
                'body' => $user->name . ' created a courier request.',
                'user_image' => $imageUrl,

                // FULL DATA
                'courier_id' => $courier->id,
                'pickup_location' => $courier->pickup_location,
                'drop_location' => $courier->drop_location,
                'distance' => $courier->distance,
                'time' => $courier->time,
                'trip_type' => $courier->trip_type,
                'sender_name' => $courier->sender_name,
                'sender_phone' => $courier->sender_phone,
                'receiver_name' => $courier->receiver_name,
                'receiver_phone' => $courier->receiver_phone,
                'package_size' => $courier->package_size,
                'suggested_price' => $courier->suggested_price,
                'payment_method' => $courier->payment_method,
                'paid_by' => $courier->paid_by,
                'drop_latitude' => $courier->drop_latitude,
                'drop_longitude' => $courier->drop_longitude,
                'expires_at' => $courier->expires_at,
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Courier request created successfully.',
            'data' => $courier
        ]);
    }

    // ✅ Online Courier Driver - List Requests (Only last 30 mins)

    public function listForDrivers(Request $request)
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized.'
            ], 401);
        }

        if ($user->is_online != 1) {
            return response()->json([
                'status' => false,
                'message' => 'You are offline. Go online to see courier requests.'
            ],403);
        }

        if ($user->courier_doc_status != 'approved') {
            return response()->json([
                'status' => false,
                'message' => 'Courier documents not approved yet.'
            ],403);
        }

        $type = $request->type;

        $query = CourierRequest::with('sender');

            /*
        |--------------------------------------------------------------------------
        | ✅ AUTOMATION FILTER (WALK / VEHICLE LOGIC)
        |--------------------------------------------------------------------------
        */

       if ($user->delivery_mode == 'walk') {

            $query->where('trip_type', '!=', 'incity')
                ->whereRaw("
                    CAST(REPLACE(distance,'km','') AS DECIMAL(10,2)) <= 10
                ");
        }

        /* ---------- FILTER LOGIC ---------- */

        if ($type == 'searching') {

            $query->where('status','pending')
                ->where('expires_at','>=',now());

        } elseif ($type == 'accepted') {

            $query->where('status','accepted')
                ->where('accepted_driver_id',$user->id);

        } elseif ($type == 'in_transit') {

            $query->where('status','in_transit')
                ->where('accepted_driver_id',$user->id);

        } elseif ($type == 'completed') {

            $query->where('status','completed')
                ->where('accepted_driver_id',$user->id);

        } else {

            // default = show all relevant to driver
            $query->where(function($q) use ($user){
                $q->where(function($sub){
                    $sub->where('status','pending')
                        ->where('expires_at','>=',now());
                })
                ->orWhere(function($sub) use ($user){
                    $sub->whereIn('status',['accepted','in_transit','completed'])
                        ->where('accepted_driver_id',$user->id);
                });
            });
        }

        $requests = $query->latest()->get();

        return response()->json([
            'status' => true,
            'message' => 'Courier requests fetched successfully.',
            'data' => $requests
        ]);
    }

    public function detailForDriver($id)
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized.'
            ], 401);
        }

        if ($user->is_online != 1) {
            return response()->json([
                'status' => false,
                'message' => 'You are offline. Go online to see courier details.'
            ], 403);
        }

        if ($user->courier_doc_status != 'approved') {
            return response()->json([
                'status' => false,
                'message' => 'Courier documents not approved yet.'
            ], 403);
        }

        $courier = CourierRequest::with([
                'sender',
                'acceptedDriver' // ✅ Driver info added
            ])
            ->where(function ($q) use ($user) {

                $q->where(function ($sub) {
                    $sub->where('status', 'pending')
                        ->where('expires_at', '>=', now());
                })
                ->orWhere(function ($sub) use ($user) {
                    $sub->whereIn('status', ['accepted','in_transit','completed'])
                        ->where('accepted_driver_id', $user->id);
                });

            })
            ->find($id);

        if (!$courier) {
            return response()->json([
                'status' => false,
                'message' => 'Courier request not found or not accessible.'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Courier detail fetched successfully.',
            'data' => $courier
        ]);
    }


    // ✅ Courier Driver Show Interest with Price
    // public function showInterest(Request $request, $courier_request_id)
    // {
    //      $user = Auth::guard('api')->user();
    //     if (!$user) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Unauthorized.'
    //         ], 401);
    //     }

    //     if ($user->is_online != 1) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'You must be online to send interest.'
    //         ]);
    //     }

    //     if ($user->courier_doc_status != 'approved') {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Courier documents not approved yet.'
    //         ]);
    //     }

    //     $courierRequest = CourierRequest::where('id', $courier_request_id)
    //         ->where('status', 'pending')
    //         ->where('expires_at', '>=', now())
    //         ->first();

    //     if (!$courierRequest) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Courier request not found or expired.'
    //         ]);
    //     }

    //     $validator = Validator::make($request->all(), [
    //         'driver_price' => 'required|numeric',
    //         'message' => 'nullable|string'
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => $validator->errors()->first()
    //         ],201);
    //     }

    //     $interest = CourierRequestDriverInterest::updateOrCreate(
    //         [
    //             'courier_request_id' => $courier_request_id,
    //             'driver_id' => $user->id,
    //         ],
    //         [
    //             'driver_price' => $request->driver_price,
    //             'message' => $request->message
    //         ]
    //     );

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Interest sent successfully.',
    //         'data' => $interest
    //     ]);
    // }


    // with notification

    public function showInterest(Request $request, $courier_request_id)
    {
         $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized.'
            ], 401);
        }

        if ($user->is_online != 1) {
            return response()->json([
                'status' => false,
                'message' => 'You must be online to send interest.'
            ]);
        }

        if ($user->courier_doc_status != 'approved') {
            return response()->json([
                'status' => false,
                'message' => 'Courier documents not approved yet.'
            ]);
        }

        $courierRequest = CourierRequest::where('id', $courier_request_id)
            ->where('status', 'pending')
            ->where('expires_at', '>=', now())
            ->first();

        if (!$courierRequest) {
            return response()->json([
                'status' => false,
                'message' => 'Courier request not found or expired.'
            ]);
        }

        $validator = Validator::make($request->all(), [
            'driver_price' => 'required|numeric',
            'message' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ],201);
        }

        $interest = CourierRequestDriverInterest::updateOrCreate(
            [
                'courier_request_id' => $courier_request_id,
                'driver_id' => $user->id,
            ],
            [
                'driver_price' => $request->driver_price,
                'message' => $request->message
            ]
        );

        // ===============================
        // 🔔 SEND NOTIFICATION TO REQUEST CREATOR
        // ===============================

        $requestOwner = User::find($courierRequest->user_id);

        if ($requestOwner && !empty($requestOwner->device_token)) {

            Log::info("📲 Sending interest notification to user", [
                'user_id' => $requestOwner->id
            ]);

            $driverImage = null;

            if (!empty($user->image)) {
                $driverImage = asset('assets/profile_image/' . $user->image);
            }

            $tokens[] = [
                'device_token' => $requestOwner->device_token,
                'device_type'  => $requestOwner->device_type ?? 'android',
                'user_id'      => $requestOwner->id,
            ];

            $fcmService = new FCMService();

            $fcmService->sendCourierNotification($tokens, [

                'notification_type' => 16,
                'title' => 'Driver Interested',
                'body' => $user->name . ' is interested in your courier request.',

                // DRIVER DATA
                'driver_id' => $user->id,
                'driver_name' => $user->name,
                'driver_phone' => $user->phone_number,
                'driver_price' => $interest->driver_price,
                'driver_message' => $interest->message,
                'driver_image' => $driverImage,

                // COURIER FULL DATA
                'courier_id' => $courierRequest->id,
                'pickup_location' => $courierRequest->pickup_location,
                'drop_location' => $courierRequest->drop_location,
                'distance' => $courierRequest->distance,
                'time' => $courierRequest->time,
                'trip_type' => $courierRequest->trip_type,
                'sender_name' => $courierRequest->sender_name,
                'sender_phone' => $courierRequest->sender_phone,
                'receiver_name' => $courierRequest->receiver_name,
                'receiver_phone' => $courierRequest->receiver_phone,
                'package_size' => $courierRequest->package_size,
                'suggested_price' => $courierRequest->suggested_price,
                'payment_method' => $courierRequest->payment_method,
                'paid_by' => $courierRequest->paid_by,
                'drop_latitude' => $courierRequest->drop_latitude,
                'drop_longitude' => $courierRequest->drop_longitude,
                'expires_at' => $courierRequest->expires_at,
            ]);

            Log::info("✅ Interest notification sent successfully.");
        } else {
            Log::info("❌ User device token not found.");
        }

        return response()->json([
            'status' => true,
            'message' => 'Interest sent successfully.',
            'data' => $interest
        ]);
    }

    // ✅ Sender View Interests on His Request
    public function myRequestInterests($courier_request_id)
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized.'
            ], 401);
        }

        $courierRequest = CourierRequest::where('id', $courier_request_id)
            ->where('user_id', $user->id)
            ->first();

        if (!$courierRequest) {
            return response()->json([
                'status' => false,
                'message' => 'Courier request not found.'
            ]);
        }

        // $interests = CourierRequestDriverInterest::with('driver')
        //     ->where('courier_request_id', $courier_request_id)
        //     ->latest()
        //     ->get();

         $interests = CourierRequestDriverInterest::with([
            'driver.vehicle' 
        ])
        ->where('courier_request_id', $courier_request_id)
        ->latest()
        ->get();

        return response()->json([
            'status' => true,
            'message' => 'Interests fetched successfully.',
            'data' => $interests
        ]);
    }

    // ✅ Sender Accept Any Driver Interest (Delete others)
    public function acceptDriver(Request $request, $courier_request_id)
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized.'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'driver_id' => 'required|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ],201);
        }

        $courierRequest = CourierRequest::where('id', $courier_request_id)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if (!$courierRequest) {
            return response()->json([
                'status' => false,
                'message' => 'Courier request not found or already accepted.'
            ]);
        }

        $interest = CourierRequestDriverInterest::where('courier_request_id', $courier_request_id)
            ->where('driver_id', $request->driver_id)
            ->first();

        if (!$interest) {
            return response()->json([
                'status' => false,
                'message' => 'This driver has not shown interest.'
            ]);
        }

        $courierRequest->accepted_driver_id = $request->driver_id;
        $courierRequest->status = 'accepted';
        $courierRequest->save();

        // delete other interests automatically
        CourierRequestDriverInterest::where('courier_request_id', $courier_request_id)
            ->where('driver_id', '!=', $request->driver_id)
            ->delete();


            // =========================================
            // 🔔 SEND NOTIFICATION TO ACCEPTED DRIVER
            // =========================================

            $driver = User::find($request->driver_id);

            if ($driver && !empty($driver->device_token)) {

                $userImage = null;

                if (!empty($user->image)) {
                    $userImage = asset('assets/profile_image/' . $user->image);
                }

                $tokens[] = [
                    'device_token' => $driver->device_token,
                    'device_type'  => $driver->device_type ?? 'android',
                    'user_id'      => $driver->id,
                ];

                $fcmService = new FCMService();

                $fcmService->sendCourierNotification($tokens, [

                    'notification_type' => 17,
                    'title' => 'Request Accepted 🎉',
                    'body' => 'Your interest has been accepted by ' . $user->name,

                    // USER (REQUEST CREATOR) DATA
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'user_phone' => $user->phone_number,
                    'user_image' => $userImage,

                    // COURIER FULL DATA
                    'courier_id' => $courierRequest->id,
                    'pickup_location' => $courierRequest->pickup_location,
                    'drop_location' => $courierRequest->drop_location,
                    'distance' => $courierRequest->distance,
                    'time' => $courierRequest->time,
                    'trip_type' => $courierRequest->trip_type,
                    'sender_name' => $courierRequest->sender_name,
                    'sender_phone' => $courierRequest->sender_phone,
                    'receiver_name' => $courierRequest->receiver_name,
                    'receiver_phone' => $courierRequest->receiver_phone,
                    'package_size' => $courierRequest->package_size,
                    'suggested_price' => $courierRequest->suggested_price,
                    'driver_price' => $interest->driver_price,
                    'payment_method' => $courierRequest->payment_method,
                    'paid_by' => $courierRequest->paid_by,
                    'drop_latitude' => $courierRequest->drop_latitude,
                    'drop_longitude' => $courierRequest->drop_longitude,
                ]);

                Log::info("📲 Acceptance notification sent to driver.");
            } else {
                Log::info("❌ Driver device token missing.");
            }

        return response()->json([
            'status' => true,
            'message' => 'Driver accepted successfully.',
            'data' => $courierRequest
        ]);
    }

    public function updateDeliveryStatus(Request $request, $courier_request_id)
    {
         $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized.'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:in_transit,completed'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $courierRequest = CourierRequest::where('id', $courier_request_id)
            ->where('accepted_driver_id', $user->id)
            ->first();

        if (!$courierRequest) {
            return response()->json([
                'status' => false,
                'message' => 'Courier request not found or not assigned to you.'
            ]);
        }

        // 🔒 Status Flow Protection
        if ($request->status == 'in_transit' && $courierRequest->status != 'accepted') {
            return response()->json([
                'status' => false,
                'message' => 'Order must be accepted before going in transit.'
            ]);
        }

        if ($request->status == 'completed' && $courierRequest->status != 'in_transit') {
            return response()->json([
                'status' => false,
                'message' => 'Order must be in transit before completing.'
            ]);
        }

        $courierRequest->status = $request->status;
        $courierRequest->save();

        // ===================================
        // 🔔 SEND NOTIFICATION TO USER
        // ===================================

        $requestOwner = User::find($courierRequest->user_id);

        if ($requestOwner && !empty($requestOwner->device_token)) {

            $driverImage = null;

            if (!empty($user->image)) {
                $driverImage = asset('assets/profile_image/' . $user->image);
            }

            $tokens[] = [
                'device_token' => $requestOwner->device_token,
                'device_type'  => $requestOwner->device_type ?? 'android',
                'user_id'      => $requestOwner->id,
            ];

            $title = '';
            $body = '';
            $notificationType = 0;

            if ($request->status == 'in_transit') {
                $title = 'Order In Transit 🚚';
                $body = 'Your parcel is on the way!';
                $notificationType = 18;
            }

            if ($request->status == 'completed') {
                $title = 'Order Delivered ✅';
                $body = 'Your parcel has been delivered successfully.';
                $notificationType = 19;
            }

            $fcmService = new FCMService();

            $fcmService->sendCourierNotification($tokens, [

                'notification_type' => $notificationType,
                'title' => $title,
                'body' => $body,

                // DRIVER DATA
                'driver_id' => $user->id,
                'driver_name' => $user->name,
                'driver_phone' => $user->phone_number,
                'driver_image' => $driverImage,

                // COURIER FULL DATA
                'courier_id' => $courierRequest->id,
                'pickup_location' => $courierRequest->pickup_location,
                'drop_location' => $courierRequest->drop_location,
                'distance' => $courierRequest->distance,
                'time' => $courierRequest->time,
                'trip_type' => $courierRequest->trip_type,
                'sender_name' => $courierRequest->sender_name,
                'sender_phone' => $courierRequest->sender_phone,
                'receiver_name' => $courierRequest->receiver_name,
                'receiver_phone' => $courierRequest->receiver_phone,
                'package_size' => $courierRequest->package_size,
                'suggested_price' => $courierRequest->suggested_price,
                'payment_method' => $courierRequest->payment_method,
                'paid_by' => $courierRequest->paid_by,
                'drop_latitude' => $courierRequest->drop_latitude,
                'drop_longitude' => $courierRequest->drop_longitude,
            ]);

            Log::info("📲 Status notification sent to user.");
        } else {
            Log::info("❌ User device token missing.");
        }

        return response()->json([
            'status' => true,
            'message' => 'Status updated successfully.',
            'data' => $courierRequest
        ]);
    }


    // list of courier sender side 

    public function senderRequests(Request $request)
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized.'
            ], 401);
        }

        $type = $request->type;

        $query = CourierRequest::with('sender')
            ->where('user_id', $user->id);

        /* -------- FILTER -------- */

        if ($type && in_array($type, ['pending','accepted','in_transit','completed'])) {
            $query->where('status', $type);
        }

        /* -------- FETCH -------- */

        $requests = $query->latest()->get();

        return response()->json([
            'status' => true,
            'message' => 'Sender requests fetched successfully.',
            'data' => $requests
        ]);
    }


        public function senderRequestDetail($id)
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized.'
            ], 401);
        }

        $courier = CourierRequest::with([
                'sender',
                
            ])
            ->where('user_id', $user->id) // VERY IMPORTANT (security)
            ->where('id', $id)
            ->first();

        if (!$courier) {
            return response()->json([
                'status' => false,
                'message' => 'Courier request not found.'
            ], 404);
        }

        
        return response()->json([
            'status' => true,
            'message' => 'Courier detail fetched successfully.',
            'data' => $courier
        ]);
    }


}
