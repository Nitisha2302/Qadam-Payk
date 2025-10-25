<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ride;
use App\Models\RideBooking;
use App\Models\ParcelBooking;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon; // ✅ Add this line
use App\Services\FCMService;
use App\Models\UserLang;

class BookingController extends Controller
{
    public function bookRideOrParcel(Request $request)
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' =>  __('messages.bookRideOrParcel.user_not_authenticated'),
            ], 401);
        }

        // 🔹 Detect user's preferred language from UserLang table
        $userLang = UserLang::where('user_id', $user->id)
            ->where('device_id', $user->device_id)
            ->where('device_type', $user->device_type)
            ->first();

        $lang = $userLang->language ?? 'ru'; // fallback to Russian
        app()->setLocale($lang);

        // ✅ Validation
        $validator = Validator::make($request->all(), [
            'ride_id'      => 'required|exists:rides,id',
            'seats_booked' => 'required_if:type,0|integer|min:1', // required only for rides
            'services'     => 'nullable|array',
            'services.*'   => 'exists:services,id',
            'type'         => 'required|in:0,1', // 0 = ride, 1 = parcel
            'comment'      => 'nullable|string|max:2000', // ✅ new validation
        ], [
            'ride_id.required'      => __('messages.bookRideOrParcel.validation.ride_id_required'),
            'ride_id.exists'        => __('messages.bookRideOrParcel.validation.ride_not_exist'),
            'seats_booked.required_if' => __('messages.bookRideOrParcel.validation.seats_required'),
            'seats_booked.integer'  => __('messages.bookRideOrParcel.validation.seats_invalid'),
            'seats_booked.min'      => __('messages.bookRideOrParcel.validation.seats_min'),
            'services.array'        => __('messages.bookRideOrParcel.validation.services_array'),
            'services.*.exists'     => __('messages.bookRideOrParcel.validation.service_invalid'),
            'type.required'         => __('messages.bookRideOrParcel.validation.type_required'),
            'type.in'               => __('messages.bookRideOrParcel.validation.type_invalid'),
            'comment.string'        => __('messages.bookRideOrParcel.validation.comment_invalid'),
            'comment.max'           => __('messages.bookRideOrParcel.validation.comment_max'),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first()
            ], 201);
        }

        // Find the ride
        $ride = \App\Models\Ride::find($request->ride_id);

        // Prevent user from booking their own ride or parcel
        if ($ride->user_id == $user->id) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.bookRideOrParcel.validation.cannot_book_own'),
            ], 201);
        }

        // Check existing bookings for this ride by the user
        $bookedRide   = \App\Models\RideBooking::where('ride_id', $ride->id)
            ->where('user_id', $user->id)
            ->where('type', 0)   // 0 = ride
            ->first();

        $bookedParcel = \App\Models\RideBooking::where('ride_id', $ride->id)
            ->where('user_id', $user->id)
            ->where('type', 1)  // 1 = parcel
            ->first();

        // If user already booked both ride and parcel, do not allow booking
        if ($bookedRide && $bookedParcel) {
            return response()->json([
                'status'  => false,
                'message' =>  __('messages.bookRideOrParcel.validation.already_booked_both'),
            ], 201);
        }

        // Now, allow booking **only for the missing type**
        // For example, if $request->type == 0 (ride) but $bookedRide exists, do not allow ride booking again
        if (($request->type == 0 && $bookedRide) || ($request->type == 1 && $bookedParcel)) {
            return response()->json([
                'status'  => false,
                'message' => $request->type == 0 
                    ?  __('messages.bookRideOrParcel.validation.already_booked_ride')
                    :  __('messages.bookRideOrParcel.validation.already_booked_parcel')
            ], 201);
        }


        //Start by anukool
        // $ride = \App\Models\Ride::find($request->ride_id);

        // // ❌ Prevent user from booking their own ride
        // if ($ride->user_id == $user->id) {
        //     return response()->json([
        //         'status'  => false,
        //         'message' => 'You cannot book your own ride.'
        //     ], 201);
        // }

        // // ❌ Prevent duplicate bookings by same user
        // $existingBooking = \App\Models\RideBooking::where('ride_id', $ride->id)
        //     ->where('user_id', $user->id)
        //     ->first();

        // if ($existingBooking) {
        //     return response()->json([
        //         'status'  => false,
        //         'message' => 'You have already booked this ride.'
        //     ], 201);
        // }

        //End by anukool

        // if ($request->type == 0) { // Ride booking
        //     $availableSeats = $ride->number_of_seats - $ride->bookings()->sum('seats_booked');
        //     $seatsBooked = $request->seats_booked ?? 1;

        //     if ($seatsBooked > $availableSeats) {
        //         return response()->json([
        //             'status'  => false,
        //             'message' => 'Not enough seats available'
        //         ], 201);
        //     }

        //     $totalPrice = $ride->price * $seatsBooked;

        // } else { // Parcel booking
        //     $seatsBooked = 1; // Parcel usually counts as 1
        //     $totalPrice = $ride->price;
        // }

        $seatsBooked = $request->type == 0 ? ($request->seats_booked ?? 1) : 1;
        $totalPrice = $ride->price * $seatsBooked;

        // ✅ Create booking
        $booking = \App\Models\RideBooking::create([
            'ride_id'      => $ride->id,
            'user_id'      => $user->id,
            'seats_booked' => $seatsBooked,
            'price'        => $totalPrice,
            'services'     => $request->services ?? [],
            'status'       => 'pending',
            'type'         => $request->type,
            'ride_date'    => Carbon::parse($ride->ride_date)->format('Y-m-d'), // copy from ride
            'ride_time'    => $ride->ride_time, // copy from ride
             'comment'      => $request->comment,
        ]);

        // Notify driver
        $driver = $ride->driver;
        $passengerName = $user->name ?: 'A passenger'; 
         if ($driver && $driver->device_token) {

            // ✅ Find driver's language
            $driverLang = UserLang::where('user_id', $driver->id)
                ->where('device_id', $driver->device_id)
                ->where('device_type', $driver->device_type)
                ->first();

            $driverLocale = $driverLang->language ?? 'ru';
            $originalLocale = app()->getLocale();
            app()->setLocale($driverLocale);

            $notificationData = [
                'notification_type' => 1,
                'title' => __('messages.bookRideOrParcel.notification.title'),
                'body'  => __('messages.bookRideOrParcel.notification.body', [
                    'passenger' => $passengerName,
                    'pickup'    => $ride->pickup_location,
                    'destination' => $ride->destination,
                ]),
            ];

            $fcmService = new FCMService();
            $fcmService->sendNotification([[
                'device_token' => $driver->device_token,
                'device_type'  => $driver->device_type ?? 'android',
                'user_id'      => $driver->id,
            ]], $notificationData);

            // ✅ Restore original API language (user language)
            app()->setLocale($originalLocale);
        }



        return response()->json([
            'status'  => true,
            'message' => __('messages.bookRideOrParcel.booking_created'),
            'data'    => [
                'id'           => $booking->id,
                'ride_id'      => $booking->ride_id,
                'user_id'      => $booking->user_id,
                'seats_booked' => $booking->seats_booked,
                'price'        => $booking->price,
                'status'       => $booking->status,
                'type'         => $booking->type,
                'ride_date'    => $ride->ride_date, // copy from ride
                'ride_time'    => $ride->ride_time, // copy from ride
                'created_at'   => $booking->created_at,
                'updated_at'   => $booking->updated_at,
                 'comment'      => $request->comment,
                // ✅ Replace services with full details
                'services'     => $booking->services_details->map(function ($service) {
                    return [
                        'id'            => $service->id,
                        'service_name'  => $service->service_name,
                        'service_image' => $service->service_image,
                    ];
                })
            ]
        ], 200);
    }

    // api to show passengers list who request to book ride to driver (driver side)

    // new based on ride _id 

    public function getDriverBookings(Request $request)
    {
        $driver = Auth::guard('api')->user();
        if (!$driver) {
            return response()->json([
                'status'  => false,
                'message' =>  __('messages.getDriverBookings.driver_not_authenticated'),
            ], 401);
        }

        // 🔹 Detect user's preferred language from UserLang table
        $userLang = UserLang::where('user_id', $driver->id)
            ->where('device_id', $driver->device_id)
            ->where('device_type', $driver->device_type)
            ->first();

        $lang = $userLang->language ?? 'ru'; // fallback to Russian
        app()->setLocale($lang);

        // Validate optional ride_id parameter
        $rideId = $request->query('ride_id');

        $bookingsQuery = \App\Models\RideBooking::with(['user', 'ride'])
            ->whereHas('ride', function ($query) use ($driver) {
                $query->where('user_id', $driver->id); // rides belong to this driver
            });

        // If ride_id is provided, filter by it
        if ($rideId) {
            $bookingsQuery->where('ride_id', $rideId);
        }

        // Get bookings
        $bookings = $bookingsQuery->orderBy('created_at', 'desc')->get();

        // Format response
        $data = $bookings->map(function ($booking) {
            return [
                'booking_id'    => $booking->id,
                'ride_id'       => $booking->ride_id,
                'user_id'       => $booking->user_id,
                'user_name'     => $booking->user->name ?? null,
                'user_phone'    => $booking->user->phone_number ?? null,
                'user_image'    => $booking->user->image ?? null,
                'seats_booked'  => $booking->seats_booked,
                'price'         => $booking->price,
                'status'        => $booking->status,
                'type'          => $booking->type,
                'comment'          => $booking->comment,
                'services'      => $booking->services_details->map(function ($service) {
                    return [
                        'id'            => $service->id,
                        'service_name'  => $service->service_name,
                        'service_image' => $service->service_image,
                    ];
                }),
                'pickup_location' => $booking->ride->pickup_location ?? null,
                'destination'     => $booking->ride->destination ?? null,
                'ride_date'       => $booking->ride->ride_date ?? null,
                'ride_time'       => $booking->ride->ride_time ?? null,
                'accept_parcel'   => $booking->ride->accept_parcel ?? null,
                'created_at'      => $booking->created_at,
            ];
        });

        return response()->json([
            'status'  => true,
            'message' => __('messages.getDriverBookings.success'),
            'data'    => $data
        ], 200);
    }


    // api for showing all the passenger booked request (passenger side)

    public function getPassengerBookingRequests(Request $request)
    {
        $passenger = Auth::guard('api')->user();
        if (!$passenger) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.getPassengerBookingRequests.passenger_not_authenticated'),
            ], 401);
        }

        // 🔹 Detect user's preferred language from UserLang table
        $userLang = UserLang::where('user_id', $passenger->id)
            ->where('device_id', $passenger->device_id)
            ->where('device_type', $passenger->device_type)
            ->first();

        $lang = $userLang->language ?? 'ru'; // fallback to Russian
        app()->setLocale($lang);

        // Fetch bookings made by this passenger
        $bookings = \App\Models\RideBooking::with(['ride', 'ride.driver'])
            ->where('user_id', $passenger->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $data = $bookings->map(function ($booking) {
            return [
                'booking_id'      => $booking->id,
                'ride_id'         => $booking->ride_id,
                'driver_id'       => $booking->ride->user_id ?? null,
                'driver_name'     => $booking->ride->driver->name ?? null,
                'driver_phone'    => $booking->ride->driver->phone_number ?? null,
                'driver_image'    => $booking->ride->driver->image ?? null,
                'status'          => $booking->status,
                'seats_booked'    => $booking->seats_booked,
                'price'           => $booking->price,
                'type'            => $booking->type,
                'services'        => $booking->services_details->map(function ($service) {
                    return [
                        'id'            => $service->id,
                        'service_name'  => $service->service_name,
                        'service_image' => $service->service_image,
                    ];
                }),
                'pickup_location' => $booking->ride->pickup_location ?? null,
                'destination'     => $booking->ride->destination ?? null,
                'ride_date'       => $booking->ride->ride_date ?? null,
                'ride_time'       => $booking->ride->ride_time ?? null,
                'accept_parcel'   => $booking->ride->accept_parcel ?? null,
                'created_at'      => $booking->created_at,
            ];
        });

        return response()->json([
            'status'  => true,
            'message' => __('messages.getPassengerBookingRequests.success'),
            'data'    => $data
        ], 200);
    }


    public function confirmBooking(Request $request)
    {
        $driver = Auth::guard('api')->user();
        if (!$driver) {
            return response()->json([
                'status' => false,
               'message' => __('messages.confirmBooking.driver_not_authenticated')
            ], 401);
        }

        // 🔹 Detect user's preferred language from UserLang table
        $userLang = UserLang::where('user_id', $driver->id)
            ->where('device_id', $driver->device_id)
            ->where('device_type', $driver->device_type)
            ->first();

        $lang = $userLang->language ?? 'ru'; // fallback to Russian
        app()->setLocale($lang);

        // Validation
        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|exists:ride_bookings,id',
            'status'     => 'required|in:confirmed,cancelled',
        ], [
            'booking_id.required' => __('messages.confirmBooking.validation.booking_id_required'),
            'booking_id.exists'   => __('messages.confirmBooking.validation.booking_not_exist'),
            'status.required'     => __('messages.confirmBooking.validation.status_required'),
            'status.in'           => __('messages.confirmBooking.validation.status_invalid'),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first()
            ], 201);
        }

        $booking = RideBooking::with('ride')->find($request->booking_id);

        // Check driver authorization
        if (!$booking->ride || $booking->ride->user_id != $driver->id) {
            return response()->json([
                'status' => false,
                'message' => __('messages.confirmBooking.unauthorized')
            ], 201);
        }

        $ride = $booking->ride;

        if ($request->status === 'confirmed') {
            // Calculate available seats
            $confirmedSeats = $ride->bookings()->where('status', 'confirmed')->sum('seats_booked');
            $availableSeats = $ride->number_of_seats - $confirmedSeats;

            if ($booking->seats_booked > $availableSeats) {
                // Not enough seats for this booking -> cancel automatically
                $booking->status = 'cancelled';
                $booking->save();

                return response()->json([
                    'status'  => false,
                    'message' => __('messages.confirmBooking.not_enough_seats'),
                    'data'    => $booking
                ], 201);
            }

            // Enough seats -> confirm this booking
            $booking->status = 'confirmed';
            $booking->save();

            // Automatically cancel other pending bookings that exceed remaining seats
            $remainingSeats = $availableSeats - $booking->seats_booked;
            if ($remainingSeats <= 0) {
                $ride->bookings()
                    ->where('status', 'pending')
                    ->where('id', '!=', $booking->id)
                    ->update(['status' => 'cancelled']);
            }
        } else {
            // If manually cancelled by driver
            $booking->status = 'cancelled';
            $booking->save();
        }

        // Send notification to passenger
        $passenger = $booking->user; 
        // ✅ Send notification to passenger in THEIR own language
        if ($passenger && $passenger->device_token) {
            $fcmService = new \App\Services\FCMService();

            // 🔹 Detect passenger's preferred language
            $passengerLang = UserLang::where('user_id', $passenger->id)
                ->where('device_id', $passenger->device_id)
                ->where('device_type', $passenger->device_type)
                ->first();

            $recipientLocale = $passengerLang->language ?? 'ru'; // Default Russian if none found
            $originalLocale = app()->getLocale(); // Save current locale (driver's language)

            app()->setLocale($recipientLocale); // 🔄 Switch to passenger language temporarily
            // ✅ Define pickup & destination correctly
            $pickup = $booking->pickup_location ?? $ride->pickup_location;
            $destination = $booking->destination ?? $ride->destination;

            $notificationData = [
                'notification_type' => 2,
                'title' => __('messages.confirmBooking.notification.title', ['status' => $booking->status]),
                'body'  => __('messages.confirmBooking.notification.body', [
                    'pickup'      => $pickup,
                    'destination' => $destination,
                    'driver'      => $driver->name,
                    'status'      => $booking->status
                ]),
            ];

            // ✅ Send FCM notification
            $fcmService->sendNotification([
                [
                    'device_token' => $passenger->device_token,
                    'device_type'  => $passenger->device_type ?? 'android',
                    'user_id'      => $passenger->id,
                ]
            ], $notificationData);

            // 🔁 Restore locale back to driver's for API response
            app()->setLocale($originalLocale);
        }

        return response()->json([
            'status'  => true,
             'message' => __('messages.confirmBooking.success', ['status' => $booking->status]),
            'data'    => $booking
        ],200);
    }

    // public function updateBookingActiveStatus(Request $request)
    //     {
    //         // ✅ Get authenticated driver
    //         $driver = Auth::guard('api')->user();
    //         if (!$driver) {
    //             return response()->json([
    //                 'status'  => false,
    //                 'message' => 'User not authenticated.',
    //             ], 401);
    //         }


    //         // Validate input
    //         $validator = Validator::make($request->all(), [
    //             'booking_id' => 'required|exists:ride_bookings,id',
    //         ], [
    //             'booking_id.required' => 'Booking ID is required.',
    //             'booking_id.exists'   => 'Booking does not exist.',
    //         ]);

    //         if ($validator->fails()) {
    //             return response()->json([
    //                 'status'  => false,
    //                 'message' => $validator->errors()->first(),
    //             ], 422);
    //         }

    //         // Find the booking
    //         $booking = RideBooking::find($request->booking_id);
    //         if (!$booking) {
    //             return response()->json([
    //                 'status'  => false,
    //                 'message' => 'Booking not found.',
    //             ], 404);
    //         }


    //         // Update booking status to active
    //         $booking->active_status = '1';
    //         $booking->save();

    //     // ✅ Send notification to passenger
    //     $passenger = $booking->user;
    //     if ($passenger && $passenger->device_token) {
    //         $fcmService = new \App\Services\FCMService();

    //         $ride = $booking->ride;
    //         $pickup = $ride->pickup_location ?? '';
    //         $destination = $ride->destination ?? '';

    //         $notificationData = [
    //             'notification_type' => 2,
    //             'title' => "Booking Activated",
    //             'body'  => "Your booking for the ride from {$pickup} to {$destination} has been started.",
    //         ];

    //         $fcmService->sendNotification([
    //             [
    //                 'device_token' => $passenger->device_token,
    //                 'device_type'  => $passenger->device_type ?? 'android',
    //                 'user_id'      => $passenger->id,
    //             ]
    //         ], $notificationData);
    //     }

    //         return response()->json([
    //             'status'  => true,
    //             'message' => 'Booking status updated to active successfully.',
    //             'data'    => [
    //                 'booking_id'    => $booking->id,
    //                 'ride_id'       => $booking->ride_id,
    //                 'driver_id'     => $driver->id,
    //                 'active_status' => $booking->active_status,
    //             ],
    //         ], 200);
    // }


    // public function updateBookingCompleteStatus(Request $request)
    // {
    //     // ✅ Get authenticated driver
    //     $driver = Auth::guard('api')->user();
    //     if (!$driver) {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => 'User not authenticated.',
    //         ], 401);
    //     }

    //     // Validate input
    //     $validator = Validator::make($request->all(), [
    //         'booking_id' => 'required|exists:ride_bookings,id',
    //     ], [
    //         'booking_id.required' => 'Booking ID is required.',
    //         'booking_id.exists'   => 'Booking does not exist.',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => $validator->errors()->first(),
    //         ], 422);
    //     }

    //     // Find the booking
    //     $booking = RideBooking::find($request->booking_id);
    //     if (!$booking) {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => 'Booking not found.',
    //         ], 404);
    //     }

    //     // // Get the ride and check if the authenticated user is the driver
    //     // $ride = Ride::find($booking->ride_id);
    //     // if (!$ride || $ride->user_id != $driver->id) {
    //     //     return response()->json([
    //     //         'status'  => false,
    //     //         'message' => 'You are not authorized to complete this booking.',
    //     //     ], 403);
    //     // }

    //     // Update booking status to complete
    //     $booking->active_status = '2';
    //     $booking->save();

    //     // ✅ Send notification to passenger
    //     $passenger = $booking->user;
    //     if ($passenger && $passenger->device_token) {
    //         $fcmService = new \App\Services\FCMService();

    //         $ride = $booking->ride;
    //         $pickup = $ride->pickup_location ?? '';
    //         $destination = $ride->destination ?? '';

    //         $notificationData = [
    //             'notification_type' => 2,
    //             'title' => "Booking Completed",
    //             'body'  => "Your booking for the ride from {$pickup} to {$destination} has been  completed.",
    //         ];

    //         $fcmService->sendNotification([
    //             [
    //                 'device_token' => $passenger->device_token,
    //                 'device_type'  => $passenger->device_type ?? 'android',
    //                 'user_id'      => $passenger->id,
    //             ]
    //         ], $notificationData);
    //     }

    //     return response()->json([
    //         'status'  => true,
    //         'message' => 'Booking status updated to complete successfully.',
    //         'data'    => [
    //             'booking_id'    => $booking->id,
    //             'ride_id'       => $booking->ride_id,
    //             'driver_id'     => $driver->id,
    //             'active_status' => $booking->active_status,
    //         ],
    //     ], 200);
    // }



    // with notification correct 


    public function updateBookingActiveStatus(Request $request)
    {
        $driver = Auth::guard('api')->user();
        if (!$driver) {
            return response()->json([
                'status'  => false,
                 'message' => __('messages.updateBookingActiveStatus.driver_not_authenticated'),
            ], 401);
        }

        // 🔹 Detect user's preferred language from UserLang table
        $userLang = UserLang::where('user_id', $driver->id)
            ->where('device_id', $driver->device_id)
            ->where('device_type', $driver->device_type)
            ->first();

        $lang = $userLang->language ?? 'ru'; // fallback to Russian
        app()->setLocale($lang);

        $validator = Validator::make($request->all(), [
        'booking_id' => 'required|exists:ride_bookings,id',
        ], [
            'booking_id.required' => __('messages.updateBookingActiveStatus.validation.booking_id_required'),
            'booking_id.exists'   => __('messages.updateBookingActiveStatus.validation.booking_not_exist'),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ], 201);
        }

        $booking = RideBooking::find($request->booking_id);
        if (!$booking) {
            return response()->json([
                'status'  => false,
                 'message' => __('messages.updateBookingActiveStatus.booking_not_found'),
            ], 404);
        }

        // Determine driver & passenger correctly
        if ($booking->ride_id) {
            $ride = $booking->ride;
            $isDriverAuthorized = $ride && $ride->user_id == $driver->id;
            $passenger = $booking->user; // passenger is booking user
            $pickup = $ride?->pickup_location ?? '';
            $destination = $ride?->destination ?? '';
        } elseif ($booking->request_id) {
            $requestData = $booking->request;
            $isDriverAuthorized = $booking->user_id == $driver->id;
            $passenger = $requestData?->user; // passenger is request creator
            $pickup = $requestData?->pickup_location ?? '';
            $destination = $requestData?->destination ?? '';
        } else {
            return response()->json([
                'status'  => false,
                'message' => __('messages.updateBookingActiveStatus.invalid_structure'),
            ], 400);
        }

       

        if (!$isDriverAuthorized) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.updateBookingActiveStatus.unauthorized'),
            ], 403);
        }

        $booking->active_status = '1';
        $booking->save();
        
        // ✅ Send notification to passenger
        // if ($passenger && $passenger->device_token) {
        //     $fcmService = new \App\Services\FCMService();
        //     $notificationData = [
        //         'notification_type' => 2,
        //         'title' => "Booking Activated",
        //         'body'  => "Your booking for the ride from {$pickup} to {$destination} has been started.",
        //     ];

        //         //  $notificationData = [
        //         //     'notification_type' => 2,
        //         //     'title' => __('messages.updateBookingActiveStatus.notification.title'),
        //         //     'body'  => __('messages.updateBookingActiveStatus.notification.body', [
        //         //         'pickup' => $pickup,
        //         //         'destination' => $destination,
        //         //     ]),
        //         // ];

        //     $fcmService->sendNotification([[
        //         'device_token' => $passenger->device_token,
        //         'device_type'  => $passenger->device_type ?? 'android',
        //         'user_id'      => $passenger->id,
        //     ]], $notificationData);
        // }


        // ✅ Send notification to passenger (in their language)
        if ($passenger && $passenger->device_token) {

            // 🔹 Get passenger language
            $passengerLang = UserLang::where('user_id', $passenger->id)
                ->where('device_id', $passenger->device_id)
                ->where('device_type', $passenger->device_type)
                ->first();

            $passengerLocale = $passengerLang->language ?? 'ru'; // default if missing
            $originalLocale = app()->getLocale(); // store driver's locale to restore later

            // 🔹 Switch to passenger's language for notification
            app()->setLocale($passengerLocale);

            // 🔹 Prepare translated notification
            $notificationData = [
                'notification_type' => 2,
                'title' => __('messages.updateBookingActiveStatus.notification.title'),
                'body'  => __('messages.updateBookingActiveStatus.notification.body', [
                    'pickup'      => $pickup,
                    'destination' => $destination,
                ]),
            ];

            $fcmService = new \App\Services\FCMService();
            $fcmService->sendNotification([
                [
                    'device_token' => $passenger->device_token,
                    'device_type'  => $passenger->device_type ?? 'android',
                    'user_id'      => $passenger->id,
                ]
            ], $notificationData);

            // 🔹 Restore driver's original locale
            app()->setLocale($originalLocale);
        }

        return response()->json([
            'status'  => true,
           'message' => __('messages.updateBookingActiveStatus.success'),
            'data'    => [
                'booking_id'    => $booking->id,
                'ride_id'       => $booking->ride_id,
                'driver_id'     => $driver->id,
                'active_status' => $booking->active_status,
            ],
        ]);
    }


    public function updateBookingCompleteStatus(Request $request)
    {
        $driver = Auth::guard('api')->user();
        if (!$driver) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.updateBookingCompleteStatus.driver_not_authenticated'),
            ], 401);
        }

         // Detect language
        $userLang = UserLang::where('user_id', $driver->id)
            ->where('device_id', $driver->device_id)
            ->where('device_type', $driver->device_type)
            ->first();
        $lang = $userLang->language ?? 'ru';
        app()->setLocale($lang);

        // Validation
        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|exists:ride_bookings,id',
        ], [
            'booking_id.required' => __('messages.updateBookingCompleteStatus.validation.booking_id_required'),
            'booking_id.exists'   => __('messages.updateBookingCompleteStatus.validation.booking_not_exist'),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ], 201);
        }

        $booking = RideBooking::find($request->booking_id);
        if (!$booking) {
            return response()->json([
                 'message' => __('messages.updateBookingCompleteStatus.booking_not_found'),
            ], 404);
        }

        // Determine driver & passenger correctly
        if ($booking->ride_id) {
            $ride = $booking->ride;
            $isDriverAuthorized = $ride && $ride->user_id == $driver->id;
            $passenger = $booking->user;
            $pickup = $ride?->pickup_location ?? '';
            $destination = $ride?->destination ?? '';
        } elseif ($booking->request_id) {
            $requestData = $booking->request;
            $isDriverAuthorized = $booking->user_id == $driver->id;
            $passenger = $requestData?->user;
            $pickup = $requestData?->pickup_location ?? '';
            $destination = $requestData?->destination ?? '';
        } else {
            return response()->json([
                'status'  => false,
                'message' => __('messages.updateBookingCompleteStatus.invalid_structure'),
            ], 400);
        }

        if (!$isDriverAuthorized) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.updateBookingCompleteStatus.unauthorized'),
            ], 403);
        }

        $booking->active_status = '2';
        $booking->save();

        // ✅ Send notification to passenger
        // if ($passenger && $passenger->device_token) {
        //     $fcmService = new \App\Services\FCMService();
        //     $notificationData = [
        //         'notification_type' => 2,
        //         'title' => "Booking Completed",
        //         'body'  => "Your booking for the ride from {$pickup} to {$destination} has been completed.",
        //     ];

        //     // $notificationData = [
        //     //     'notification_type' => 2,
        //     //     'title' => __('messages.updateBookingCompleteStatus.notification.title'),
        //     //     'body'  => __('messages.updateBookingCompleteStatus.notification.body', [
        //     //         'pickup' => $pickup,
        //     //         'destination' => $destination,
        //     //     ]),
        //     // ];

        //     $fcmService->sendNotification([[
        //         'device_token' => $passenger->device_token,
        //         'device_type'  => $passenger->device_type ?? 'android',
        //         'user_id'      => $passenger->id,
        //     ]], $notificationData);
        // }


         //✅ Send notification to passenger in THEIR language
            if ($passenger && $passenger->device_token) {
                // Detect Passenger Language
                $passengerLang = UserLang::where('user_id', $passenger->id)
                    ->where('device_id', $passenger->device_id)
                    ->where('device_type', $passenger->device_type)
                    ->first();
                    
                $lang = $passengerLang->language ?? 'ru'; // fallback
                app()->setLocale($lang);

                $notificationData = [
                    'notification_type' => 2,
                    'title' => __('messages.updateBookingCompleteStatus.notification.title'),
                    'body'  => __('messages.updateBookingCompleteStatus.notification.body', [
                        'pickup' => $pickup,
                        'destination' => $destination,
                    ]),
                ];

                $fcmService = new \App\Services\FCMService();
                $fcmService->sendNotification([[
                    'device_token' => $passenger->device_token,
                    'device_type'  => $passenger->device_type ?? 'android',
                    'user_id'      => $passenger->id,
                ]], $notificationData);
            }

        return response()->json([
            'status'  => true,
            'message' => __('messages.updateBookingCompleteStatus.success'),
            'data'    => [
                'booking_id'    => $booking->id,
                'ride_id'       => $booking->ride_id,
                'driver_id'     => $driver->id,
                'active_status' => $booking->active_status,
            ],
        ]);
    }


    // code for get cancelled pending confirm ride of driver and passednger

    // Driver
    public function getDriverConfirmedPendingancelledRides(Request $request)
    {
        $driver = Auth::guard('api')->user();
        if (!$driver) {
            return response()->json(['status' => false, 'message' => 'Driver not authenticated'], 401);
        }

        $statusFilter = $request->query('status');

        // Determine statuses to fetch
        if (!$statusFilter) {
            $statuses = ['pending','confirmed','cancelled','declined'];
        } else {
            switch ($statusFilter) {
                case 'pending':    
                    $statuses = ['pending']; 
                    break;
                case 'complete':   
                    $statuses = ['confirmed']; 
                    break;
                case 'cancelled':  
                case 'declined':   // ✅ Treat declined same as cancelled
                    $statuses = ['cancelled','declined']; 
                    break;
                default:
                    return response()->json([
                        'status'=>false,
                        'message'=>'Invalid status. Use pending, complete, cancelled, declined or leave empty.'
                    ], 400);
            }
        }

        // RideBookings where driver owns the ride
        $rideBookings = RideBooking::with(['user','ride'])
            ->whereHas('ride', fn($q) => $q->where('user_id',$driver->id))
            ->whereIn('status',$statuses)
            ->orderByDesc('created_at')
            ->get();

        // PassengerRequests assigned to driver
        $passengerRequests = \App\Models\PassengerRequest::with('user')
            ->where('driver_id',$driver->id)
            ->whereIn('status',$statuses)
            ->orderByDesc('created_at')
            ->get();

        $data = $rideBookings->concat($passengerRequests)
            ->sortByDesc(fn($item)=> data_get($item,'ride_date'))
            ->map(fn($item) => [
                'source' => $item instanceof RideBooking ? 'ride_booking' : 'passenger_request',
                'booking_id' => $item->id,
                'pickup_location' => data_get($item,'ride.pickup_location',$item->pickup_location),
                'destination' => data_get($item,'ride.destination',$item->destination),
                'ride_date' => data_get($item,'ride.ride_date',$item->ride_date),
                'ride_time' => data_get($item,'ride.ride_time',$item->ride_time),
                'price' => $item instanceof RideBooking ? $item->price : $item->budget,
                // ✅ Add number of passengers / seats booked
                'seats_booked' => $item instanceof RideBooking ? $item->seats_booked : 1,

                // ✅ Add services
                'services' => $item instanceof RideBooking
                    ? Service::whereIn('id', $item->services ?? [])->get(['id','service_name','service_image'])
                    : Service::whereIn('id', $item->services ?? [])->get(['id','service_name','service_image']),
                'status' => $item->status,
                'passenger_id' => data_get($item,'user.id'),
                'passenger_name' => data_get($item,'user.name'),
                'passenger_phone' => data_get($item,'user.phone_number'),
                'passenger_image' => data_get($item,'user.image'),
            ])->values();

        return response()->json([
            'status'=>true,
            'message'=>'Driver rides fetched successfully',
            'data'=>$data
        ],200);
    }


    // Passenger
    public function getPassengerConfirmedPendingCanclledRides(Request $request)
    {
        $passenger = Auth::guard('api')->user();
        if (!$passenger) {
            return response()->json(['status' => false, 'message' => 'Passenger not authenticated'], 401);
        }

        $statusFilter = $request->query('status');

        if (!$statusFilter) {
            $statuses = ['pending','confirmed','cancelled','declined'];
        } else {
            switch ($statusFilter) {
                case 'pending':    
                    $statuses = ['pending']; 
                    break;
                case 'complete':   
                    $statuses = ['confirmed']; 
                    break;
                case 'cancelled':  
                case 'declined':   // ✅ Treat declined same as cancelled
                    $statuses = ['cancelled','declined']; 
                    break;
                default:
                    return response()->json([
                        'status'=>false,
                        'message'=>'Invalid status. Use pending, complete, cancelled, declined or leave empty.'
                    ], 400);
            }
        }

        // RideBookings by passenger
        $rideBookings = RideBooking::with(['ride','ride.driver'])
            ->where('user_id',$passenger->id)
            ->whereIn('status',$statuses)
            ->orderByDesc('created_at')
            ->get();

        // PassengerRequests by passenger
        $passengerRequests = \App\Models\PassengerRequest::with('driver')
            ->where('user_id',$passenger->id)
            ->whereIn('status',$statuses)
            ->orderByDesc('created_at')
            ->get();

        $data = $rideBookings->concat($passengerRequests)
            ->sortByDesc(fn($item)=> data_get($item,'ride_date'))
            ->map(fn($item) => [
                'source' => $item instanceof RideBooking ? 'ride_booking' : 'passenger_request',
                'booking_id' => $item->id,
                'pickup_location' => data_get($item,'ride.pickup_location',$item->pickup_location),
                'destination' => data_get($item,'ride.destination',$item->destination),
                'ride_date' => data_get($item,'ride.ride_date',$item->ride_date),
                'ride_time' => data_get($item,'ride.ride_time',$item->ride_time),
                'price' => $item instanceof RideBooking ? $item->price : $item->budget,
                'status' => $item->status,
                 // ✅ Add number of passengers / seats booked
                'seats_booked' => $item instanceof RideBooking ? $item->seats_booked : 1,

                // ✅ Add services
                'services' => $item instanceof RideBooking
                    ? Service::whereIn('id', $item->services ?? [])->get(['id','service_name','service_image'])
                    : Service::whereIn('id', $item->services ?? [])->get(['id','service_name','service_image']),
                'driver_id' => data_get($item,'ride.driver.id',$item->driver_id),
                'driver_name' => data_get($item,'ride.driver.name',$item->driver->name ?? null),
                'driver_phone' => data_get($item,'ride.driver.phone_number',$item->driver->phone_number ?? null),
                'driver_image' => data_get($item,'ride.driver.image',$item->driver->image ?? null),
            ])->values();

        return response()->json([
            'status'=>true,
            'message'=>'Passenger rides fetched successfully',
            'data'=>$data
        ],200);
    }


    public function getConfirmationStatus(Request $request)
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json(['status' => false, 'message' => __('messages.getConfirmationStatus.user_not_authenticated')], 401);
        }

        // 🔹 Detect user's preferred language from UserLang table
        $userLang = UserLang::where('user_id', $user->id)
            ->where('device_id', $user->device_id)
            ->where('device_type', $user->device_type)
            ->first();

        $lang = $userLang->language ?? 'ru'; // fallback to Russian
        app()->setLocale($lang);

        $statusType = $request->query('status_type', 'active'); // active / completed / cancelled

        // ✅ With relationships needed for accessors
        $ridesQuery = \App\Models\RideBooking::with(['ride.user', 'request.user', 'user'])
            ->where(function ($q) use ($user) {
                $q->whereHas('ride', fn($q2) => $q2->where('user_id', $user->id)) // Driver (via Ride)
                ->orWhere('user_id', $user->id) // Passenger (via Ride)
                ->orWhereHas('request', fn($q3) => $q3->where('user_id', $user->id)); // Passenger (via Request)
            });

        // ✅ Apply status filter
        if ($statusType === 'active') {
            $ridesQuery->where('active_status', 1);
        } elseif ($statusType === 'completed') {
            $ridesQuery->where('active_status', 2);
        } elseif ($statusType === 'cancelled') {
            $ridesQuery->whereIn('status', ['cancelled', 'declined']);
        }

        $rides = $ridesQuery->orderByDesc('created_at')->get();

        $data = $rides->map(function ($item) use ($user) {
            $driver = $item->driver;
            $passenger = $item->passenger;

            return [
                'booking_id' => $item->id,
                'source' => ($driver && $driver->id == $user->id) ? 'driver' : 'passenger',

                // ✅ Accessors handle pickup & destination dynamically
                'pickup_location' => $item->pickup_location,
                'destination' => $item->destination,

                'ride_id' => $item->ride_id,
                'request_id' => $item->request_id,
                'ride_date' => $item->ride_date,
                'ride_time' => $item->ride_time,
                'price' => $item->price,
                'status' => $item->status,
                'active_status' => $item->active_status,
                'seats_booked' => $item->seats_booked,

                // ✅ Accessor handles fetching service details
                'services' => $item->services_details,

                // ✅ Driver info
                'driver_id' => $driver->id ?? null,
                'driver_name' => $driver->name ?? null,
                'driver_phone' => $driver->phone_number ?? null,
                'driver_image' => $driver->image ?? null,

                // ✅ Passenger info
                'passenger_id' => $passenger->id ?? null,
                'passenger_name' => $passenger->name ?? null,
                'passenger_phone' => $passenger->phone_number ?? null,
                'passenger_image' => $passenger->image ?? null,
            ];
        });

        return response()->json([
            'status' => true,
           'message' => __('messages.getConfirmationStatus.success'),
            'data' => $data
        ], 200);
    }



    // latest
    public function getSendResponse(Request $request)
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.getSendResponse.user_not_authenticated')
            ], 401);
        }

        // 🔹 Detect user's preferred language from UserLang table
        $userLang = UserLang::where('user_id', $user->id)
            ->where('device_id', $user->device_id)
            ->where('device_type', $user->device_type)
            ->first();

        $lang = $userLang->language ?? 'ru'; // fallback to Russian
        app()->setLocale($lang);

        //  1. Bookings: user booked someone else's ride
        $bookings = \App\Models\RideBooking::with(['ride', 'ride.user'])
            ->where('user_id', $user->id)
            ->whereHas('ride', fn($q) => $q->where('user_id', '!=', $user->id))
            ->where('active_status', '!=', 2) // ✅ exclude completed bookings
            ->where('status', '!=', 'cancelled')
            ->orderBy('created_at', 'desc')
            ->get();
            

        $bookingData = $bookings->map(function ($booking) {
            $ride = $booking->ride;
            if (!$ride) return null; // skip if ride is missing
            return [
                'type'            => __('messages.getSendResponse.types.booking'),
                'booking_id'       => $booking->id,
                'request_id'       => $booking->request_id ?? null,
                'ride_id'          => $booking->ride_id,
                'driver_id'        => optional($ride)->user_id,
                'driver_name'      => optional($ride->user)->name,
                'pickup_location'  => optional($ride)->pickup_location,
                'destination'      => optional($ride)->destination,
                'number_of_seats'  => $booking->seats_booked,
                'budget'           => $booking->price,
                'status'           => $booking->status,
                'active_status'    => $booking->active_status ?? 0,
                'services'         => $booking->services ?? [],
                'ride_date'        => optional($ride)->ride_date,
                'ride_time'        => optional($ride)->ride_time,
                'created_at'       => $booking->created_at,
            ];
        })->filter(); // ✅ remove null entries

        // 2. Passenger Requests: user showed interest as driver in others’ requests
        $interests = \App\Models\PassengerRequestDriverInterest::with(['passengerRequest.user'])
            ->where('driver_id', $user->id)
            ->whereHas('passengerRequest', fn($q) => $q->where('user_id', '!=', $user->id))
            ->orderBy('created_at', 'desc')
            ->get();

        $interestData = $interests->map(function ($interest) {
            $req = $interest->passengerRequest;
            $booking = RideBooking::where('request_id', $req->id)->first();
            $activeStatus = $booking->active_status ?? 0;
            $status = $interest->status ?? $req->status;

            if ($activeStatus == 2 || $status === 'cancelled') return null;

            return [
                 'type'            => __('messages.getSendResponse.types.request_interest'),
                'booking_id'       => $booking->id ?? null,
                'request_id'       => $req->id,
                'passenger_id'     => $req->user_id,
                'passenger_name'   => optional($req->user)->name,
                'pickup_location'  => $req->pickup_location,
                'destination'      => $req->destination,
                'number_of_seats'  => $req->number_of_seats,
                'budget'           => $req->budget,
                'status'           => $interest->status ?? $req->status,
                'active_status'    => $activeStatus,
                'services'         => $req->services ?? [],
                'ride_date'        => $req->ride_date,
                'ride_time'        => $req->ride_time,
                'created_at'       => $interest->created_at,
            ];
        })->filter(); // ✅ important


        // 🟢 Merge both datasets
        $sentData = collect($bookingData)
            ->merge($interestData)
            ->sortByDesc('created_at')
            ->values();

        return response()->json([
            'status'  => true,
             'message' => __('messages.getSendResponse.success'),
            'data'    => $sentData
        ]);
    }

   
    // latest 
    
    // public function getReceivedResponse(Request $request)
    // {
    //     $user = Auth::guard('api')->user();
    //     if (!$user) {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => 'User not authenticated'
    //         ], 401);
    //     }

    //     // DRIVER VIEW: rides created by driver
    //     $driverRides = \App\Models\Ride::with(['rideBookings.user', 'vehicle'])
    //         ->where('user_id', $user->id)
    //         ->get();

    //     if ($driverRides->isNotEmpty()) {
    //         $rideData = $driverRides->map(function ($ride) {
    //             $vehicle = $ride->vehicle;

    //             $filteredBookings = $ride->rideBookings
    //                 ->filter(fn($b) => $b->active_status != 2 && $b->status != 'cancelled')
    //                 ->map(function ($booking) {
    //                     return [
    //                         'created_by'      => 'passenger',
    //                         'booking_id'      => $booking->id,
    //                         'passenger_id'    => $booking->user_id,
    //                         'passenger_name'  => optional($booking->user)->name,
    //                         'passenger_phone' => optional($booking->user)->phone_number,
    //                         'passenger_image' => optional($booking->user)->image,
    //                         'seats_booked'    => $booking->seats_booked,
    //                         'price'           => $booking->price,
    //                         'status'          => $booking->status,
    //                         'active_status'   => $booking->active_status,
    //                         'comment'         => optional($booking)->comment,
    //                         'services'        => $booking->services ?? [],
    //                         'created_at'      => $booking->created_at,
    //                     ];
    //                 })
    //                 ->values();

    //             return [
    //                 'created_by'      => 'driver',
    //                 'ride_id'         => $ride->id,
    //                 'pickup_location' => $ride->pickup_location,
    //                 'destination'     => $ride->destination,
    //                 'ride_date'       => $ride->ride_date,
    //                 'ride_time'       => $ride->ride_time,
    //                 'accept_parcel'   => $ride->accept_parcel,
    //                 'number_of_seats' => $ride->number_of_seats,
    //                 'vehicle_id'      => $ride->vehicle_id,
    //                 'vehicle_name'    => $vehicle->brand ?? null,
    //                 'vehicle_model'   => $vehicle->model ?? null,
    //                 'vehicle_number'  => $vehicle->number_plate ?? null,
    //                 'vehicle_image'   => $vehicle->vehicle_image ?? null,
    //                 'bookings'        => $filteredBookings,
    //             ];
    //         })->filter()->values();

    //         // Passenger Requests created by driver
    //         $requests = \App\Models\PassengerRequest::with(['interests.driver.vehicle'])
    //             ->where('user_id', $user->id)
    //             ->orderBy('created_at', 'desc')
    //             ->get();

    //         $requestData = $requests->map(function ($req) {
    //             if ($req->status == 'cancelled') return null;

    //             $rideDetails = [
    //                 'created_by'          => 'driver',
    //                 'request_id'          => $req->id,
    //                 'pickup_location'     => $req->pickup_location,
    //                 'destination'         => $req->destination,
    //                 'number_of_seats'     => $req->number_of_seats,
    //                 'pickup_contact_name' => $req->pickup_contact_name,
    //                 'pickup_contact_no'   => $req->pickup_contact_no,
    //                 'drop_contact_name'   => $req->drop_contact_name,
    //                 'drop_contact_no'     => $req->drop_contact_no,
    //                 'parcel_details'      => $req->parcel_details,
    //                 'parcel_images'       => $req->parcel_images,
    //                 'budget'              => $req->budget,
    //                 'status'              => $req->status,
    //                 'active_status'       => null,
    //                 'comment'             => null,
    //                 'services'            => $req->services ?? [],
    //                 'ride_date'           => $req->ride_date,
    //                 'ride_time'           => $req->ride_time,
    //                 'created_at'          => $req->created_at,
    //             ];

    //             // Interested drivers independent of booking
    //             $interestedDrivers = $req->interests->map(function ($interest) use ($rideDetails) {
    //                 $driver = $interest->driver;
    //                 if (!$driver) return null;

    //                 $driverData = [
    //                     'driver_id'       => $driver->id,
    //                     'name'            => $driver->name,
    //                     'phone_number'    => $driver->phone_number,
    //                     'email'           => $driver->email,
    //                     'image'           => $driver->image,
    //                     'dob'             => $driver->dob,
    //                     'gender'          => $driver->gender,
    //                     'id_verified'     => $driver->id_verified,
    //                     'is_phone_verify' => $driver->is_phone_verify,
    //                     'device_type'     => $driver->device_type,
    //                     'device_id'       => $driver->device_id,
    //                 ];

    //                 $vehicleData = $driver->vehicle ? [
    //                     'vehicle_number' => $driver->vehicle->vehicle_number,
    //                     'vehicle_type'   => $driver->vehicle->vehicle_type,
    //                 ] : [];

    //                 return array_merge($rideDetails, [
    //                     'interest_id' => $interest->id,
    //                     'request_id'  => $interest->passenger_request_id,
    //                 ], $driverData, $vehicleData);
    //             })->filter()->values();

    //             return array_merge($rideDetails, ['bookings' => $interestedDrivers]);
    //         })->filter()->values();

    //         $receivedData = [
    //             'rides_with_bookings' => $rideData,
    //             'passenger_requests'  => $requestData,
    //         ];
    //     } else {
    //         // PASSENGER VIEW — exclude cancelled requests
    //         $receivedRequests = \App\Models\PassengerRequest::with(['interests.driver.vehicle'])
    //             ->where('user_id', $user->id)
    //             ->orderBy('created_at', 'desc')
    //             ->get();

    //         $passengerRequests = $receivedRequests
    //             ->filter(fn($req) => $req->status != 'cancelled')
    //             ->map(function ($req) {
    //                 $rideDetails = [
    //                     'created_by'      => 'passenger',
    //                     'request_id'      => $req->id,
    //                     'driver_id'       => $req->driver_id,
    //                     'pickup_location' => $req->pickup_location,
    //                     'destination'     => $req->destination,
    //                     'number_of_seats' => $req->number_of_seats,
    //                     'budget'          => $req->budget,
    //                     'status'          => $req->status,
    //                     'active_status'   => $req->active_status,
    //                     'services'        => $req->services ?? [],
    //                     'ride_date'       => $req->ride_date,
    //                     'ride_time'       => $req->ride_time,
    //                     'created_at'      => $req->created_at,
    //                 ];

    //                 // Include interested drivers even if no booking exists
    //                 $interestedDrivers = $req->interests->map(function ($interest) use ($rideDetails) {
    //                     $driver = $interest->driver;
    //                     if (!$driver) return null;

    //                     $driverData = [
    //                         'driver_id'       => $driver->id,
    //                         'name'            => $driver->name,
    //                         'phone_number'    => $driver->phone_number,
    //                         'email'           => $driver->email,
    //                         'image'           => $driver->image,
    //                         'dob'             => $driver->dob,
    //                         'gender'          => $driver->gender,
    //                         'id_verified'     => $driver->id_verified,
    //                         'is_phone_verify' => $driver->is_phone_verify,
    //                         'device_type'     => $driver->device_type,
    //                         'device_id'       => $driver->device_id,
    //                     ];

    //                     $vehicleData = $driver->vehicle ? [
    //                         'vehicle_number' => $driver->vehicle->vehicle_number,
    //                         'vehicle_type'   => $driver->vehicle->vehicle_type,
    //                     ] : [];

    //                     return array_merge($rideDetails, [
    //                         'interest_id' => $interest->id,
    //                         'request_id'  => $interest->passenger_request_id,
    //                     ], $driverData, $vehicleData);
    //                 })->filter()->values();

    //                 return array_merge($rideDetails, ['bookings' => $interestedDrivers]);
    //             })->values();

    //         $receivedData = [
    //             'rides_with_bookings' => [],
    //             'passenger_requests'  => $passengerRequests,
    //         ];
    //     }

    //     return response()->json([
    //         'status'  => true,
    //         'message' => 'Received requests fetched successfully',
    //         'data'    => $receivedData
    //     ]);
    // }


    public function getReceivedResponse(Request $request)
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'status'  => false,
                 'message' => __('messages.getReceivedResponse.user_not_authenticated')
            ], 401);
        }

        // 🔹 Detect user's preferred language from UserLang table
        $userLang = UserLang::where('user_id', $user->id)
            ->where('device_id', $user->device_id)
            ->where('device_type', $user->device_type)
            ->first();

        $lang = $userLang->language ?? 'ru'; // fallback to Russian
        app()->setLocale($lang);

        //  DRIVER VIEW: rides created by the driver
        $driverRides = \App\Models\Ride::with(['rideBookings.user', 'vehicle'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        //  If driver has rides
        if ($driverRides->isNotEmpty()) {
            $rideData = $driverRides->map(function ($ride) {
                $vehicle = $ride->vehicle;

                //  Filter out completed & cancelled bookings
                $filteredBookings = $ride->rideBookings
                    ->filter(fn($b) => $b->active_status != 2 && $b->status != 'cancelled')
                    ->map(function ($booking) {
                        return [
                            'created_by'      => __('messages.getReceivedResponse.created_by.passenger'),
                            'booking_id'      => $booking->id,
                            'passenger_id'    => $booking->user_id,
                            'passenger_name'  => optional($booking->user)->name,
                            'passenger_phone' => optional($booking->user)->phone_number,
                            'passenger_image' => optional($booking->user)->image,
                            'seats_booked'    => $booking->seats_booked,
                            'price'           => $booking->price,
                            'status'          => $booking->status,
                            'active_status'   => $booking->active_status,
                            'comment'         => $booking->comment,
                            'services'        => $booking->services ?? [],
                            'created_at'      => $booking->created_at,
                        ];
                    })
                    ->values();

                //  Skip rides that have no valid bookings
                // if ($filteredBookings->isEmpty()) {
                //     return null;
                // }

                //  Skip ride only if it had bookings and all are completed
               // Skip ride ONLY if it had bookings and ALL are completed
                if ($ride->rideBookings->isNotEmpty() && $filteredBookings->isEmpty()) {
                    return null; // ride fully completed, skip it
                }

                return [
                    'created_by'      => __('messages.getReceivedResponse.created_by.driver'),
                    'ride_id'         => $ride->id,
                    'pickup_location' => $ride->pickup_location,
                    'destination'     => $ride->destination,
                    'ride_date'       => $ride->ride_date,
                    'ride_time'       => $ride->ride_time,
                    'accept_parcel'   => $ride->accept_parcel,
                    'number_of_seats' => $ride->number_of_seats,
                    'vehicle_id'      => $ride->vehicle_id,
                    'vehicle_name'    => $vehicle->brand ?? null,
                    'vehicle_model'   => $vehicle->model ?? null,
                    'vehicle_number'  => $vehicle->number_plate ?? null,
                    'vehicle_image'   => $vehicle->vehicle_image ?? null,
                    'bookings'        => $filteredBookings,
                ];
            })->filter()->values();

            // ✅ Driver’s created passenger requests (exclude completed/cancelled)
            $requests = \App\Models\PassengerRequest::with(['interests.driver.vehicle'])
                ->where('user_id', $user->id)
                ->where('status', '!=', 'cancelled')
                ->orderBy('created_at', 'desc')
                ->get();

            $requestData = $requests->map(function ($req) {
                // ✅ Skip completed requests
                $booking = \App\Models\RideBooking::where('request_id', $req->id)->first();
                if (($booking && $booking->active_status == 2) || $req->status == 'cancelled') {
                    return null;
                }

                $rideDetails = [
                    'created_by'      => __('messages.getReceivedResponse.created_by.driver'),
                    'request_id'          => $req->id,
                    'pickup_location'     => $req->pickup_location,
                    'destination'         => $req->destination,
                    'number_of_seats'     => $req->number_of_seats,
                    'pickup_contact_name' => $req->pickup_contact_name,
                    'pickup_contact_no'   => $req->pickup_contact_no,
                    'drop_contact_name'   => $req->drop_contact_name,
                    'drop_contact_no'     => $req->drop_contact_no,
                    'parcel_details'      => $req->parcel_details,
                    'parcel_images'       => $req->parcel_images,
                    'budget'              => $req->budget,
                    'status'              => $req->status,
                    'active_status'       => $booking->active_status ?? null,
                    'comment'             => null,
                    'services'            => $req->services ?? [],
                    'ride_date'           => $req->ride_date,
                    'ride_time'           => $req->ride_time,
                    'created_at'          => $req->created_at,
                ];

                // Interested drivers (exclude completed/cancelled)
                $interestedDrivers = $req->interests
                    ->filter(function ($interest) use ($booking) {
                        return (!$booking || $booking->active_status != 2) && ($interest->status != 'cancelled');
                    })
                    ->map(function ($interest) use ($rideDetails) {
                        $driver = $interest->driver;
                        if (!$driver) return null;

                        $driverData = [
                            'driver_id'       => $driver->id,
                            'name'            => $driver->name,
                            'phone_number'    => $driver->phone_number,
                            'email'           => $driver->email,
                            'image'           => $driver->image,
                            'dob'             => $driver->dob,
                            'gender'          => $driver->gender,
                            'id_verified'     => $driver->id_verified,
                            'is_phone_verify' => $driver->is_phone_verify,
                            'device_type'     => $driver->device_type,
                            'device_id'       => $driver->device_id,
                        ];

                        $vehicleData = $driver->vehicle ? [
                            'vehicle_number' => $driver->vehicle->vehicle_number,
                            'vehicle_type'   => $driver->vehicle->vehicle_type,
                        ] : [];

                        return array_merge($rideDetails, [
                            'interest_id' => $interest->id,
                            'request_id'  => $interest->passenger_request_id,
                        ], $driverData, $vehicleData);
                    })
                    ->filter()
                    ->values();

                return array_merge($rideDetails, ['bookings' => $interestedDrivers]);
            })->filter()->values();

            $receivedData = [
                'rides_with_bookings' => $rideData,
                'passenger_requests'  => $requestData,
            ];
        } else {
            // ✅ PASSENGER VIEW: Exclude cancelled and completed requests
            $receivedRequests = \App\Models\PassengerRequest::with(['interests.driver.vehicle'])
                ->where('user_id', $user->id)
                ->where('status', '!=', 'cancelled')
                ->orderBy('created_at', 'desc')
                ->get();

            $passengerRequests = $receivedRequests
                ->filter(function ($req) {
                    $booking = \App\Models\RideBooking::where('request_id', $req->id)->first();
                    return !$booking || $booking->active_status != 2;
                })
                ->map(function ($req) {
                    $rideDetails = [
                        'created_by'      => 'passenger',
                        'request_id'      => $req->id,
                        'driver_id'       => $req->driver_id,
                        'pickup_location' => $req->pickup_location,
                        'destination'     => $req->destination,
                        'number_of_seats' => $req->number_of_seats,
                        'budget'          => $req->budget,
                        'status'          => $req->status,
                        'active_status'   => $req->active_status,
                        'services'        => $req->services ?? [],
                        'ride_date'       => $req->ride_date,
                        'ride_time'       => $req->ride_time,
                        'created_at'      => $req->created_at,
                    ];

                    // Interested drivers (exclude completed)
                    $interestedDrivers = $req->interests
                        ->filter(function ($interest) {
                            $booking = \App\Models\RideBooking::where('request_id', $interest->passenger_request_id)->first();
                            return !$booking || $booking->active_status != 2;
                        })
                        ->map(function ($interest) use ($rideDetails) {
                            $driver = $interest->driver;
                            if (!$driver) return null;

                            $driverData = [
                                'driver_id'       => $driver->id,
                                'name'            => $driver->name,
                                'phone_number'    => $driver->phone_number,
                                'email'           => $driver->email,
                                'image'           => $driver->image,
                                'dob'             => $driver->dob,
                                'gender'          => $driver->gender,
                                'id_verified'     => $driver->id_verified,
                                'is_phone_verify' => $driver->is_phone_verify,
                                'device_type'     => $driver->device_type,
                                'device_id'       => $driver->device_id,
                            ];

                            $vehicleData = $driver->vehicle ? [
                                'vehicle_number' => $driver->vehicle->vehicle_number,
                                'vehicle_type'   => $driver->vehicle->vehicle_type,
                            ] : [];

                            return array_merge($rideDetails, [
                                'interest_id' => $interest->id,
                                'request_id'  => $interest->passenger_request_id,
                            ], $driverData, $vehicleData);
                        })
                        ->filter()
                        ->values();

                    return array_merge($rideDetails, ['bookings' => $interestedDrivers]);
                })->values();

            $receivedData = [
                'rides_with_bookings' => [],
                'passenger_requests'  => $passengerRequests,
            ];
        }

        return response()->json([
            'status'  => true,
           'message' => __('messages.getReceivedResponse.success'),
            'data'    => $receivedData
        ]);
    }







}



















