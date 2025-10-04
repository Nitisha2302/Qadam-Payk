<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ride;
use App\Models\RideBooking;
use App\Models\ParcelBooking;
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
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first()
            ], 201);
        }

        $ride = \App\Models\Ride::find($request->ride_id);

        // ❌ Prevent user from booking their own ride
        if ($ride->user_id == $user->id) {
            return response()->json([
                'status'  => false,
                'message' => 'You cannot book your own ride.'
            ], 201);
        }

        if ($request->type == 0) { // Ride booking
            $availableSeats = $ride->number_of_seats - $ride->bookings()->sum('seats_booked');
            $seatsBooked = $request->seats_booked ?? 1;

            if ($seatsBooked > $availableSeats) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Not enough seats available'
                ], 201);
            }

            $totalPrice = $ride->price * $seatsBooked;

        } else { // Parcel booking
            $seatsBooked = 1; // Parcel usually counts as 1
            $totalPrice = $ride->price;
        }

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

    // public function getDriverBookings(Request $request)
    // {
    //     $driver = Auth::guard('api')->user();
    //     if (!$driver) {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => 'Driver not authenticated'
    //         ], 401);
    //     }

    //     // Fetch bookings where the ride belongs to this driver
    //     $bookings = \App\Models\RideBooking::with(['user', 'ride'])
    //         ->whereHas('ride', function ($query) use ($driver) {
    //             $query->where('user_id', $driver->id); // rides belong to this driver
    //         })
    //         //  ->where('status', '!=', 'cancelled')
    //         ->orderBy('created_at', 'desc')
    //         ->get();

    //     // Combine all info into a single data array
    //     $data = $bookings->map(function ($booking) {
    //         return [
    //             'booking_id'    => $booking->id,
    //             'ride_id'       => $booking->ride_id,
    //             'user_id'       => $booking->user_id,
    //             'user_name'     => $booking->user->name ?? null,
    //             'user_phone'    => $booking->user->phone_number ?? null,
    //             'user_image'    => $booking->user->image ?? null,
    //             'seats_booked'  => $booking->seats_booked,
    //             'price'         => $booking->price,
    //             'status'        => $booking->status,
    //             'type'          => $booking->type,
    //             'services'      => $booking->services_details->map(function ($service) {
    //                 return [
    //                     'id'            => $service->id,
    //                     'service_name'  => $service->service_name,
    //                     'service_image' => $service->service_image,
    //                 ];
    //             }),
    //             'pickup_location' => $booking->ride->pickup_location ?? null,
    //             'destination'     => $booking->ride->destination ?? null,
    //             'ride_date'       => $booking->ride->ride_date ?? null,
    //             'ride_time'       => $booking->ride->ride_time ?? null,
    //             'accept_parcel'   => $booking->ride->accept_parcel ?? null,
    //             'created_at'      => $booking->created_at,
    //         ];
    //     });

    //     return response()->json([
    //         'status'  => true,
    //         'message' => 'Bookings retrieved successfully',
    //         'data'    => $data
    //     ],200);
    // }

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
    public function confirmBooking(Request $request)
    {
        $driver = Auth::guard('api')->user();
        if (!$driver) {
            return response()->json([
                'status' => false,
                'message' => 'Driver not authenticated'
            ], 401);
        }

        // Validation with custom messages
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

        // Check if this booking belongs to a ride of this driver
        if (!$booking->ride || $booking->ride->user_id != $driver->id) {
            return response()->json([
                'status' => false,
                'message' => 'You are not authorized to update this booking'
            ], 201);
        }

        // Update booking status
        $booking->status = $request->status;
        $booking->save();

        // ----------------------
        // ✅ Send notification to passenger
        // ----------------------
        $passenger = $booking->user; // passenger who booked
    
        if ($passenger && $passenger->device_token) {
            $fcmService = new \App\Services\FCMService();

            $statusText = $booking->status == 'confirmed' ? 'confirmed' : 'cancelled';
            $pickup = $booking->ride->pickup_location;
            $destination = $booking->ride->destination;

            $notificationData = [
                'notification_type' => 2, // booking status update
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
            'message' => 'Booking ' . $request->status . ' successfully',
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
        $booking->active_status = '3';
        $booking->save();

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


    // public function updateBookingActiveCompleteStatus(Request $request)
    // {
    //     // ✅ Get authenticated driver
    //     $driver = Auth::guard('api')->user();
    //     if (!$driver) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'User not authenticated.',
    //         ], 401);
    //     }

    //     $now = Carbon::now();

    //     // ✅ Activate rides: active_status = 0, ride time <= now, driver-specific
    //     $ridesToActivate = RideBooking::where('active_status', '0')
    //         ->where('user_id', $driver->id) // only this driver's rides
    //         ->get()
    //         ->filter(function ($booking) use ($now) {
    //             $rideDateTime = Carbon::parse($booking->ride_date . ' ' . $booking->ride_time);
    //             return $rideDateTime->lessThanOrEqualTo($now);
    //         });

    //     foreach ($ridesToActivate as $booking) {
    //         $booking->active_status = '1'; // Active
    //         $booking->save();
    //     }

    //     // ✅ Complete rides: active_status = 1, ride time < now, driver-specific
    //     $ridesToComplete = RideBooking::where('active_status', '1')
    //         ->where('user_id', $driver->id)
    //         ->get()
    //         ->filter(function ($booking) use ($now) {
    //             $rideDateTime = Carbon::parse($booking->ride_date . ' ' . $booking->ride_time);
    //             return $rideDateTime->lessThan($now);
    //         });

    //     foreach ($ridesToComplete as $booking) {
    //         $booking->active_status = '3'; // Completed
    //         $booking->save();
    //     }

    //     return response()->json([
    //         'status'  => true,
    //         'message' => 'Booking statuses updated automatically based on ride time.',
    //         'data'    => [
    //             'activated_rides' => $ridesToActivate->count(),
    //             'completed_rides' => $ridesToComplete->count(),
    //         ],
    //     ], 200);
    // }


    // code for get cancelled pending confirm ride of driver and passednger


     // for confirm only 
    // public function getDriverConfirmedRides(Request $request)
    // {
    //     $driver = Auth::guard('api')->user();

    //     if (!$driver) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'User not authenticated'
    //         ], 401);
    //     }

    //     // --- 1) Driver's own rides booked by passengers ---
    //     $rideBookings = RideBooking::with([
    //             'user:id,name,phone_number,image', // passenger
    //             'ride:id,user_id,pickup_location,destination,ride_date,ride_time'
    //         ])
    //         ->whereHas('ride', function ($q) use ($driver) {
    //             $q->where('user_id', $driver->id);
    //         })
    //         ->whereIn('status', ['pending', 'confirmed'])
    //         ->orderBy('created_at', 'desc')
    //         ->get()
    //         ->map(function ($booking) {
    //             return [
    //                 'source'          => 'ride_booking',
    //                 'booking_id'      => $booking->id,
    //                 'pickup_location' => $booking->ride->pickup_location ?? null,
    //                 'destination'     => $booking->ride->destination ?? null,
    //                 'ride_date'       => $booking->ride->ride_date,
    //                 'ride_time'       => $booking->ride->ride_time,
    //                 'price'           => $booking->price,
    //                 'status'          => $booking->status,
    //                 'passenger_id'    => $booking->user->id ?? null,
    //                 'passenger_name'  => $booking->user->name ?? null,
    //                 'passenger_phone' => $booking->user->phone_number ?? null,
    //                 'passenger_image' => $booking->user->image ?? null,
    //             ];
    //         });

    //     // --- 2) Passenger requests where driver is confirmed ---
    //     $confirmedRequests = \App\Models\PassengerRequest::with([
    //         'user:id,name,phone_number,image'
    //     ])
    //     ->where('driver_id', $driver->id) // passenger chose this driver
    //     ->whereIn('status', ['pending', 'confirmed'])
    //     ->orderBy('created_at', 'desc')
    //     ->get()
    //     ->map(function ($request) {
    //         return [
    //             'source'          => 'passenger_request',
    //             'booking_id'      => $request->id,
    //             'pickup_location' => $request->pickup_location ?? null,
    //             'destination'     => $request->destination ?? null,
    //             'ride_date'       => $request->ride_date,
    //             'ride_time'       => $request->ride_time,
    //             'price'           => $request->budget,
    //             'status'          => $request->status, // ✅ actual status
    //             'passenger_id'    => $request->user->id ?? null,
    //             'passenger_name'  => $request->user->name ?? null,
    //             'passenger_phone' => $request->user->phone_number ?? null,
    //             'passenger_image' => $request->user->image ?? null,
    //         ];
    //     });

    //     // --- merge both collections and sort by ride_date descending ---
    //     $data = $rideBookings->merge($confirmedRequests)->sortByDesc('ride_date')->values();

    //     return response()->json([
    //         'status'  => true,
    //         'message' => 'Confirmed rides fetched successfully',
    //         'data'    => $data
    //     ], 200);
    // }


    // three in one  /api/driver/rides?status=pending   /api/driver/rides?status=confirmed  /api/driver/rides?status=cancelled

    // public function getDriverConfirmedPendingancelledRides(Request $request)
    // {
    //     $driver = Auth::guard('api')->user();

    //     if (!$driver) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'User not authenticated'
    //         ], 401);
    //     }

    //     $statusFilter = $request->query('status');
    //     $allowedStatuses = ['pending', 'confirmed', 'cancelled'];
    //     $statuses = in_array($statusFilter, $allowedStatuses) ? [$statusFilter] : ['pending', 'confirmed'];

    //     // --- 1) Driver's rides booked by passengers ---
    //     $rideBookings = RideBooking::with(['user', 'ride'])
    //         ->whereHas('ride', fn($q) => $q->where('user_id', $driver->id))
    //         ->whereIn('status', $statuses)
    //         ->orderBy('created_at', 'desc')
    //         ->get();

    //     // --- 2) Passenger requests where driver is assigned ---
    //     $confirmedRequests = \App\Models\PassengerRequest::with('user')
    //         ->where('driver_id', $driver->id)
    //         ->whereIn('status', $statuses)
    //         ->orderBy('created_at', 'desc')
    //         ->get();

    //     // --- Merge both collections ---
    //     $merged = $rideBookings->concat($confirmedRequests)->sortByDesc(fn($item) => data_get($item, 'ride_date'));

    //     // --- Map to final array format ---
    //     $data = $merged->map(fn($item) => [
    //         'source' => $item instanceof RideBooking ? 'ride_booking' : 'passenger_request',
    //         'booking_id' => $item->id,
    //         'pickup_location' => data_get($item, 'ride.pickup_location', $item->pickup_location),
    //         'destination' => data_get($item, 'ride.destination', $item->destination),
    //         'ride_date' => data_get($item, 'ride.ride_date', $item->ride_date),
    //         'ride_time' => data_get($item, 'ride.ride_time', $item->ride_time),
    //         'price' => $item instanceof RideBooking ? $item->price : $item->budget,
    //         'status' => $item->status,
    //         'passenger_id' => data_get($item, 'user.id'),
    //         'passenger_name' => data_get($item, 'user.name'),
    //         'passenger_phone' => data_get($item, 'user.phone_number'),
    //         'passenger_image' => data_get($item, 'user.image'),
    //     ])->values();

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Rides fetched successfully',
    //         'data' => $data
    //     ], 200);
    // }

    // public function getPassengerConfirmedPendingCanclledRides(Request $request)
    // {
    //     $passenger = Auth::guard('api')->user();

    //     if (!$passenger) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'User not authenticated'
    //         ], 401);
    //     }

    //     // Get status from query parameter
    //     $statusFilter = $request->query('status');
    //     $allowedStatuses = ['pending', 'confirmed', 'declined', 'cancelled'];
    //     // $statuses = in_array($statusFilter, $allowedStatuses) ? [$statusFilter] : ['pending', 'confirmed'];
    //      if ($statusFilter === 'declined') {
    //        $statuses = ['declined', 'cancelled'];
    //     } elseif (in_array($statusFilter, $allowedStatuses)) {
    //         $statuses = [$statusFilter];
    //     } else {
    //         $statuses = ['pending', 'confirmed'];
    //     }

    //     // --- 1) Rides booked by this passenger ---
    //     $rideBookings = RideBooking::with(['ride', 'ride.driver'])
    //         ->where('user_id', $passenger->id)
    //         ->whereIn('status', $statuses)
    //         ->orderBy('created_at', 'desc')
    //         ->get();

    //     // --- 2) Passenger requests created by this passenger that have a driver assigned ---
    //     $assignedRequests = \App\Models\PassengerRequest::with('driver')
    //         ->where('user_id', $passenger->id)
    //         ->whereIn('status', $statuses)
    //         ->orderBy('created_at', 'desc')
    //         ->get();

    //     // --- Merge collections ---
    //     $merged = $rideBookings->concat($assignedRequests)->sortByDesc(fn($item) => data_get($item, 'ride_date'));

    //     // --- Map to final array ---
    //     $data = $merged->map(fn($item) => [
    //         'source' => $item instanceof RideBooking ? 'ride_booking' : 'passenger_request',
    //         'booking_id' => $item->id,
    //         'pickup_location' => data_get($item, 'ride.pickup_location', $item->pickup_location),
    //         'destination' => data_get($item, 'ride.destination', $item->destination),
    //         'ride_date' => data_get($item, 'ride.ride_date', $item->ride_date),
    //         'ride_time' => data_get($item, 'ride.ride_time', $item->ride_time),
    //         'price' => $item instanceof RideBooking ? $item->price : $item->budget,
    //         'status' => $item->status,
    //         'driver_id' => data_get($item, 'ride.driver.id', $item->driver_id),
    //         'driver_name' => data_get($item, 'ride.driver.name', $item->driver->name ?? null),
    //         'driver_phone' => data_get($item, 'ride.driver.phone_number', $item->driver->phone_number ?? null),
    //         'driver_image' => data_get($item, 'ride.driver.image', $item->driver->image ?? null),
    //     ])->values();

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Passenger rides fetched successfully',
    //         'data' => $data
    //     ], 200);
    // }


    // new flow 






   









}
