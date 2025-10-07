<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Ride;
use App\Models\Service;
use Carbon\Carbon;

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
             'vehicle_image.mimes'   => 'Vehicle image must be a file of type: jpg, jpeg, png.',
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

    public function editVehicle(Request $request)
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not authenticated.',
            ], 401);
        }

        // ✅ All fields required except image
        $validator = Validator::make($request->all(), [
            'vehicle_id'    => 'required|exists:vehicles,id',
            'brand'         => 'required|string|max:255',
            'model'         => 'required|string|max:255',
            'number_plate'  => 'required|string|unique:vehicles,number_plate,' . $request->vehicle_id,
            'vehicle_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            'vehicle_id.required'    => 'Vehicle ID is required.',
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

        // ✅ Use vehicle_id consistently
            $vehicle = Vehicle::where('id', $request->vehicle_id)
                            ->where('user_id', $user->id)
                            ->first();

            if (!$vehicle) {
                return response()->json([
                    'status' => false,
                    'message' => 'Vehicle not found or not owned by this user.',
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
            'reaching_time'  => 'nullable|date_format:H:i', // ✅ added rule
            'accept_parcel'   => 'nullable|boolean',
            'services'        => 'nullable|array',
            'services.*'      => 'exists:services,id', 
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
            'services.*.exists' => 'One or more selected services are invalid.',
            'reaching_time.date_format' => 'Reaching time must be in HH:MM (24-hour) format.',

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
            'reaching_time' => $request->reaching_time ?? $ride->reaching_time ?? null, // ✅ prefer user input, else from ride
           'accept_parcel'  => $request->accept_parcel ?? false,
            'services'       => $request->services,
        ]);

             // ✅ Replace IDs with details before response
         $ride->services = Service::whereIn('id', $request->services ?? [])
                            ->get(['id','service_name','service_image']);


        return response()->json([
            'status'  => true,
            'message' => 'Ride created successfully.',
            'data'    => $ride,
        ], 200);
    }


    public function editRide(Request $request)
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not authenticated.',
            ], 401);
        }

        // ✅ Validation with custom messages
        $validator = Validator::make($request->all(), [
            'ride_id'         => 'required|exists:rides,id',
            'vehicle_id'      => 'required|exists:vehicles,id',
            'pickup_location' => 'required|string|max:255',
            'destination'     => 'required|string|max:255',
            'number_of_seats' => 'required|integer|min:1',
            'price'           => 'required|numeric|min:0',
            'ride_date'       => 'required|date|after_or_equal:today',
            'ride_time'       => 'required|date_format:H:i',
             'reaching_time'  => 'nullable|date_format:H:i',
            'accept_parcel'   => 'nullable|boolean',
         'services'        => ['nullable', 'array'],
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
            'reaching_time.date_format' => 'Reaching time must be in HH:MM (24-hour) format.',
        ]);

        

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ], 201);
        }

        // ✅ Find ride
        $ride = Ride::where('id', $request->ride_id)
                    ->where('user_id', $user->id)
                    ->first();

        if (!$ride) {
            return response()->json([
                'status' => false,
                'message' => 'Ride not found or not authorized.',
            ], 201);
        }

  
         // ✅ Update ride (store only IDs for services)
        $ride->update([
            'vehicle_id'      => $request->vehicle_id,
            'pickup_location' => $request->pickup_location,
            'destination'     => $request->destination,
            'number_of_seats' => $request->number_of_seats,
            'price'           => $request->price,
            'ride_date'       => $request->ride_date,
            'ride_time'       => $request->ride_time,
             'reaching_time' => $request->reaching_time ?? $ride->reaching_time ?? null,
            'accept_parcel'   => $request->accept_parcel ?? false,
            'services'        => $request->services,
        ]);

        // ✅ Expand services only for response
        $ride->services = Service::whereIn('id', $request->services ?? [])
                            ->get(['id','service_name','service_image']);

        return response()->json([
            'status'  => true,
            'message' => 'Ride updated successfully.',
            'data'    => $ride,
        ], 200);
    }

    // api for get all the ride list ceatred by deiver
    public function getAllRidesCreatedByDriver(Request $request)
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not authenticated.',
            ], 401);
        }

        // ✅ Fetch rides created by this driver with vehicle & driver relation
        $rides = Ride::with([
                'vehicle:id,brand,model,number_plate,vehicle_image,vehicle_type',
                'driver:id,name,phone_number,image'  // <- use phone_number
            ])
            ->where('user_id', $user->id)
            ->get();

        // ✅ Format response (merge vehicle + driver + services into flat array)
        $rides = $rides->map(function ($ride) {
            return [
                "id"              => $ride->id,
                "pickup_location" => $ride->pickup_location,
                "destination"     => $ride->destination,
                "number_of_seats" => $ride->number_of_seats,
                "price"           => $ride->price,
                "ride_date"       => $ride->ride_date,
                "ride_time"       => $ride->ride_time,
                "accept_parcel"   => (bool) $ride->accept_parcel,

                // Vehicle details
                "vehicle_id"      => $ride->vehicle->id ?? null,
                "vehicle_brand"   => $ride->vehicle->brand ?? null,
                "vehicle_model"   => $ride->vehicle->model ?? null,
                "vehicle_number"  => $ride->vehicle->number_plate ?? null,
                "vehicle_type"    => $ride->vehicle->vehicle_type ?? null,
                "vehicle_image"   => $ride->vehicle->vehicle_image ?? null,

                // Driver details
                "driver_id"       => $ride->driver->id ?? null,
                "driver_name"     => $ride->driver->name ?? null,
                "driver_phone"    => $ride->driver->phone_number ?? null,
                "driver_image"    => $ride->driver->image ?? null,

                // Services (convert JSON ids → actual service details)
                "services"        => Service::whereIn('id', $ride->services ?? [])
                                            ->get(['id','service_name','service_image']),
            ];
        });

        return response()->json([
            "status"  => true,
            "message" => "Driver rides fetched successfully.",
            "data"    => $rides,
        ], 200);
    }





    public function searchRides(Request $request)
    {
        $user = Auth::guard('api')->user(); // may be null for guest

        $validator = Validator::make($request->all(), [
            'pickup_location' => 'nullable|string|max:255',
            'destination'     => 'nullable|string|max:255',
            'ride_date'       => 'nullable|date_format:d-m-Y|after_or_equal:today',
            'number_of_seats' => 'nullable|integer|min:1',
            'services'        => 'nullable|array',
            'services.*'      => 'string|max:50',
        ], [
            'pickup_location.string'   => 'Pickup location must be a valid string.',
            'pickup_location.max'      => 'Pickup location must not exceed 255 characters.',
            'destination.string'       => 'Destination must be a valid string.',
            'destination.max'          => 'Destination must not exceed 255 characters.',
            'ride_date.date_format'    => 'Ride date must be in DD-MM-YYYY format.',
            'ride_date.after_or_equal' => 'Ride date must be today or a future date.',
            'number_of_seats.integer'  => 'Number of seats must be a valid number.',
            'number_of_seats.min'      => 'Number of seats must be at least 1.',
            'services.array'           => 'Services must be an array.',
            'services.*.string'        => 'Each service must be a string.',
            'services.*.max'           => 'Each service cannot exceed 50 characters.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        // ✅ Default seats = 1 if not provided
        $numberOfSeats = $request->number_of_seats ?? 1;

        $query = \App\Models\Ride::query();

        // ✅ Exclude rides created by authenticated user (only if logged in)
        if ($user) {
            $query->where('user_id', '!=', $user->id);
        }

        if ($request->pickup_location) {
            $query->where('pickup_location', 'like', '%'.$request->pickup_location.'%');
        }

        if ($request->destination) {
            $query->where('destination', 'like', '%'.$request->destination.'%');
        }

        if ($request->ride_date) {
            try {
                $rideDate = Carbon::createFromFormat('d-m-Y', $request->ride_date)->format('Y-m-d');
                $query->whereDate('ride_date', $rideDate);
            } catch (\Exception $e) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Invalid ride_date format. Please use DD-MM-YYYY.',
                ], 422);
            }
        }

        // ✅ Always apply seat filter
        $query->where('number_of_seats', '>=', $numberOfSeats);

        // ✅ Optional: Filter by services
        if ($request->services && is_array($request->services)) {
            foreach ($request->services as $service) {
                $query->whereJsonContains('services', $service);
            }
        }

        $rides = $query->orderBy('ride_date', 'asc')
                    ->orderBy('ride_time', 'asc')
                    ->get();

        $ridesData = $rides->map(function ($ride) use ($request) {
            $vehicle = Vehicle::find($ride->vehicle_id);
            $driver  = $vehicle ? User::find($vehicle->user_id) : null;

            $totalPrice = $request->number_of_seats 
                        ? $ride->price * $request->number_of_seats 
                        : $ride->price;

            return [
                'ride_id'         => $ride->id,
                'pickup_location' => $ride->pickup_location,
                'destination'     => $ride->destination,
                'number_of_seats' => $ride->number_of_seats,
                'price'           => $totalPrice,
                'ride_date'       => $ride->ride_date,
                'ride_time'       => $ride->ride_time,
                'services'        => $ride->services_details,
                'accept_parcel'   => $ride->accept_parcel,

                // Vehicle
                'vehicle_id'    => $vehicle->id ?? null,
                'brand'         => $vehicle->brand ?? null,
                'model'         => $vehicle->model ?? null,
                'vehicle_image' => $vehicle->vehicle_image ?? null,
                'vehicle_type'  => $vehicle->vehicle_type ?? null,
                'number_plate'  => $vehicle->number_plate ?? null,

                // Driver
                'driver_id'     => $driver->id ?? null,
                'driver_name'   => $driver->name ?? null,
                'driver_image'  => $driver->image ?? null,
                'driver_status' => $driver ? ($driver->id_verified ? 'verified' : 'not verified') : null,
                'driver_rating' => '3',
            ];
        });

        return response()->json([
            'status'  => true,
            'message' => 'Rides found successfully.',
            'data'    => $ridesData,
        ], 200);
    }


    public function searchParcelRides(Request $request)
    {
        $user = Auth::guard('api')->user(); // user may be null

        $validator = Validator::make($request->all(), [
            'pickup_location' => 'nullable|string|max:255',
            'destination'     => 'nullable|string|max:255',
            'ride_date'       => 'nullable|date_format:d-m-Y|after_or_equal:today',
            'number_of_seats' => 'nullable|integer|min:1',
            'services'        => 'nullable|array',
            'services.*'      => 'string|max:50',
        ], [
            'pickup_location.string'   => 'Pickup location must be a valid string.',
            'pickup_location.max'      => 'Pickup location must not exceed 255 characters.',
            'destination.string'       => 'Destination must be a valid string.',
            'destination.max'          => 'Destination must not exceed 255 characters.',
            'ride_date.date_format'    => 'Ride date must be in DD-MM-YYYY format.',
            'ride_date.after_or_equal' => 'Ride date must be today or a future date.',
            'number_of_seats.integer'  => 'Number of seats must be a valid number.',
            'number_of_seats.min'      => 'Number of seats must be at least 1.',
            'services.array'           => 'Services must be an array.',
            'services.*.string'        => 'Each service must be a string.',
            'services.*.max'           => 'Each service cannot exceed 50 characters.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ], 201);
        }

        // ✅ Default seats = 1 if not provided
        $numberOfSeats = $request->number_of_seats ?? 1;

        $query = \App\Models\Ride::query();

        // ✅ Only rides that accept parcels
        $query->where('accept_parcel', 1);

        // ✅ Exclude rides of logged-in user (only if authenticated)
        if ($user) {
            $query->where('user_id', '!=', $user->id);
        }

        if ($request->pickup_location) {
            $query->where('pickup_location', 'like', '%'.$request->pickup_location.'%');
        }

        if ($request->destination) {
            $query->where('destination', 'like', '%'.$request->destination.'%');
        }

        if ($request->ride_date) {
            try {
                $rideDate = Carbon::createFromFormat('d-m-Y', $request->ride_date)->format('Y-m-d');
                $query->whereDate('ride_date', $rideDate);
            } catch (\Exception $e) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Invalid ride_date format. Please use DD-MM-YYYY.',
                ], 201);
            }
        }

        // ✅ Always apply seat filter
        $query->where('number_of_seats', '>=', $numberOfSeats);

        // ✅ Optional: Filter by services
        if ($request->services && is_array($request->services)) {
            foreach ($request->services as $service) {
                $query->whereJsonContains('services', $service);
            }
        }

        $rides = $query->orderBy('ride_date', 'asc')
                    ->orderBy('ride_time', 'asc')
                    ->get();

        $ridesData = $rides->map(function ($ride) use ($request) {
            $vehicle = Vehicle::find($ride->vehicle_id);
            $driver  = $vehicle ? User::find($vehicle->user_id) : null;

            $totalPrice = $request->number_of_seats 
                        ? $ride->price * $request->number_of_seats 
                        : $ride->price;

            return [
                'ride_id'         => $ride->id,
                'pickup_location' => $ride->pickup_location,
                'destination'     => $ride->destination,
                'number_of_seats' => $ride->number_of_seats,
                'price'           => $totalPrice,
                'ride_date'       => $ride->ride_date,
                'ride_time'       => $ride->ride_time,
                'services'        => $ride->services_details,
                'accept_parcel'   => $ride->accept_parcel,

                // Vehicle
                'vehicle_id'    => $vehicle->id ?? null,
                'brand'         => $vehicle->brand ?? null,
                'model'         => $vehicle->model ?? null,
                'vehicle_image' => $vehicle->vehicle_image ?? null,
                'vehicle_type'  => $vehicle->vehicle_type ?? null,
                'number_plate'  => $vehicle->number_plate ?? null,

                // Driver
                'driver_id'     => $driver->id ?? null,
                'driver_name'   => $driver->name ?? null,
                'driver_image'  => $driver->image ?? null,
                'driver_status' => $driver ? ($driver->id_verified ? 'verified' : 'not verified') : null,
                'driver_rating' => '3',
            ];
        });

        return response()->json([
            'status'  => true,
            'message' => 'Parcel rides found successfully.',
            'data'    => $ridesData,
        ], 200);
    }


    // public function driverDetails(Request $request)
    // {
    //     $userId = $request->query(key: 'user_id');

    //     $validator = Validator::make(['user_id' => $userId], [
    //         'user_id' => 'required|integer|exists:users,id',
    //     ], [
    //         'user_id.required' => 'User ID is required.',
    //         'user_id.integer'  => 'User ID must be a valid integer.',
    //         'user_id.exists'   => 'Driver not found.',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => $validator->errors()->first(),
    //         ], 201);
    //     }

    //     $driver = User::find($userId);
    //     $vehicle = Vehicle::where('user_id', $driver->id)->first();

    //     // Base driver & vehicle info
    //     $responseData = [
    //         'driver_id'      => $driver->id,
    //         'name'           => $driver->name,
    //         'image'          => $driver->image,
    //         'phone_number'   => $driver->phone_number,
    //         'dob'            => $driver->dob,
    //         'gender'         => $driver->gender,
    //         'vehicle_id'     => $vehicle->id ?? null,
    //         'brand'          => $vehicle->brand ?? null,
    //         'model'          => $vehicle->model ?? null,
    //         'vehicle_image'  => $vehicle->vehicle_image ?? null,
    //         'vehicle_type'   => $vehicle->vehicle_type ?? null,
    //         'number_plate'   => $vehicle->number_plate ?? null,
    //     ];

    //     // Fetch rides for this driver and merge directly
    //     $rides = Ride::where('vehicle_id', $vehicle->id ?? 0)
    //                 ->orderBy('ride_date', 'asc')
    //                 ->orderBy('ride_time', 'asc')
    //                 ->get();

    //     foreach ($rides as $ride) {
    //         $rideData = [
    //             'ride_id'          => $ride->id,
    //             'pickup_location'  => $ride->pickup_location,
    //             'destination'      => $ride->destination,
    //             'number_of_seats'  => $ride->number_of_seats,
    //             'price'            => $ride->price * $ride->number_of_seats,
    //             'ride_date'        => $ride->ride_date,
    //             'ride_time'        => $ride->ride_time,
    //             'services'         => $ride->services_details,
    //             'accept_parcel'    => $ride->accept_parcel,
    //             'vehicle_id'       => $vehicle->id ?? null,
    //             'brand'            => $vehicle->brand ?? null,
    //             'model'            => $vehicle->model ?? null,
    //             'vehicle_image'    => $vehicle->vehicle_image ?? null,
    //             'vehicle_type'     => $vehicle->vehicle_type ?? null,
    //             'number_plate'     => $vehicle->number_plate ?? null,
    //             'driver_id'        => $driver->id,
    //             'driver_status'    => $driver->id_verified ,
    //             'driver_rating'    => '3',
    //             'phone_number'     => $driver->phone_number,
    //             'id_verified'      => $driver->id_verified,
    //         ];

    //         // Merge ride data into the same responseData
    //         $responseData = array_merge($responseData, $rideData);
    //     }

    //     return response()->json([
    //         'status'  => true,
    //         'message' => 'Driver details fetched successfully.',
    //         'data'    => $responseData,
    //     ], 200);
    // }


    public function driverDetails(Request $request)
    {
        $userId = $request->query('user_id');

        $validator = Validator::make(['user_id' => $userId], [
            'user_id' => 'required|integer|exists:users,id',
        ], [
            'user_id.required' => 'User ID is required.',
            'user_id.integer'  => 'User ID must be a valid integer.',
            'user_id.exists'   => 'Driver not found.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ], 201);
        }

        $driver = User::find($userId);
        $vehicle = Vehicle::where('user_id', $driver->id)->first();

        // Fetch rides
        $rides = Ride::where('vehicle_id', $vehicle->id ?? 0)
                    ->orderBy('ride_date', 'asc')
                    ->orderBy('ride_time', 'asc')
                    ->get();

        $data = [];

        if ($rides->count() > 0) {
            foreach ($rides as $ride) {
                $rideData = [
                    'driver_id'      => $driver->id,
                    'name'           => $driver->name,
                    'image'          => $driver->image,
                    'phone_number'   => $driver->phone_number,
                    'dob'            => $driver->dob,
                    'gender'         => $driver->gender,
                    // 'id_verified'         => $driver->id_verified,
                    'vehicle_id'     => $vehicle->id ?? null,
                    'brand'          => $vehicle->brand ?? null,
                    'model'          => $vehicle->model ?? null,
                    'vehicle_image'  => $vehicle->vehicle_image ?? null,
                    'vehicle_type'   => $vehicle->vehicle_type ?? null,
                    'number_plate'   => $vehicle->number_plate ?? null,
                    'ride_id'        => $ride->id,
                    'pickup_location'=> $ride->pickup_location,
                    'destination'    => $ride->destination,
                    'number_of_seats'=> $ride->number_of_seats,
                    'price'          => $ride->price * $ride->number_of_seats,
                    'ride_date'      => $ride->ride_date,
                    'ride_time'      => $ride->ride_time,
                    'services'       => $ride->services_details,
                    'accept_parcel'  => $ride->accept_parcel,
                    'id_verified'    => $driver->id_verified,
                ];

                $data[] = $rideData;
            }
        } else {
            // No rides, still return driver & vehicle info
            $data[] = [
                'driver_id'      => $driver->id,
                'name'           => $driver->name,
                'image'          => $driver->image,
                'phone_number'   => $driver->phone_number,
                'dob'            => $driver->dob,
                'gender'         => $driver->gender,
                'vehicle_id'     => $vehicle->id ?? null,
                'brand'          => $vehicle->brand ?? null,
                'model'          => $vehicle->model ?? null,
                'vehicle_image'  => $vehicle->vehicle_image ?? null,
                'vehicle_type'   => $vehicle->vehicle_type ?? null,
                'number_plate'   => $vehicle->number_plate ?? null,
                'id_verified'    => $driver->id_verified,

            ];
        }

        return response()->json([
            'status'  => true,
            'message' => 'Driver details fetched successfully.',
            'data'    => $data,
        ], 200);
    }











}
