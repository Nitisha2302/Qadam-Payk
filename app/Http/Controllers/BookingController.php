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

class BookingController extends Controller
{
    public function bookRideOrParcel(Request $request)
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        // ✅ Validation
        $validator = Validator::make($request->all(), [
            'ride_id'      => 'required|exists:rides,id',
            'seats_booked' => 'required_if:type,0|integer|min:1', // required only for rides
            'services'     => 'nullable|array',
            'services.*'   => 'exists:services,id',
            'type'         => 'required|in:0,1', // 0 = ride, 1 = parcel
            'comment'      => 'nullable|string|max:2000', // ✅ new validation
        ], [
            'ride_id.required'      => 'Ride ID is required.',
            'ride_id.exists'        => 'Selected ride does not exist.',
            'seats_booked.required_if' => 'Number of seats is required for rides.',
            'seats_booked.integer'  => 'Seats must be a valid number.',
            'seats_booked.min'      => 'Seats must be at least 1.',
            'services.array'        => 'Services must be an array.',
            'services.*.exists'     => 'Selected service is invalid.',
            'type.required'         => 'Booking type is required.',
            'type.in'               => 'Type must be 0 (ride) or 1 (parcel).',
            'comment.string'        => 'Comment must be a valid text.',
            'comment.max'           => 'Comment cannot exceed 500 characters.',
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
                'message' => 'You cannot book your own ride or parcel.'
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
                'message' => 'You have already booked ride and parcel.'
            ], 201);
        }

        // Now, allow booking **only for the missing type**
        // For example, if $request->type == 0 (ride) but $bookedRide exists, do not allow ride booking again
        if (($request->type == 0 && $bookedRide) || ($request->type == 1 && $bookedParcel)) {
            return response()->json([
                'status'  => false,
                'message' => $request->type == 0 
                    ? 'You have already booked this ride.'
                    : 'You have already booked this parcel.'
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
            $tokens = [
                [
                    'device_token' => $driver->device_token,
                    'device_type' => $driver->device_type ?? 'android',
                    'user_id' => $driver->id,
                ]
            ];

            $notificationData = [
                'notification_type' => 1,
                'title' => '🚖 New Ride Booking',
                'body' => "📍 {$passengerName} booked your ride from {$ride->pickup_location} to {$ride->destination}. Please confirm!",
            ];

            // ✅ Use FCMService
            $fcmService = new FCMService();
            $fcmService->sendNotification($tokens, $notificationData);
        }


        return response()->json([
            'status'  => true,
            'message' => 'Booking created successfully',
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
                'message' => 'Driver not authenticated'
            ], 401);
        }

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
            'message' => 'Bookings retrieved successfully',
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
                'message' => 'Passenger not authenticated'
            ], 401);
        }

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
            'message' => 'Your ride requests retrieved successfully',
            'data'    => $data
        ], 200);
    }


    // api for confirm search ride by driver side
    // public function confirmBooking(Request $request)
    // {
    //     $driver = Auth::guard('api')->user();
    //     if (!$driver) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Driver not authenticated'
    //         ], 401);
    //     }

    //     // Validation with custom messages
    //     $validator = Validator::make($request->all(), [
    //         'booking_id' => 'required|exists:ride_bookings,id',
    //         'status'     => 'required|in:confirmed,cancelled',
    //     ], [
    //         'booking_id.required' => 'Booking ID is required.',
    //         'booking_id.exists'   => 'This booking does not exist.',
    //         'status.required'     => 'Status is required to update the booking.',
    //         'status.in'           => 'Status must be either "confirmed" or "cancelled".',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => $validator->errors()->first()
    //         ], 201);
    //     }


    //     $booking = RideBooking::with('ride')->find($request->booking_id);

    //     // Check if this booking belongs to a ride of this driver
    //     if (!$booking->ride || $booking->ride->user_id != $driver->id) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'You are not authorized to update this booking'
    //         ], 201);
    //     }

    //     // Update booking status
    //     $booking->status = $request->status;
    //     $booking->save();

    //     // ----------------------
    //     // ✅ Send notification to passenger
    //     // ----------------------
    //     $passenger = $booking->user; // passenger who booked
    
    //     if ($passenger && $passenger->device_token) {
    //         $fcmService = new \App\Services\FCMService();

    //         $statusText = $booking->status == 'confirmed' ? 'confirmed' : 'cancelled';
    //         $pickup = $booking->ride->pickup_location;
    //         $destination = $booking->ride->destination;

    //         $notificationData = [
    //             'notification_type' => 2, // booking status update
    //             'title' => "Booking {$statusText}",
    //             'body'  => "Your booking for ride from {$pickup} to {$destination} has been {$statusText} by the {$driver->name}.",
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
    //         'message' => 'Booking ' . $request->status . ' successfully',
    //         'data'    => $booking
    //     ],200);
    // }


    public function confirmBooking(Request $request)
    {
        $driver = Auth::guard('api')->user();
        if (!$driver) {
            return response()->json([
                'status' => false,
                'message' => 'Driver not authenticated'
            ], 401);
        }

        // Validation
        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|exists:ride_bookings,id',
            'status'     => 'required|in:confirmed,cancelled',
        ], [
            'booking_id.required' => 'Booking ID is required.',
            'booking_id.exists'   => 'This booking does not exist.',
            'status.required'     => 'Status is required to update the booking.',
            'status.in'           => 'Status must be either "confirmed" or "cancelled".',
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
                'message' => 'You are not authorized to update this booking'
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
                    'message' => 'Not enough seats available. Booking automatically cancelled.',
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
        if ($passenger && $passenger->device_token) {
            $fcmService = new \App\Services\FCMService();

            $statusText = $booking->status;
            $pickup = $ride->pickup_location;
            $destination = $ride->destination;

            $notificationData = [
                'notification_type' => 2,
                'title' => "Booking {$statusText}",
                'body'  => "Your booking for ride from {$pickup} to {$destination} has been {$statusText} by the {$driver->name}.",
            ];

            $fcmService->sendNotification([
                [
                    'device_token' => $passenger->device_token,
                    'device_type'  => $passenger->device_type ?? 'android',
                    'user_id'      => $passenger->id,
                ]
            ], $notificationData);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Booking ' . $booking->status . ' successfully',
            'data'    => $booking
        ],200);
    }


    public function updateBookingActiveStatus(Request $request)
        {
            // ✅ Get authenticated driver
            $driver = Auth::guard('api')->user();
            if (!$driver) {
                return response()->json([
                    'status'  => false,
                    'message' => 'User not authenticated.',
                ], 401);
            }


            // Validate input
            $validator = Validator::make($request->all(), [
                'booking_id' => 'required|exists:ride_bookings,id',
            ], [
                'booking_id.required' => 'Booking ID is required.',
                'booking_id.exists'   => 'Booking does not exist.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => $validator->errors()->first(),
                ], 422);
            }

            // Find the booking
            $booking = RideBooking::find($request->booking_id);
            if (!$booking) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Booking not found.',
                ], 404);
            }

            // Get the ride and check if the authenticated user is the driver
            // $ride = Ride::find($booking->ride_id);
            // if (!$ride || $ride->user_id != $driver->id) {
            //     return response()->json([
            //         'status'  => false,
            //         'message' => 'You are not authorized to confirm this booking.',
            //     ], 403);
            // }

            // Update booking status to active
            $booking->active_status = '1';
            $booking->save();

        // ✅ Send notification to passenger
        $passenger = $booking->user;
        if ($passenger && $passenger->device_token) {
            $fcmService = new \App\Services\FCMService();

            $ride = $booking->ride;
            $pickup = $ride->pickup_location ?? '';
            $destination = $ride->destination ?? '';

            $notificationData = [
                'notification_type' => 2,
                'title' => "Booking Activated",
                'body'  => "Your booking for the ride from {$pickup} to {$destination} has been started.",
            ];

            $fcmService->sendNotification([
                [
                    'device_token' => $passenger->device_token,
                    'device_type'  => $passenger->device_type ?? 'android',
                    'user_id'      => $passenger->id,
                ]
            ], $notificationData);
        }

            return response()->json([
                'status'  => true,
                'message' => 'Booking status updated to active successfully.',
                'data'    => [
                    'booking_id'    => $booking->id,
                    'ride_id'       => $booking->ride_id,
                    'driver_id'     => $driver->id,
                    'active_status' => $booking->active_status,
                ],
            ], 200);
    }


    public function updateBookingCompleteStatus(Request $request)
    {
        // ✅ Get authenticated driver
        $driver = Auth::guard('api')->user();
        if (!$driver) {
            return response()->json([
                'status'  => false,
                'message' => 'User not authenticated.',
            ], 401);
        }

        // Validate input
        $validator = Validator::make($request->all(), [
            'booking_id' => 'required|exists:ride_bookings,id',
        ], [
            'booking_id.required' => 'Booking ID is required.',
            'booking_id.exists'   => 'Booking does not exist.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        // Find the booking
        $booking = RideBooking::find($request->booking_id);
        if (!$booking) {
            return response()->json([
                'status'  => false,
                'message' => 'Booking not found.',
            ], 404);
        }

        // // Get the ride and check if the authenticated user is the driver
        // $ride = Ride::find($booking->ride_id);
        // if (!$ride || $ride->user_id != $driver->id) {
        //     return response()->json([
        //         'status'  => false,
        //         'message' => 'You are not authorized to complete this booking.',
        //     ], 403);
        // }

        // Update booking status to complete
        $booking->active_status = '2';
        $booking->save();

        // ✅ Send notification to passenger
        $passenger = $booking->user;
        if ($passenger && $passenger->device_token) {
            $fcmService = new \App\Services\FCMService();

            $ride = $booking->ride;
            $pickup = $ride->pickup_location ?? '';
            $destination = $ride->destination ?? '';

            $notificationData = [
                'notification_type' => 2,
                'title' => "Booking Completed",
                'body'  => "Your booking for the ride from {$pickup} to {$destination} has been  completed.",
            ];

            $fcmService->sendNotification([
                [
                    'device_token' => $passenger->device_token,
                    'device_type'  => $passenger->device_type ?? 'android',
                    'user_id'      => $passenger->id,
                ]
            ], $notificationData);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Booking status updated to complete successfully.',
            'data'    => [
                'booking_id'    => $booking->id,
                'ride_id'       => $booking->ride_id,
                'driver_id'     => $driver->id,
                'active_status' => $booking->active_status,
            ],
        ], 200);
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
            return response()->json(['status' => false, 'message' => 'User not authenticated'], 401);
        }

        $statusType = $request->query('status_type', 'active'); // active / completed / cancelled

        $ridesQuery = \App\Models\RideBooking::with(['ride', 'ride.driver', 'user'])
            ->where(function ($q) use ($user) {
                // Driver rides
                $q->whereHas('ride', fn($q2) => $q2->where('user_id', $user->id))
                // Passenger rides
                ->orWhere('user_id', $user->id);
            });

        // Apply status filter
        if ($statusType === 'active') {
            $ridesQuery->where('active_status', 1);
        } elseif ($statusType === 'completed') {
            $ridesQuery->where('active_status', 2);
        } elseif ($statusType === 'cancelled') {
            $ridesQuery->whereIn('status', ['cancelled', 'declined']);
        }

        $rides = $ridesQuery->orderByDesc('created_at')->get();

        $data = $rides->map(fn($item) => [
            'booking_id' => $item->id,
            'source' => $item->ride->user_id == $user->id ? 'driver' : 'passenger',
            'pickup_location' => $item->ride->pickup_location ?? null,
            'destination' => $item->ride->destination ?? null,
            'ride_id' => $item->ride_id,
            'ride_date' => $item->ride_date,
            'ride_time' => $item->ride_time,
            'price' => $item->price,
            'status' => $item->status,
            'active_status' => $item->active_status,
            'seats_booked' => $item->seats_booked,
            'services' => \App\Models\Service::whereIn('id', $item->services ?? [])->get(['id','service_name','service_image']),
            // Driver info
            'driver_id' => $item->ride->user_id ?? null,
            'driver_name' => $item->ride->driver->name ?? null,
            'driver_phone' => $item->ride->driver->phone_number ?? null,
            'driver_image' => $item->ride->driver->image ?? null,
            // Passenger info
            'passenger_id' => $item->user_id,
            'passenger_name' => $item->user->name ?? null,
            'passenger_phone' => $item->user->phone_number ?? null,
            'passenger_image' => $item->user->image ?? null,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Rides fetched successfully',
            'data' => $data
        ], 200);
    }



    // ***********************************************************************************************

    // new 

    // public function getSendResponse(Request $request)
    // {
    //     $user = Auth::guard('api')->user();
    //     if (!$user) {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => 'User not authenticated'
    //         ], 401);
    //     }

    //     // 🟢 1. Bookings: user booked someone else's ride
    //     $bookings = \App\Models\RideBooking::with(['ride', 'ride.user'])
    //         ->where('user_id', $user->id)
    //         ->whereHas('ride', fn($q) => $q->where('user_id', '!=', $user->id))
    //         ->orderBy('created_at', 'desc')
    //         ->get();

    //     $bookingData = $bookings->map(function ($booking) {
    //         $ride = $booking->ride;
    //         return [
    //             'type'             => 'booking',
    //             'request_id'       => $booking->request_id ?? $booking->id,
    //             'ride_id'          => $booking->ride_id,
    //             'driver_id'        => optional($ride)->user_id,
    //             'driver_name'      => optional($ride->user)->name,
    //             'pickup_location'  => optional($ride)->pickup_location,
    //             'destination'      => optional($ride)->destination,
    //             'number_of_seats'  => $booking->seats_booked,
    //             'budget'           => $booking->price,
    //             'status'           => $booking->status,
    //             'services'         => $booking->services ?? [],
    //             'ride_date'        => optional($ride)->ride_date,
    //             'ride_time'        => optional($ride)->ride_time,
    //             'created_at'       => $booking->created_at,
    //         ];
    //     });

    //     // 🟢 2. Passenger Requests: user showed interest as driver in others’ requests
    //     $interests = \App\Models\PassengerRequestDriverInterest::with(['passengerRequest.user'])
    //         ->where('driver_id', $user->id)
    //         ->whereHas('passengerRequest', fn($q) => $q->where('user_id', '!=', $user->id))
    //         ->orderBy('created_at', 'desc')
    //         ->get();

    //     $interestData = $interests->map(function ($interest) {
    //         $req = $interest->passengerRequest;
    //         return [
    //             'type'             => 'request_interest',
    //             'request_id'       => $req->id,
    //             'passenger_id'     => $req->user_id,
    //             'passenger_name'   => optional($req->user)->name,
    //             'pickup_location'  => $req->pickup_location,
    //             'destination'      => $req->destination,
    //             'number_of_seats'  => $req->number_of_seats,
    //             'budget'           => $req->budget,
    //             'status'           => $interest->status ?? $req->status,
    //             'services'         => $req->services ?? [],
    //             'ride_date'        => $req->ride_date,
    //             'ride_time'        => $req->ride_time,
    //             'created_at'       => $interest->created_at,
    //         ];
    //     });

    //     // 🟢 Merge both datasets
    //     $sentData = collect($bookingData)
    //         ->merge($interestData)
    //         ->sortByDesc('created_at')
    //         ->values();

    //     return response()->json([
    //         'status'  => true,
    //         'message' => 'Sent items fetched successfully',
    //         'data'    => $sentData
    //     ]);
    // }

    // public function getReceivedResponse(Request $request)
    // {
    //     $user = Auth::guard('api')->user();
    //     if (!$user) {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => 'User not authenticated'
    //         ], 401);
    //     }

    //     $receivedData = collect([]);

    //       // Initialize arrays
    //     $ridesWithBookings = [];
    //     $passengerRequests = [];

    //     // Check if user is Driver (created rides)
    //     $driverRides = \App\Models\Ride::with(['rideBookings.user', 'vehicle'])->where('user_id', $user->id)->get();

    //     if ($driverRides->isNotEmpty()) {
    //         // DRIVER
    //         $rideData = $driverRides->map(function ($ride) {
    //             $vehicle = Ride::where('vehicle_id', $ride->vehicle_id ?? 0)
    //                 ->orderBy('ride_date', 'asc')
    //                 ->orderBy('ride_time', 'asc')
    //                 ->get();

    //                 return [
    //                     'created_by'      => 'driver', // Explicitly mark who created
    //                     'ride_id'         => $ride->id,
    //                     'pickup_location' => $ride->pickup_location,
    //                     'destination'     => $ride->destination,
    //                     'ride_date'       => $ride->ride_date,
    //                     'ride_time'       => $ride->ride_time,
    //                     'accept_parcel'   => $ride->accept_parcel,
    //                     'number_of_seats' => $ride->number_of_seats,
    //                     'vehicle_id'      => $ride->vehicle_id,
    //                     // 👇 Flattened vehicle info
    //                     'vehicle_name'  => $ride->vehicle->brand ?? null,
    //                     'vehicle_model' => $ride->vehicle->model ?? null,
    //                     'vehicle_number'=> $ride->vehicle->number_plate ?? null,
    //                     'vehicle_image' => $ride->vehicle->vehicle_image ?? null,
    //                     'bookings'        => $ride->rideBookings->map(function ($booking) {
    //                         return [
    //                             'created_by'      => 'passenger', // Each booking created by passenger
    //                             'booking_id'      => $booking->id,
    //                             'passenger_id'    => $booking->user_id,
    //                             'passenger_name'  => optional($booking->user)->name,
    //                             'passenger_phone' => optional($booking->user)->phone_number,
    //                             'passenger_image' => optional($booking->user)->image,
    //                             'seats_booked'    => $booking->seats_booked,
    //                             // 'is_ride'    =>      $booking->type,
    //                             'price'           => $booking->price,
    //                             'status'          => $booking->status,
    //                             'active_status'          => $booking->active_status,
    //                               'comment'          => optional($booking)->comment,
    //                             'services'        => $booking->services ?? [],
    //                             'created_at'      => $booking->created_at,
    //                         ];
    //                     }),
    //                 ];
    //             });

    //         // $rideData = $driverRides->map(function ($ride) {
    //         //     return [
    //         //         'created_by'      => 'driver', // 🚩 Explicitly mark who created
    //         //         'ride_id'         => $ride->id,
    //         //         'pickup_location' => $ride->pickup_location,
    //         //         'destination'     => $ride->destination,
    //         //         'ride_date'       => $ride->ride_date,
    //         //         'ride_time'       => $ride->ride_time,
    //         //         'accept_parcel'   => $ride->accept_parcel,
    //         //         'number_of_seats' => $ride->number_of_seats,
    //         //         'vehicle_id'      => $ride->vehicle_id,
    //         //         $vehicle = Ride::where('vehicle_id', $ride->vehicle_id ?? 0)
    //         //         ->orderBy('ride_date', 'asc')
    //         //         ->orderBy('ride_time', 'asc')
    //         //         ->get();
    //         //         'vehicle_brand' => $vehicle -> pluck('brand')->first() ?? null,
    //         //         'bookings'        => $ride->rideBookings->map(function ($booking) {
    //         //             return [
    //         //                 'created_by'      => 'passenger', // 🚩 Each booking created by passenger
    //         //                 'booking_id'      => $booking->id,
    //         //                 'passenger_id'    => $booking->user_id,
    //         //                 'passenger_name'  => optional($booking->user)->name,
    //         //                 'passenger_phone' => optional($booking->user)->phone_number,
    //         //                 'seats_booked'    => $booking->seats_booked,
    //         //                 'price'           => $booking->price,
    //         //                 'status'          => $booking->status,
    //         //                 'services'        => $booking->services ?? [],
    //         //                 'created_at'      => $booking->created_at,
    //         //             ];
    //         //         }),
    //         //     ];
    //         // });

    //         // Ride requests assigned to driver
    //         $requests = \App\Models\PassengerRequest::where('user_id', $user->id)
    //             ->orderBy('created_at', 'desc')
    //             ->get();

    //         $requests = \App\Models\PassengerRequest::with(['interests.driver.vehicle'])
    //             ->where('user_id', $user->id)
    //             ->orderBy('created_at', 'desc')
    //             ->get();


    //         $requestData = $requests->map(function ($req) {
    //              // ✅ Get active_status from booking table (if exists)
    //         $requestBooking = \App\Models\RideBooking::where('request_id', $req->id)
    //                             ->whereNotNull('active_status')
    //                             ->first();
    //             // Passenger Request details
    //             $rideDetails = [
    //                 'created_by'      => 'driver',
    //                 'request_id'      => $req->id,
    //                 'pickup_location' => $req->pickup_location,
    //                 'destination'     => $req->destination,
    //                 'number_of_seats' => $req->number_of_seats,

    //                 'pickup_contact_name' => $req->pickup_contact_name,
    //                 'pickup_contact_no' => $req->pickup_contact_no,
    //                 'drop_contact_name' => $req->drop_contact_name,
    //                'drop_contact_no' => $req->drop_contact_no,
    //                'parcel_details' => $req->parcel_details,
    //                'parcel_images' => $req->parcel_images,

    //                 'budget'          => $req->budget,
    //                 'status'          => $req->status,

    //                 'active_status'       => $requestBooking->active_status ?? null,
           
    //                 'comment'          => optional($requestBooking)->comment,

    //                 'services'        => $req->services ?? [],
    //                 'ride_date'       => $req->ride_date,
    //                 'ride_time'       => $req->ride_time,
    //                 'created_at'      => $req->created_at,
    //             ];

    //             // Interested drivers, mapped as array
    //             $interestedDrivers = $req->interests->map(function ($interest) use ($rideDetails) {
    //                 $driver = $interest->driver;
    //                 if (!$driver) return null;


    //                 $driverData = [
    //                     'driver_id'        => $driver->id,
    //                     'name'             => $driver->name,
    //                     'phone_number'     => $driver->phone_number,
    //                     'email'            => $driver->email,
    //                     'image'            => $driver->image,
    //                     'dob'              => $driver->dob,
    //                     'gender'           => $driver->gender,
    //                     'id_verified'      => $driver->id_verified,
    //                     'is_phone_verify'  => $driver->is_phone_verify,
    //                     'device_type'      => $driver->device_type,
    //                     'device_id'        => $driver->device_id,
    //                 ];
    //                 $vehicleData = $driver->vehicle ? [
    //                     'vehicle_number'   => $driver->vehicle->vehicle_number,
    //                     'vehicle_type'     => $driver->vehicle->vehicle_type,
    //                 ] : [];

    //                 return array_merge(
    //                     $rideDetails,
    //                     [
    //                         'interest_id' => $interest->id,
    //                         'request_id'  => $interest->passenger_request_id,
    //                     ],
    //                     $driverData,
    //                     $vehicleData
    //                 );
    //             })->filter()->values();

    //             // Attach interested drivers into bookings array
    //             return array_merge($rideDetails, [
    //                 'bookings' => $interestedDrivers
    //             ]);
    //         });

    //         // $requestData = $requests->map(function ($req) {
    //         //     return [
    //         //         'created_by'      => 'passenger', // 🚩 Requests are created by passengers
    //         //         'request_id'      => $req->id,
    //         //         'pickup_location' => $req->pickup_location,
    //         //         'destination'     => $req->destination,
    //         //         'number_of_seats' => $req->number_of_seats,
    //         //         'budget'          => $req->budget,
    //         //         'status'          => $req->status,
    //         //         'services'        => $req->services ?? [],
    //         //         'ride_date'       => $req->ride_date,
    //         //         'ride_time'       => $req->ride_time,
    //         //         'created_at'      => $req->created_at,
    //         //     ];
    //         // });

    //         $receivedData = [
    //             'rides_with_bookings' => $rideData,
    //             'passenger_requests'  => $requestData,
    //         ];
    //     } else {
    //         // PASSENGER → Received requests where driver showed interest
    //         $receivedRequests = \App\Models\PassengerRequest::where('user_id', $user->id)
    //             // ->whereNotNull('driver_id')
    //             ->orderBy('created_at', 'desc')
    //             ->get();

    //         $passengerRequests  = $receivedRequests->map(function ($req) {
    //             return [
    //                 'created_by'      => 'passenger', // 🚩 Created by passenger
    //                 'request_id'      => $req->id,
    //                 'driver_id'       => $req->driver_id,
    //                 'pickup_location' => $req->pickup_location,
    //                 'destination'     => $req->destination,
    //                 'number_of_seats' => $req->number_of_seats,
    //                 'budget'          => $req->budget,
    //                 'status'          => $req->status,
    
    //                  'active_status'          => $req->active_status,
    //                 //  'comment'          => $requestBooking->comment,

    //                 'services'        => $req->services ?? [],
    //                 'ride_date'       => $req->ride_date,
    //                 'ride_time'       => $req->ride_time,
    //                 'created_at'      => $req->created_at,
    //             ];
    //         });
    //          // Wrap in same structure as driver
    //         $receivedData = [
    //             'rides_with_bookings' => [], // empty because user has no rides
    //             'passenger_requests'  => $passengerRequests,
    //         ];
    //     }

    //     return response()->json([
    //         'status'  => true,
    //         'message' => 'Received requests fetched successfully',
    //         'data'    => $receivedData
    //     ]);
    // }


    // latest without converstaion 
    public function getSendResponse(Request $request)
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        //  1. Bookings: user booked someone else's ride
        $bookings = \App\Models\RideBooking::with(['ride', 'ride.user'])
            ->where('user_id', $user->id)
            ->whereHas('ride', fn($q) => $q->where('user_id', '!=', $user->id))
            ->where('active_status', '!=', 2) // ✅ exclude completed bookings
            ->orderBy('created_at', 'desc')
            ->get();
            

        $bookingData = $bookings->map(function ($booking) {
            $ride = $booking->ride;
            if (!$ride) return null; // skip if ride is missing
            return [
                'type'             => 'booking',
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

            if ($activeStatus == 2) return null;

            return [
                'type'             => 'request_interest',
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
            'message' => 'Sent items fetched successfully',
            'data'    => $sentData
        ]);
    }

    // with converstaion 

    // public function getSendResponse(Request $request)
    // {
    //     $user = Auth::guard('api')->user();
    //     if (!$user) {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => 'User not authenticated'
    //         ], 401);
    //     }

    //     //  1. Bookings: user booked someone else's ride
    //     $bookings = \App\Models\RideBooking::with(['ride', 'ride.user'])
    //         ->where('user_id', $user->id)
    //         ->whereHas('ride', fn($q) => $q->where('user_id', '!=', $user->id))
    //         ->where('active_status', '!=', 2) // ✅ exclude completed bookings
    //         ->orderBy('created_at', 'desc')
    //         ->get();
            

    //         $bookingData = $bookings->map(function ($booking) use ($user) {
    //             $ride = $booking->ride;
    //             $driver = optional($ride)->user;

    //             // 🟢 find conversation between user & driver
    //             $conversation = \App\Models\Conversation::where(function ($q) use ($user, $driver) {
    //                 $q->where('user_one_id', $user->id)
    //                 ->where('user_two_id', $driver?->id);
    //             })->orWhere(function ($q) use ($user, $driver) {
    //                 $q->where('user_one_id', $driver?->id)
    //                 ->where('user_two_id', $user->id);
    //             })->first();
    //         return [
    //             'type'             => 'booking',
    //             'request_id'       => $booking->request_id ?? $booking->id,
    //             'ride_id'          => $booking->ride_id,
    //             'driver_id'        => optional($ride)->user_id,
    //             'driver_name'      => optional($ride->user)->name,
    //             'pickup_location'  => optional($ride)->pickup_location,
    //             'destination'      => optional($ride)->destination,
    //             'number_of_seats'  => $booking->seats_booked,
    //             'budget'           => $booking->price,
    //             'status'           => $booking->status,
    //             'active_status'    => $booking->active_status ?? 0,
    //             'services'         => $booking->services ?? [],
    //             'ride_date'        => optional($ride)->ride_date,
    //             'ride_time'        => optional($ride)->ride_time,
    //              'conversation_id'  => $conversation?->id, // ✅ added
    //             'created_at'       => $booking->created_at,
    //         ];
    //     });

    //     // 2. Passenger Requests: user showed interest as driver in others’ requests
    //     $interests = \App\Models\PassengerRequestDriverInterest::with(['passengerRequest.user'])
    //         ->where('driver_id', $user->id)
    //         ->whereHas('passengerRequest', fn($q) => $q->where('user_id', '!=', $user->id))
    //         ->orderBy('created_at', 'desc')
    //         ->get();

    //     $interestData = $interests->map(function ($interest) use ($user) {
    //         $req = $interest->passengerRequest;
    //         $passenger = optional($req)->user;
    //         //  Try to fetch linked booking (if this request was confirmed)
    //         $booking =RideBooking::where('request_id', $req->id)->first();

    //         // ✅ Determine correct active_status
    //         $activeStatus = $booking->active_status ?? 0;

    //         // ✅ Skip if completed (2)
    //         if ($activeStatus == 2) {
    //             return null;
    //         }

    //          // 🟢 find conversation between user (driver) & passenger
    //         $conversation = \App\Models\Conversation::where(function ($q) use ($user, $passenger) {
    //             $q->where('user_one_id', $user->id)
    //             ->where('user_two_id', $passenger?->id);
    //         })->orWhere(function ($q) use ($user, $passenger) {
    //             $q->where('user_one_id', $passenger?->id)
    //             ->where('user_two_id', $user->id);
    //         })->first();


    //         return [
    //             'type'             => 'request_interest',
    //             'request_id'       => $req->id,
    //             'passenger_id'     => $req->user_id,
    //             'passenger_name'   => optional($req->user)->name,
    //             'pickup_location'  => $req->pickup_location,
    //             'destination'      => $req->destination,
    //             'number_of_seats'  => $req->number_of_seats,
    //             'budget'           => $req->budget,
    //             'status'           => $interest->status ?? $req->status,
    //              'active_status'    => $activeStatus,
    //             'services'         => $req->services ?? [],
    //             'ride_date'        => $req->ride_date,
    //             'ride_time'        => $req->ride_time,
    //               'conversation_id'  => $conversation?->id, // ✅ added
    //             'created_at'       => $interest->created_at,
    //         ];
    //     });

    //     // 🟢 Merge both datasets
    //     $sentData = collect($bookingData)
    //         ->merge($interestData)
    //         ->sortByDesc('created_at')
    //         ->values();

    //     return response()->json([
    //         'status'  => true,
    //         'message' => 'Sent items fetched successfully',
    //         'data'    => $sentData
    //     ]);
    // }

    public function getReceivedResponse(Request $request)
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        $receivedData = collect([]);

          // Initialize arrays
        $ridesWithBookings = [];
        $passengerRequests = [];

        // Check if user is Driver (created rides)
        $driverRides = \App\Models\Ride::with(['rideBookings.user', 'vehicle'])->where('user_id', $user->id)->get();

        if ($driverRides->isNotEmpty()) {
            // DRIVER
            $rideData = $driverRides->map(function ($ride) {
                $vehicle = Ride::where('vehicle_id', $ride->vehicle_id ?? 0)
                    ->orderBy('ride_date', 'asc')
                    ->orderBy('ride_time', 'asc')
                    ->get();

                    return [
                        'created_by'      => 'driver', // Explicitly mark who created
                        'ride_id'         => $ride->id,
                        'pickup_location' => $ride->pickup_location,
                        'destination'     => $ride->destination,
                        'ride_date'       => $ride->ride_date,
                        'ride_time'       => $ride->ride_time,
                        'accept_parcel'   => $ride->accept_parcel,
                        'number_of_seats' => $ride->number_of_seats,
                        'vehicle_id'      => $ride->vehicle_id,
                        // 👇 Flattened vehicle info
                        'vehicle_name'  => $ride->vehicle->brand ?? null,
                        'vehicle_model' => $ride->vehicle->model ?? null,
                        'vehicle_number'=> $ride->vehicle->number_plate ?? null,
                        'vehicle_image' => $ride->vehicle->vehicle_image ?? null,
                        'bookings'        => $ride->rideBookings->map(function ($booking) {
                            return [
                                'created_by'      => 'passenger', // Each booking created by passenger
                                'booking_id'      => $booking->id,
                                'passenger_id'    => $booking->user_id,
                                'passenger_name'  => optional($booking->user)->name,
                                'passenger_phone' => optional($booking->user)->phone_number,
                                'passenger_image' => optional($booking->user)->image,
                                'seats_booked'    => $booking->seats_booked,
                                // 'is_ride'    =>      $booking->type,
                                'price'           => $booking->price,
                                'status'          => $booking->status,
                                'active_status'          => $booking->active_status,
                                  'comment'          => optional($booking)->comment,
                                'services'        => $booking->services ?? [],
                                'created_at'      => $booking->created_at,
                            ];
                        }),
                    ];
                });

            // $rideData = $driverRides->map(function ($ride) {
            //     return [
            //         'created_by'      => 'driver', // 🚩 Explicitly mark who created
            //         'ride_id'         => $ride->id,
            //         'pickup_location' => $ride->pickup_location,
            //         'destination'     => $ride->destination,
            //         'ride_date'       => $ride->ride_date,
            //         'ride_time'       => $ride->ride_time,
            //         'accept_parcel'   => $ride->accept_parcel,
            //         'number_of_seats' => $ride->number_of_seats,
            //         'vehicle_id'      => $ride->vehicle_id,
            //         $vehicle = Ride::where('vehicle_id', $ride->vehicle_id ?? 0)
            //         ->orderBy('ride_date', 'asc')
            //         ->orderBy('ride_time', 'asc')
            //         ->get();
            //         'vehicle_brand' => $vehicle -> pluck('brand')->first() ?? null,
            //         'bookings'        => $ride->rideBookings->map(function ($booking) {
            //             return [
            //                 'created_by'      => 'passenger', // 🚩 Each booking created by passenger
            //                 'booking_id'      => $booking->id,
            //                 'passenger_id'    => $booking->user_id,
            //                 'passenger_name'  => optional($booking->user)->name,
            //                 'passenger_phone' => optional($booking->user)->phone_number,
            //                 'seats_booked'    => $booking->seats_booked,
            //                 'price'           => $booking->price,
            //                 'status'          => $booking->status,
            //                 'services'        => $booking->services ?? [],
            //                 'created_at'      => $booking->created_at,
            //             ];
            //         }),
            //     ];
            // });

            // Ride requests assigned to driver
            $requests = \App\Models\PassengerRequest::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();

            $requests = \App\Models\PassengerRequest::with(['interests.driver.vehicle'])
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();


            $requestData = $requests->map(function ($req) {
                 // ✅ Get active_status from booking table (if exists)
            $requestBooking = \App\Models\RideBooking::where('request_id', $req->id)
                                ->whereNotNull('active_status')
                                ->first();
                // Passenger Request details
                $rideDetails = [
                    'created_by'      => 'driver',
                    'request_id'      => $req->id,
                    'pickup_location' => $req->pickup_location,
                    'destination'     => $req->destination,
                    'number_of_seats' => $req->number_of_seats,

                    'pickup_contact_name' => $req->pickup_contact_name,
                    'pickup_contact_no' => $req->pickup_contact_no,
                    'drop_contact_name' => $req->drop_contact_name,
                   'drop_contact_no' => $req->drop_contact_no,
                   'parcel_details' => $req->parcel_details,
                   'parcel_images' => $req->parcel_images,

                    'budget'          => $req->budget,
                    'status'          => $req->status,

                    'active_status'       => $requestBooking->active_status ?? null,
           
                    'comment'          => optional($requestBooking)->comment,

                    'services'        => $req->services ?? [],
                    'ride_date'       => $req->ride_date,
                    'ride_time'       => $req->ride_time,
                    'created_at'      => $req->created_at,
                ];

                // Interested drivers, mapped as array
                $interestedDrivers = $req->interests->map(function ($interest) use ($rideDetails) {
                    $driver = $interest->driver;
                    if (!$driver) return null;


                    $driverData = [
                        'driver_id'        => $driver->id,
                        'name'             => $driver->name,
                        'phone_number'     => $driver->phone_number,
                        'email'            => $driver->email,
                        'image'            => $driver->image,
                        'dob'              => $driver->dob,
                        'gender'           => $driver->gender,
                        'id_verified'      => $driver->id_verified,
                        'is_phone_verify'  => $driver->is_phone_verify,
                        'device_type'      => $driver->device_type,
                        'device_id'        => $driver->device_id,
                    ];
                    $vehicleData = $driver->vehicle ? [
                        'vehicle_number'   => $driver->vehicle->vehicle_number,
                        'vehicle_type'     => $driver->vehicle->vehicle_type,
                    ] : [];

                    return array_merge(
                        $rideDetails,
                        [
                            'interest_id' => $interest->id,
                            'request_id'  => $interest->passenger_request_id,
                        ],
                        $driverData,
                        $vehicleData
                    );
                })->filter()->values();

                // Attach interested drivers into bookings array
                return array_merge($rideDetails, [
                    'bookings' => $interestedDrivers
                ]);
            });

            // $requestData = $requests->map(function ($req) {
            //     return [
            //         'created_by'      => 'passenger', // 🚩 Requests are created by passengers
            //         'request_id'      => $req->id,
            //         'pickup_location' => $req->pickup_location,
            //         'destination'     => $req->destination,
            //         'number_of_seats' => $req->number_of_seats,
            //         'budget'          => $req->budget,
            //         'status'          => $req->status,
            //         'services'        => $req->services ?? [],
            //         'ride_date'       => $req->ride_date,
            //         'ride_time'       => $req->ride_time,
            //         'created_at'      => $req->created_at,
            //     ];
            // });

            $receivedData = [
                'rides_with_bookings' => $rideData,
                'passenger_requests'  => $requestData,
            ];
        } else {
            // PASSENGER → Received requests where driver showed interest
            $receivedRequests = \App\Models\PassengerRequest::where('user_id', $user->id)
                // ->whereNotNull('driver_id')
                ->orderBy('created_at', 'desc')
                ->get();

            $passengerRequests  = $receivedRequests->map(function ($req) {
                return [
                    'created_by'      => 'passenger', // 🚩 Created by passenger
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
            });
             // Wrap in same structure as driver
            $receivedData = [
                'rides_with_bookings' => [], // empty because user has no rides
                'passenger_requests'  => $passengerRequests,
            ];
        }

        return response()->json([
            'status'  => true,
            'message' => 'Received requests fetched successfully',
            'data'    => $receivedData
        ]);
    }


















}