<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ride;
use App\Models\RideBooking;
use App\Models\ParcelBooking;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon; // ✅ Add this line

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
            'ride_date'    => $ride->ride_date, // copy from ride
            'ride_time'    => $ride->ride_time, // copy from ride
        ]);

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

    public function getDriverBookings(Request $request)
    {
        $driver = Auth::guard('api')->user();
        if (!$driver) {
            return response()->json([
                'status'  => false,
                'message' => 'Driver not authenticated'
            ], 401);
        }

        // Fetch bookings where the ride belongs to this driver
        $bookings = \App\Models\RideBooking::with(['user', 'ride'])
            ->whereHas('ride', function ($query) use ($driver) {
                $query->where('user_id', $driver->id); // rides belong to this driver
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Combine all info into a single data array
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
        ],200);
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








}
