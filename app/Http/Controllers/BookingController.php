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



    // code for get cancelled pending confirm ride of driver and passednger


    // public function getDriverConfirmedPendingancelledRides(Request $request)
    // {
    //     $driver = Auth::guard('api')->user();
    //     if (!$driver) {
    //         return response()->json(['status' => false, 'message' => 'User not authenticated'], 401);
    //     }

    //     $statusFilter = $request->query('status');

    //     // Determine which statuses to fetch
    //     if (!$statusFilter) {
    //         $statuses = ['pending','confirmed','cancelled','declined'];
    //     } else {
    //         switch ($statusFilter) {
    //             case 'pending':    $statuses = ['pending']; break;
    //             case 'complete':   $statuses = ['confirmed']; break;
    //             case 'cancelled':  $statuses = ['cancelled','declined']; break;
    //             default:
    //                 return response()->json([
    //                     'status'=>false,
    //                     'message'=>'Invalid status. Use pending, complete, cancelled or leave empty.'
    //                 ], 400);
    //         }
    //     }

    //     // 1) RideBookings by this driver
    //     $rideBookings = RideBooking::with(['user','ride'])
    //         ->whereHas('ride', fn($q) => $q->where('user_id',$driver->id))
    //         ->whereIn('status',$statuses)
    //         ->orderBy('created_at','desc')
    //         ->get();

    //     // 2) PassengerRequests assigned to this driver
    //     $passengerRequests = \App\Models\PassengerRequest::with('user')
    //         ->where('driver_id',$driver->id)
    //         ->whereIn('status',$statuses)
    //         ->orderBy('created_at','desc')
    //         ->get();

    //     // Merge both collections
    //     $merged = $rideBookings->concat($passengerRequests)
    //         ->sortByDesc(fn($item)=> data_get($item,'ride_date'));

    //     // Map to response
    //     $data = $merged->map(fn($item) => [
    //         'source' => $item instanceof RideBooking ? 'ride_booking' : 'passenger_request',
    //         'booking_id' => $item->id,
    //         'pickup_location' => data_get($item,'ride.pickup_location',$item->pickup_location),
    //         'destination' => data_get($item,'ride.destination',$item->destination),
    //         'ride_date' => data_get($item,'ride.ride_date',$item->ride_date),
    //         'ride_time' => data_get($item,'ride.ride_time',$item->ride_time),
    //         'price' => $item instanceof RideBooking ? $item->price : $item->budget,
    //         'status' => $item->status,
    //         'passenger_id' => data_get($item,'user.id'),
    //         'passenger_name' => data_get($item,'user.name'),
    //         'passenger_phone' => data_get($item,'user.phone_number'),
    //         'passenger_image' => data_get($item,'user.image'),
    //     ])->values();

    //     return response()->json([
    //         'status'=>true,
    //         'message'=>'Rides fetched successfully',
    //         'data'=>$data
    //     ],200);
    // }



    // public function getPassengerConfirmedPendingCanclledRides(Request $request)
    // {
    //     $passenger = Auth::guard('api')->user();
    //     if (!$passenger) {
    //         return response()->json(['status' => false, 'message' => 'User not authenticated'], 401);
    //     }

    //     $statusFilter = $request->query('status');

    //     if (!$statusFilter) {
    //         $statuses = ['pending','confirmed','cancelled','declined'];
    //     } else {
    //         switch ($statusFilter) {
    //             case 'pending':    $statuses = ['pending']; break;
    //             case 'complete':   $statuses = ['confirmed']; break;
    //             case 'cancelled':  $statuses = ['cancelled','declined']; break;
    //             default:
    //                 return response()->json([
    //                     'status'=>false,
    //                     'message'=>'Invalid status. Use pending, complete, cancelled or leave empty.'
    //                 ], 400);
    //         }
    //     }

    //     // 1) RideBookings by this passenger
    //     $rideBookings = RideBooking::with(['ride','ride.driver'])
    //         ->where('user_id',$passenger->id)
    //         ->whereIn('status',$statuses)
    //         ->orderBy('created_at','desc')
    //         ->get();

    //     // 2) PassengerRequests by this passenger
    //     $passengerRequests = \App\Models\PassengerRequest::with('driver')
    //         ->where('user_id',$passenger->id)
    //         ->whereIn('status',$statuses)
    //         ->orderBy('created_at','desc')
    //         ->get();

    //     $merged = $rideBookings->concat($passengerRequests)
    //         ->sortByDesc(fn($item)=> data_get($item,'ride_date'));

    //     $data = $merged->map(fn($item) => [
    //         'source' => $item instanceof RideBooking ? 'ride_booking' : 'passenger_request',
    //         'booking_id' => $item->id,
    //         'pickup_location' => data_get($item,'ride.pickup_location',$item->pickup_location),
    //         'destination' => data_get($item,'ride.destination',$item->destination),
    //         'ride_date' => data_get($item,'ride.ride_date',$item->ride_date),
    //         'ride_time' => data_get($item,'ride.ride_time',$item->ride_time),
    //         'price' => $item instanceof RideBooking ? $item->price : $item->budget,
    //         'status' => $item->status,
    //         'driver_id' => data_get($item,'ride.driver.id',$item->driver_id),
    //         'driver_name' => data_get($item,'ride.driver.name',$item->driver->name ?? null),
    //         'driver_phone' => data_get($item,'ride.driver.phone_number',$item->driver->phone_number ?? null),
    //         'driver_image' => data_get($item,'ride.driver.image',$item->driver->image ?? null),
    //     ])->values();

    //     return response()->json([
    //         'status'=>true,
    //         'message'=>'Passenger rides fetched successfully',
    //         'data'=>$data
    //     ],200);
    // }


    // new flow 

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


    public function getSendResponse(Request $request)
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        $sentData = collect([]);

        // Check if user is driver (created rides)
        $driverRides = \App\Models\Ride::where('user_id', $user->id)->pluck('id')->toArray();

        if (!empty($driverRides)) {
            // DRIVER → Sent = Requests they showed interest in from passenger requests
            $sentRequests = \App\Models\PassengerRequest::where('driver_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();

            $sentData = $sentRequests->map(function ($req) {
                return [
                    'request_id'      => $req->id,
                    'ride_id'         => $req->ride_id,
                    'driver_id'       => $req->driver_id,
                    'pickup_location' => $req->pickup_location,
                    'destination'     => $req->destination,
                    'number_of_seats' => $req->number_of_seats,
                    'budget'          => $req->budget,
                    'status'          => $req->status,
                    'services'        => $req->services ?? [],
                    'ride_date'       => $req->ride_date,
                    'ride_time'       => $req->ride_time,
                    'created_at'      => $req->created_at,
                ];
            });

        } else {
            // PASSENGER → Sent = Bookings made or Requests created
            $bookings = \App\Models\RideBooking::with(['ride', 'ride.driver'])
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();

            $requests = \App\Models\PassengerRequest::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();

            $bookingData = $bookings->map(function ($booking) {
                return [
                    'request_id'      => $booking->request_id,
                    'ride_id'         => $booking->ride_id,
                    'driver_id'       => optional($booking->ride)->user_id,
                    'pickup_location' => optional($booking->ride)->pickup_location,
                    'destination'     => optional($booking->ride)->destination,
                    'number_of_seats' => $booking->seats_booked,
                    'budget'          => $booking->price,
                    'status'          => $booking->status,
                    'services'        => $booking->services ?? [],
                    'ride_date'       => $booking->ride_date ?? optional($booking->ride)->ride_date,
                    'ride_time'       => $booking->ride_time ?? optional($booking->ride)->ride_time,
                    'created_at'      => $booking->created_at,
                ];
            });

            $requestData = $requests->map(function ($req) {
                return [
                    'request_id'      => $req->id,
                    'driver_id'       => $req->driver_id,
                    'pickup_location' => $req->pickup_location,
                    'destination'     => $req->destination,
                    'number_of_seats' => $req->number_of_seats,
                    'budget'          => $req->budget,
                    'status'          => $req->status,
                    'services'        => $req->services ?? [],
                    'ride_date'       => $req->ride_date,
                    'ride_time'       => $req->ride_time,
                    'created_at'      => $req->created_at,
                ];
            });

            $sentData = $bookingData->merge($requestData);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Sent requests fetched successfully',
            'data'    => $sentData
        ]);
    }

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

        // Check if user is Driver (created rides)
        $driverRides = \App\Models\Ride::with(['rideBookings.user'])->where('user_id', $user->id)->get();

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
                        'bookings'        => $ride->rideBookings->map(function ($booking) {
                            return [
                                'created_by'      => 'passenger', // Each booking created by passenger
                                'booking_id'      => $booking->id,
                                'passenger_id'    => $booking->user_id,
                                'passenger_name'  => optional($booking->user)->name,
                                'passenger_phone' => optional($booking->user)->phone_number,
                                'seats_booked'    => $booking->seats_booked,
                                'price'           => $booking->price,
                                'status'          => $booking->status,
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
                // Ride details
                $rideDetails = [
                    'created_by'      => 'passenger',
                    'request_id'      => $req->id,
                    'pickup_location' => $req->pickup_location,
                    'destination'     => $req->destination,
                    'number_of_seats' => $req->number_of_seats,
                    'budget'          => $req->budget,
                    'status'          => $req->status,
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
                ->whereNotNull('driver_id')
                ->orderBy('created_at', 'desc')
                ->get();

            $receivedData = $receivedRequests->map(function ($req) {
                return [
                    'created_by'      => 'passenger', // 🚩 Created by passenger
                    'request_id'      => $req->id,
                    'driver_id'       => $req->driver_id,
                    'pickup_location' => $req->pickup_location,
                    'destination'     => $req->destination,
                    'number_of_seats' => $req->number_of_seats,
                    'budget'          => $req->budget,
                    'status'          => $req->status,
                    'services'        => $req->services ?? [],
                    'ride_date'       => $req->ride_date,
                    'ride_time'       => $req->ride_time,
                    'created_at'      => $req->created_at,
                ];
            });
        }

        return response()->json([
            'status'  => true,
            'message' => 'Received requests fetched successfully',
            'data'    => $receivedData
        ]);
    }


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

    //     // Check if user is driver (created rides)
    //     $driverRides = \App\Models\Ride::with(['rideBookings.user'])->where('user_id', $user->id)->get();

    //     if ($driverRides->isNotEmpty()) {
    //         // ✅ DRIVER → Received = group bookings per ride
    //         $rideData = $driverRides->map(function ($ride) {
    //             return [
    //                 'ride_id'         => $ride->id,
    //                 'pickup_location' => $ride->pickup_location,
    //                 'destination'     => $ride->destination,
    //                 'ride_date'       => $ride->ride_date,
    //                 'ride_time'       => $ride->ride_time,
    //                 'accept_parcel'   => $ride->accept_parcel,
    //                 'bookings'        => $ride->rideBookings->map(function ($booking) {
    //                     return [
    //                         'booking_id'      => $booking->id,
    //                         'passenger_id'    => $booking->user_id,
    //                         'passenger_name'  => optional($booking->user)->name,
    //                         'passenger_phone' => optional($booking->user)->phone_number,
    //                         'seats_booked'    => $booking->seats_booked,
    //                         'price'           => $booking->price,
    //                         'status'          => $booking->status,
    //                         'services'        => $booking->services ?? [],
    //                         'created_at'      => $booking->created_at,
    //                     ];
    //                 }),
    //             ];
    //         });

    //         // ✅ Also include passenger requests assigned to driver
    //         $requests = \App\Models\PassengerRequest::where('driver_id', $user->id)
    //             ->orderBy('created_at', 'desc')
    //             ->get();

    //         $requestData = $requests->map(function ($req) {
    //             return [
    //                 'request_id'      => $req->id,
    //                 'pickup_location' => $req->pickup_location,
    //                 'destination'     => $req->destination,
    //                 'number_of_seats' => $req->number_of_seats,
    //                 'budget'          => $req->budget,
    //                 'status'          => $req->status,
    //                 'services'        => $req->services ?? [],
    //                 'ride_date'       => $req->ride_date,
    //                 'ride_time'       => $req->ride_time,
    //                 'created_at'      => $req->created_at,
    //             ];
    //         });

    //         $receivedData = [
    //             'rides_with_bookings' => $rideData,
    //             'passenger_requests'  => $requestData,
    //         ];

    //     } else {
    //         // ✅ PASSENGER → Received = requests where driver showed interest
    //         $receivedRequests = \App\Models\PassengerRequest::where('user_id', $user->id)
    //             ->whereNotNull('driver_id')
    //             ->orderBy('created_at', 'desc')
    //             ->get();

    //         $receivedData = $receivedRequests->map(function ($req) {
    //             return [
    //                 'request_id'      => $req->id,
    //                 'driver_id'       => $req->driver_id,
    //                 'pickup_location' => $req->pickup_location,
    //                 'destination'     => $req->destination,
    //                 'number_of_seats' => $req->number_of_seats,
    //                 'budget'          => $req->budget,
    //                 'status'          => $req->status,
    //                 'services'        => $req->services ?? [],
    //                 'ride_date'       => $req->ride_date,
    //                 'ride_time'       => $req->ride_time,
    //                 'created_at'      => $req->created_at,
    //             ];
    //         });
    //     }

    //     return response()->json([
    //         'status'  => true,
    //         'message' => 'Received requests fetched successfully',
    //         'data'    => $receivedData
    //     ]);
    // }








   









}
