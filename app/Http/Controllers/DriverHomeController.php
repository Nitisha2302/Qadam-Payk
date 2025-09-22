<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Vehicle;
use App\Models\Ride;

class DriverHomeController extends Controller
{
    /**
     * Add Vehicle API
     */
    public function addVehicle(Request $request)
    {
        // ✅ Get authenticated user via the 'api' guard
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not authenticated.',
            ], 401);
        }

        // ✅ Validate input
        $validator = Validator::make($request->all(), [
            'brand'         => 'required|string|max:255',
            'model'         => 'required|string|max:255',
            'number_plate'  => 'required|string|unique:vehicles,number_plate',
            'vehicle_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            'brand.required'        => 'Vehicle brand is required.',
            'model.required'        => 'Vehicle model is required.',
            'number_plate.required' => 'Number plate is required.',
            'number_plate.unique'   => 'This number plate is already registered.',
            'vehicle_image.image'   => 'Vehicle image must be an image file.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ], 201);
        }

        // ✅ Handle file upload with custom naming (userId + original name)
        $imageName = null;
        if ($request->hasFile('vehicle_image')) {
            $file = $request->file('vehicle_image');
            $originalName = $file->getClientOriginalName();
            $imageName = $user->id . '_' . $originalName;
            $file->move(public_path('assets/vehicle_image/'), $imageName);
        }

        // ✅ Create Vehicle
        $vehicle = Vehicle::create([
            'user_id'      => $user->id,
            'brand'        => $request->brand,
            'model'        => $request->model,
            'number_plate' => $request->number_plate,
            'vehicle_image'=> $imageName,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Vehicle added successfully.',
            'data'    => $vehicle,
        ], 200);
    }

    
    public function getVehicles(Request $request)
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not authenticated.',
            ], 401);
        }

        $vehicles = Vehicle::select('id', 'brand', 'model', 'number_plate', 'vehicle_image')
            ->where('user_id', $user->id)
            ->get();

        return response()->json([
            'status'  => true,
            'message' => $vehicles->isEmpty() ? 'No vehicles found for this user.' : 'Vehicles fetched successfully.',
            'data'    => $vehicles,
        ], 200);
    }

    public function editVehicle(Request $request, $id)
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not authenticated.',
            ], 401);
        }

        $vehicle = Vehicle::where('id', $id)->where('user_id', $user->id)->first();
        if (!$vehicle) {
            return response()->json([
                'status' => false,
                'message' => 'Vehicle not found or not owned by this user.',
            ], 404);
        }

        // ✅ All fields required except image
        $validator = Validator::make($request->all(), [
            'brand'         => 'required|string|max:255',
            'model'         => 'required|string|max:255',
            'number_plate'  => 'required|string|unique:vehicles,number_plate,' . $vehicle->id,
            'vehicle_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            'brand.required'        => 'Vehicle brand is required.',
            'model.required'        => 'Vehicle model is required.',
            'number_plate.required' => 'Number plate is required.',
            'number_plate.unique'   => 'This number plate is already registered.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ], 201);
        }

        // ✅ Handle file upload if new image provided
        if ($request->hasFile('vehicle_image')) {
            $file = $request->file('vehicle_image');
            $originalName = $file->getClientOriginalName();
            $imageName = $user->id . '_' . $originalName;
            $file->move(public_path('assets/vehicle_image/'), $imageName);
            $vehicle->vehicle_image = $imageName;
        }

        // ✅ Update required fields
        $vehicle->brand = $request->brand;
        $vehicle->model = $request->model;
        $vehicle->number_plate = $request->number_plate;

        $vehicle->save();

        return response()->json([
            'status'  => true,
            'message' => 'Vehicle updated successfully.',
            'data'    => $vehicle,
        ], 200);
    }

    public function createRide(Request $request)
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not authenticated.',
            ], 401);
        }

        // ✅ Validation with custom error messages
        $validator = Validator::make($request->all(), [
            'vehicle_id'      => 'required|exists:vehicles,id',
            'pickup_location' => 'required|string|max:255',
            'destination'     => 'required|string|max:255',
            'number_of_seats' => 'required|integer|min:1',
            'price'           => 'required|numeric|min:0',
            'ride_date'       => 'required|date|after_or_equal:today',
            'ride_time'       => 'required|date_format:H:i',
            'accept_parcel'   => 'nullable|boolean',
            'services'        => 'nullable|array',
            'services.*'      => 'string|max:100',
        ], [
            'vehicle_id.required'      => 'Vehicle ID is required.',
            'vehicle_id.exists'        => 'The selected vehicle is invalid.',
            'pickup_location.required' => 'Pickup location is required.',
            'destination.required'     => 'Destination is required.',
            'number_of_seats.required' => 'Number of seats is required.',
            'number_of_seats.integer'  => 'Number of seats must be an integer.',
            'price.required'           => 'Price is required.',
            'price.numeric'            => 'Price must be a number.',
            'ride_date.required'       => 'Ride date is required.',
            'ride_date.after_or_equal' => 'Ride date must be today or later.',
            'ride_time.required'       => 'Ride time is required.',
            'ride_time.date_format'    => 'Ride time must be in the format HH:MM.',
            'accept_parcel.boolean'    => 'Accept parcel must be true or false.',
            'services.array'           => 'Services must be an array.',
            'services.*.string'        => 'Each service must be a string.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ], 201);
        }

        // ✅ Create Ride (services auto cast to JSON in DB)
        $ride = Ride::create([
            'user_id'        => $user->id,
            'vehicle_id'     => $request->vehicle_id,
            'pickup_location'=> $request->pickup_location,
            'destination'    => $request->destination,
            'number_of_seats'=> $request->number_of_seats,
            'price'          => $request->price,
            'ride_date'      => $request->ride_date,
            'ride_time'      => $request->ride_time,
           'accept_parcel'  => $request->accept_parcel ?? false,
            'services'       => $request->services,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Ride created successfully.',
            'data'    => $ride,
        ], 200);
    }


    public function editRide(Request $request, $ride_id)
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not authenticated.',
            ], 401);
        }

        // Find ride and make sure it belongs to this user
        $ride = Ride::where('id', $ride_id)
                    ->where('user_id', $user->id)
                    ->first();

        if (!$ride) {
            return response()->json([
                'status' => false,
                'message' => 'Ride not found or not authorized.',
            ], 404);
        }

        // ✅ Validation with custom messages
        $validator = Validator::make($request->all(), [
            'vehicle_id'      => 'required|exists:vehicles,id',
            'pickup_location' => 'required|string|max:255',
            'destination'     => 'required|string|max:255',
            'number_of_seats' => 'required|integer|min:1',
            'price'           => 'required|numeric|min:0',
            'ride_date'       => 'required|date|after_or_equal:today',
            'ride_time'       => 'required|date_format:H:i',
            'accept_parcel'   => 'nullable|boolean',
            'services'        => 'nullable|array',
            'services.*'      => 'string|max:100',
        ], [
            'vehicle_id.required'      => 'Vehicle ID is required.',
            'vehicle_id.exists'        => 'The selected vehicle is invalid.',
            'pickup_location.required' => 'Pickup location is required.',
            'destination.required'     => 'Destination is required.',
            'number_of_seats.required' => 'Number of seats is required.',
            'number_of_seats.integer'  => 'Number of seats must be an integer.',
            'price.required'           => 'Price is required.',
            'price.numeric'            => 'Price must be a number.',
            'ride_date.required'       => 'Ride date is required.',
            'ride_date.after_or_equal' => 'Ride date must be today or later.',
            'ride_time.required'       => 'Ride time is required.',
            'ride_time.date_format'    => 'Ride time must be in the format HH:MM.',
            'accept_parcel.boolean'    => 'Accept parcel must be true or false.',
            'services.array'           => 'Services must be an array.',
            'services.*.string'        => 'Each service must be a string.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ], 201);
        }

        // ✅ Update ride
        $ride->update([
            'vehicle_id'     => $request->vehicle_id,
            'pickup_location'=> $request->pickup_location,
            'destination'    => $request->destination,
            'number_of_seats'=> $request->number_of_seats,
            'price'          => $request->price,
            'ride_date'      => $request->ride_date,
            'ride_time'      => $request->ride_time,
            'accept_parcel'  => $request->accept_parcel ?? false,
            'services'       => $request->services,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Ride updated successfully.',
            'data'    => $ride,
        ], 200);
    }






}
