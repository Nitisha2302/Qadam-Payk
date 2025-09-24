<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ride;
use App\Models\RideBooking;
use App\Models\ParcelBooking;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    public function bookRide(Request $request)
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        // Validation
        $validator = Validator::make($request->all(), [
            // Ride
            'ride_id'      => 'required|exists:rides,id',
            'seats_booked' => 'required|integer|min:1',

            // Parcel (optional)
            'pickup_city'        => 'required_with:drop_city,pickup_name,pickup_contact|nullable|string|max:255',
            'pickup_name'        => 'required_with:pickup_city|nullable|string|max:255',
            'pickup_contact'     => 'required_with:pickup_city|nullable|string|max:15',
            'drop_city'          => 'required_with:pickup_city|nullable|string|max:255',
            'drop_name'          => 'required_with:drop_city|nullable|string|max:255',
            'drop_contact'       => 'required_with:drop_city|nullable|string|max:15',
            'parcel_description' => 'nullable|string|max:500',
            'parcel_images.*'    => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            // Ride custom messages
            'ride_id.required'      => 'Ride ID is required.',
            'ride_id.exists'        => 'Selected ride does not exist.',
            'seats_booked.required' => 'Number of seats is required.',
            'seats_booked.integer'  => 'Seats must be a valid integer.',
            'seats_booked.min'      => 'Seats must be at least 1.',

            // Parcel custom messages
            'pickup_city.required_with'    => 'Pickup city is required when parcel details are provided.',
            'pickup_name.required_with'    => 'Pickup contact name is required.',
            'pickup_contact.required_with' => 'Pickup contact number is required.',
            'drop_city.required_with'      => 'Drop city is required.',
            'drop_name.required_with'      => 'Drop contact name is required.',
            'drop_contact.required_with'   => 'Drop contact number is required.',
            'parcel_images.*.image'        => 'Each parcel file must be a valid image.',
            'parcel_images.*.mimes'        => 'Parcel images must be jpeg, png, jpg, or gif.',
            'parcel_images.*.max'          => 'Each parcel image cannot exceed 2MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $ride = Ride::findOrFail($request->ride_id);

        // Check seat availability
        $bookedSeats     = $ride->rideBookings()->sum('seats_booked');
        $availableSeats  = $ride->number_of_seats - $bookedSeats;

        if ($request->seats_booked > $availableSeats) {
            return response()->json([
                'status'  => false,
                'message' => "Only $availableSeats seat(s) available"
            ], 400);
        }

        // Create Ride Booking
        $booking = RideBooking::create([
            'ride_id'      => $ride->id,
            'user_id'      => $user->id,
            'seats_booked' => $request->seats_booked,
            'price'        => $request->price ?? $ride->price * $request->seats_booked,
            'status'       => 'confirmed',
        ]);

        // Create Parcel Booking if provided
        $parcelBooking = null;
        if ($request->pickup_city && $request->drop_city) {

            $imagePaths = [];
            if ($request->hasFile('parcel_images')) {
                foreach ($request->file('parcel_images') as $image) {
                    try {
                        $filename = time() . '_' . $image->getClientOriginalName();
                        $image->move(public_path('assets/parcels/'), $filename);
                        $imagePaths[] =  $filename;
                    } catch (\Exception $e) {
                        return response()->json([
                            'status'  => false,
                            'message' => 'Failed to upload parcel images: ' . $e->getMessage()
                        ], 500);
                    }
                }
            }

            $parcelBooking = ParcelBooking::create([
                'user_id'            => $user->id,
                'ride_booking_id'    => $booking->id,
                'pickup_city'        => $request->pickup_city,
                'pickup_name'        => $request->pickup_name,
                'pickup_contact'     => $request->pickup_contact,
                'drop_city'          => $request->drop_city,
                'drop_name'          => $request->drop_name,
                'drop_contact'       => $request->drop_contact,
                'parcel_description' => $request->parcel_description,
                'parcel_images'      => json_encode($imagePaths),
                'status'             => 'confirmed',
            ]);
        }

        return response()->json([
            'status'         => true,
            'message'        => 'Booking created successfully',
            'ride_booking'   => $booking,
            'parcel_booking' => $parcelBooking
        ], 200);
    }
}
