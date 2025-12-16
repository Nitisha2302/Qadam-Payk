<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Ride;
use App\Models\UserBlock;
use App\Models\Service;
use Carbon\Carbon;
use App\Models\UserLang;
use App\Services\FCMService;

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
                 'message' => __('messages.vehicle.add_vehicle.user_not_authenticated'),
            ], 401);
        }

        // 🔹 Detect user's preferred language from UserLang table
        $userLang = UserLang::where('user_id', $user->id)
            ->where('device_id', $user->device_id)
            ->where('device_type', $user->device_type)
            ->first();

        $lang = $userLang->language ?? 'ru'; // fallback to Russian
        app()->setLocale($lang);

        // ✅ Validate input
        $validator = Validator::make($request->all(), [
            'brand'         => 'required|string|max:255',
            'model'         => 'required|string|max:255',
            'number_plate'  => 'required|string|unique:vehicles,number_plate',
            'vehicle_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            'brand.required'        => __('messages.vehicle.add_vehicle.validation.brand_required'),
            'model.required'        => __('messages.vehicle.add_vehicle.validation.model_required'),
            'number_plate.required' => __('messages.vehicle.add_vehicle.validation.number_plate_required'),
            'number_plate.unique'   => __('messages.vehicle.add_vehicle.validation.number_plate_unique'),
            'vehicle_image.image'   => __('messages.vehicle.add_vehicle.validation.vehicle_image_image'),
            'vehicle_image.mimes'   => __('messages.vehicle.add_vehicle.validation.vehicle_image_mimes'),
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
            'message' => __('messages.vehicle.add_vehicle.success'),
            'data'    => $vehicle,
        ], 200);
    }

    
    // public function getVehicles(Request $request)
    // {
    //     $user = Auth::guard('api')->user();
    //     if (!$user) {
    //         return response()->json([
    //             'status' => false,
    //            'message' => __('messages.vehicle.get_vehicles.user_not_authenticated'),
    //         ], 401);
    //     }

    //      // 🔹 Detect user's preferred language from UserLang table
    //     $userLang = UserLang::where('user_id', $user->id)
    //         ->where('device_id', $user->device_id)
    //         ->where('device_type', $user->device_type)
    //         ->first();

    //     $lang = $userLang->language ?? 'ru'; // fallback to Russian
    //     app()->setLocale($lang);

    //     $vehicles = Vehicle::select('id', 'brand', 'model', 'number_plate', 'vehicle_image')
    //         ->where('user_id', $user->id)
    //         ->get();

    //     return response()->json([
    //         'status'  => true,
    //         'message' => $vehicles->isEmpty()
    //             ? __('messages.vehicle.get_vehicles.no_vehicles_found')
    //             : __('messages.vehicle.get_vehicles.success'),
    //         'data'    => $vehicles,
    //     ], 200);
    // }
    
    // with language 

    public function getVehicles(Request $request)
    {
        // ✅ 1️⃣ Authenticate user
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.vehicle.get_vehicles.user_not_authenticated'),
            ], 401);
        }

        // ✅ 2️⃣ Detect user's preferred language from UserLang table
        $userLang = UserLang::where('user_id', $user->id)
            ->where('device_id', $user->device_id)
            ->where('device_type', $user->device_type)
            ->first();

        // Fallback to Russian
        $lang = $userLang->language ?? 'ru';
        app()->setLocale($lang);

        // ✅ 3️⃣ Fetch user’s vehicles filtered by language_code
        $vehicles = Vehicle::select('id', 'brand', 'model', 'number_plate', 'vehicle_image', 'language_code')
            ->where('user_id', $user->id)
            ->where(function ($q) use ($lang) {
                $q->where('language_code', $lang)
                ->orWhereNull('language_code'); // include universal vehicles
            })
            ->get();

        // ✅ 4️⃣ Return localized response
        return response()->json([
            'status'  => true,
            'message' => $vehicles->isEmpty()
                ? __('messages.vehicle.get_vehicles.no_vehicles_found')
                : __('messages.vehicle.get_vehicles.success'),
            'language_used' => $lang,
            'data'    => $vehicles,
        ], 200);
    }

    public function editVehicle(Request $request)
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'status' => false,
               'message' => __('messages.vehicle.edit_vehicle.user_not_authenticated'),
            ], 401);
        }

         // 🔹 Detect user's preferred language from UserLang table
        $userLang = UserLang::where('user_id', $user->id)
            ->where('device_id', $user->device_id)
            ->where('device_type', $user->device_type)
            ->first();

        $lang = $userLang->language ?? 'ru'; // fallback to Russian
        app()->setLocale($lang);

        // ✅ All fields required except image
        $validator = Validator::make($request->all(), [
            'vehicle_id'    => 'required|exists:vehicles,id',
            'brand'         => 'required|string|max:255',
            'model'         => 'required|string|max:255',
            'number_plate'  => 'required|string|unique:vehicles,number_plate,' . $request->vehicle_id,
            'vehicle_image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ], [
            'vehicle_id.required'   => __('messages.vehicle.edit_vehicle.validation.vehicle_id_required'),
            'vehicle_id.exists'     => __('messages.vehicle.edit_vehicle.validation.vehicle_not_found'),
            'brand.required'        => __('messages.vehicle.edit_vehicle.validation.brand_required'),
            'model.required'        => __('messages.vehicle.edit_vehicle.validation.model_required'),
            'number_plate.required' => __('messages.vehicle.edit_vehicle.validation.number_plate_required'),
            'number_plate.unique'   => __('messages.vehicle.edit_vehicle.validation.number_plate_unique'),
            'vehicle_image.image'   => __('messages.vehicle.edit_vehicle.validation.vehicle_image_invalid'),
            'vehicle_image.mimes'   => __('messages.vehicle.edit_vehicle.validation.vehicle_image_invalid'),
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
                    'message' => __('messages.vehicle.edit_vehicle.vehicle_not_found'),
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
            'message' => __('messages.vehicle.edit_vehicle.success'),
            'data'    => $vehicle,
        ], 200);
    }

    public function createRide(Request $request)
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => __('messages.ride.create.user_not_authenticated'),
            ], 401);
        }

         // 🔹 Detect user's preferred language from UserLang table
        $userLang = UserLang::where('user_id', $user->id)
            ->where('device_id', $user->device_id)
            ->where('device_type', $user->device_type)
            ->first();

        $lang = $userLang->language ?? 'ru'; // fallback to Russian
        app()->setLocale($lang);

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
             'vehicle_id.required'      => __('messages.ride.create.validation.vehicle_id_required'),
            'vehicle_id.exists'        => __('messages.ride.create.validation.vehicle_id_exists'),
            'pickup_location.required' => __('messages.ride.create.validation.pickup_location_required'),
             'destination.required'     => __('messages.ride.create.validation.destination_required'),
            'number_of_seats.required' => __('messages.ride.create.validation.number_of_seats_required'),
            'number_of_seats.integer'  => __('messages.ride.create.validation.number_of_seats_integer'),
             'price.required'           => __('messages.ride.create.validation.price_required'),
            'price.numeric'            => __('messages.ride.create.validation.price_numeric'),
            'ride_date.required'       => __('messages.ride.create.validation.ride_date_required'),
            'ride_date.after_or_equal' => __('messages.ride.create.validation.ride_date_after_or_equal'),
            'ride_time.required'       => __('messages.ride.create.validation.ride_time_required'),
            'ride_time.date_format'    => __('messages.ride.create.validation.ride_time_format'),
            'accept_parcel.boolean'    => __('messages.ride.create.validation.accept_parcel_boolean'),
            'services.*.exists'        => __('messages.ride.create.validation.services_exists'),
            'reaching_time.date_format'=> __('messages.ride.create.validation.reaching_time_format'),

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
             'message' => __('messages.ride.create.success'),
            'data'    => $ride,
        ], 200);
    }


    // public function editRide(Request $request)
    // {
    //     $user = Auth::guard('api')->user();

    //     if (!$user) {
    //         return response()->json([
    //             'status' => false,
    //            'message' => __('messages.ride.edit.user_not_authenticated')
    //         ], 401);
    //     }

    //      // 🔹 Detect user's preferred language from UserLang table
    //     $userLang = UserLang::where('user_id', $user->id)
    //         ->where('device_id', $user->device_id)
    //         ->where('device_type', $user->device_type)
    //         ->first();

    //     $lang = $userLang->language ?? 'ru'; // fallback to Russian
    //     app()->setLocale($lang);

    //     // ✅ Validation with custom messages
    //     $validator = Validator::make($request->all(), [
    //         'ride_id'         => 'required|exists:rides,id',
    //         'vehicle_id'      => 'required|exists:vehicles,id',
    //         'pickup_location' => 'required|string|max:255',
    //         'destination'     => 'required|string|max:255',
    //         'number_of_seats' => 'required|integer|min:1',
    //         'price'           => 'required|numeric|min:0',
    //         'ride_date'       => 'required|date|after_or_equal:today',
    //         'ride_time'       => 'required|date_format:H:i',
    //          'reaching_time'  => 'nullable|date_format:H:i',
    //         'accept_parcel'   => 'nullable|boolean',
    //      'services'        => ['nullable', 'array'],
    //     ], [
    //         'ride_id.required'         => __('messages.ride.edit.validation.ride_id_required'),
    //         'ride_id.exists'           => __('messages.ride.edit.validation.ride_not_found'),
    //         'vehicle_id.required'      => __('messages.ride.edit.validation.vehicle_id_required'),
    //         'vehicle_id.exists'        => __('messages.ride.edit.validation.vehicle_not_found'),
    //         'pickup_location.required' => __('messages.ride.edit.validation.pickup_location_required'),
    //         'destination.required'     => __('messages.ride.edit.validation.destination_required'),
    //         'number_of_seats.required' => __('messages.ride.edit.validation.number_of_seats_required'),
    //         'number_of_seats.integer'  => __('messages.ride.edit.validation.number_of_seats_integer'),
    //         'price.required'           => __('messages.ride.edit.validation.price_required'),
    //         'price.numeric'            => __('messages.ride.edit.validation.price_numeric'),
    //         'ride_date.required'       => __('messages.ride.edit.validation.ride_date_required'),
    //         'ride_date.after_or_equal' => __('messages.ride.edit.validation.ride_date_after_or_equal'),
    //         'ride_time.required'       => __('messages.ride.edit.validation.ride_time_required'),
    //         'ride_time.date_format'    => __('messages.ride.edit.validation.ride_time_format'),
    //         'reaching_time.date_format'=> __('messages.ride.edit.validation.reaching_time_format'),
    //         'accept_parcel.boolean'    => __('messages.ride.edit.validation.accept_parcel_boolean'),
    //         'services.array'           => __('messages.ride.edit.validation.services_array'),
    //         'services.*.exists'        => __('messages.ride.edit.validation.services_exists'),
    //     ]);

        

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => $validator->errors()->first(),
    //         ], 201);
    //     }

    //     // ✅ Find ride
    //     $ride = Ride::where('id', $request->ride_id)
    //                 ->where('user_id', $user->id)
    //                 ->first();

    //     if (!$ride) {
    //         return response()->json([
    //             'status' => false,
    //            'message' => __('messages.ride.edit.ride_not_found'),
    //         ], 201);
    //     }

  
    //      // ✅ Update ride (store only IDs for services)
    //     $ride->update([
    //         'vehicle_id'      => $request->vehicle_id,
    //         'pickup_location' => $request->pickup_location,
    //         'destination'     => $request->destination,
    //         'number_of_seats' => $request->number_of_seats,
    //         'price'           => $request->price,
    //         'ride_date'       => $request->ride_date,
    //         'ride_time'       => $request->ride_time,
    //          'reaching_time' => $request->reaching_time ?? $ride->reaching_time ?? null,
    //         'accept_parcel'   => $request->accept_parcel ?? false,
    //         'services'        => $request->services,
    //     ]);

    //     // ✅ Expand services only for response
    //     $ride->services = Service::whereIn('id', $request->services ?? [])
    //                         ->get(['id','service_name','service_image']);

    //     return response()->json([
    //         'status'  => true,
    //           'message' => __('messages.ride.edit.success'),
    //         'data'    => $ride,
    //     ], 200);
    // }

    // edit ride with restrictions 


    public function editRide(Request $request)
    {
        /* =====================================================
        🔐 AUTH CHECK
        ====================================================== */
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.ride.edit.user_not_authenticated')
            ], 401);
        }

        /* =====================================================
        🌐 LANGUAGE DETECTION
        ====================================================== */
        $userLang = UserLang::where('user_id', $user->id)
            ->where('device_id', $user->device_id)
            ->where('device_type', $user->device_type)
            ->first();

        $lang = $userLang->language ?? 'ru';
        app()->setLocale($lang);

        /* =====================================================
        ✅ VALIDATION
        ====================================================== */
        $validator = Validator::make($request->all(), [
            'ride_id'         => 'required|exists:rides,id',
            'vehicle_id'      => 'required|exists:vehicles,id',
            'pickup_location' => 'required|string|max:255',
            'destination'     => 'required|string|max:255',
            'number_of_seats' => 'required|integer|min:1',
            'price'           => 'required|numeric|min:0',
            'ride_date'       => 'required|date|after_or_equal:today',
            'ride_time'       => 'required|date_format:H:i',
            'reaching_time'   => 'nullable|date_format:H:i',
            'accept_parcel'   => 'nullable|boolean',
            'services'        => 'nullable|array',
        ], [
            'ride_id.required' => __('messages.ride.edit.validation.ride_id_required'),
            'ride_id.exists'   => __('messages.ride.edit.validation.ride_not_found'),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ], 201);
        }

        /* =====================================================
        🚗 FIND RIDE (OWNER CHECK)
        ====================================================== */
        $ride = Ride::where('id', $request->ride_id)
            ->where('user_id', $user->id)
            ->first();

        if (!$ride) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.ride.edit.ride_not_found'),
            ], 201);
        }

        /* =====================================================
        🔒 BOOKING-BASED RULES
        ====================================================== */

        // 🔢 Total CONFIRMED seats booked
        $bookedSeats = $ride->bookings()
            ->where('status', 'confirmed')
            ->sum('seats_booked');

        // 🚫 Ride started or completed
        $rideLocked = $ride->bookings()
            ->whereIn('active_status', [1, 2]) // 1=active, 2=complete
            ->exists();
            
        if ($rideLocked) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.ride.edit.ride_already_started'),
            ], 201);
        }
        // 🚫 ALL SEATS FILLED → NO EDIT ALLOWED
        if ($bookedSeats >= $ride->number_of_seats) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.ride.edit.all_seats_filled'),
            ], 201);
        }

        // 🚫 Restrictions when passengers exist
        if ($bookedSeats > 0) {

            // ❌ Route cannot change
            if (
                $request->pickup_location !== $ride->pickup_location ||
                $request->destination !== $ride->destination
            ) {
                return response()->json([
                    'status'  => false,
                    'message' => __('messages.ride.edit.route_change_not_allowed'),
                ], 201);
            }

            // ❌ Date / Time cannot change
            if (
                $request->ride_date !== $ride->ride_date ||
                $request->ride_time !== $ride->ride_time
            ) {
                return response()->json([
                    'status'  => false,
                    'message' => __('messages.ride.edit.time_change_not_allowed'),
                ], 201);
            }

            // ❌ Seats cannot be less than booked
            if ($request->number_of_seats < $bookedSeats) {
                return response()->json([
                    'status'  => false,
                    'message' => __('messages.ride.edit.seats_less_than_booked'),
                ], 201);
            }
        }

        /* =====================================================
        ✅ SAFE UPDATE
        ====================================================== */
        $ride->update([
            'vehicle_id'      => $request->vehicle_id,
            'price'           => $request->price,
            'number_of_seats' => $request->number_of_seats,
            'reaching_time'   => $request->reaching_time ?? $ride->reaching_time,
            'accept_parcel'   => $request->accept_parcel ?? $ride->accept_parcel,
            'services'        => $request->services,
            'comment'         => $request->comment ?? $ride->comment,

            // Only editable if no confirmed bookings
            'pickup_location' => $bookedSeats == 0 ? $request->pickup_location : $ride->pickup_location,
            'destination'     => $bookedSeats == 0 ? $request->destination : $ride->destination,
            'ride_date'       => $bookedSeats == 0 ? $request->ride_date : $ride->ride_date,
            'ride_time'       => $bookedSeats == 0 ? $request->ride_time : $ride->ride_time,
        ]);

        /* =====================================================
        📦 RESPONSE DATA
        ====================================================== */
        $ride->services = Service::whereIn('id', $request->services ?? [])
            ->get(['id', 'service_name', 'service_image']);

        return response()->json([
            'status'  => true,
            'message' => __('messages.ride.edit.success'),
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
                'message' => __('messages.ride.driver_rides.user_not_authenticated'),
            ], 401);
        }

         // 🔹 Detect user's preferred language from UserLang table
        $userLang = UserLang::where('user_id', $user->id)
            ->where('device_id', $user->device_id)
            ->where('device_type', $user->device_type)
            ->first();

        $lang = $userLang->language ?? 'ru'; // fallback to Russian
        app()->setLocale($lang);

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
            "message" => __('messages.ride.driver_rides.success'),
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
            // ✅ Get list of users blocked by current user
            $blockedUserIds = UserBlock::where('user_id', $user->id)
                ->pluck('blocked_user_id')
                ->toArray();

            // ✅ Get list of users who blocked current user
            $blockedByUserIds = UserBlock::where('blocked_user_id', $user->id)
                ->pluck('user_id')
                ->toArray();

            // ✅ Combine both
            $allBlockedIds = array_unique(array_merge($blockedUserIds, $blockedByUserIds));

            // ✅ Exclude rides from or to blocked users
            if (!empty($allBlockedIds)) {
                $query->whereNotIn('user_id', $allBlockedIds);
            }
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

           // Filter out rides where ANY booking has active_status = 2
            $rides = $rides->filter(function ($ride) {
                return $ride->rideBookings->every(fn($b) => $b->active_status != 2);
            })->values();

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

            // ✅ Get list of users blocked by current user
            $blockedUserIds = UserBlock::where('user_id', $user->id)
                ->pluck('blocked_user_id')
                ->toArray();

            // ✅ Get list of users who blocked current user
            $blockedByUserIds = UserBlock::where('blocked_user_id', $user->id)
                ->pluck('user_id')
                ->toArray();

            // ✅ Combine both
            $allBlockedIds = array_unique(array_merge($blockedUserIds, $blockedByUserIds));

            // ✅ Exclude rides from or to blocked users
            if (!empty($allBlockedIds)) {
                $query->whereNotIn('user_id', $allBlockedIds);
            }
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

        // Filter out rides where any booking has active_status = 2
        $rides = $rides->filter(fn($ride) => $ride->rideBookings->every(fn($b) => $b->active_status != 2))
                    ->values();

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


    public function driverDetails(Request $request)
    {

        $user = Auth::guard('api')->user();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => __('messages.driver.details.user_not_authenticated'),
                ], 401);
            }

            // 🔹 Detect user's preferred language from UserLang table
            $userLang = UserLang::where('user_id', $user->id)
                ->where('device_id', $user->device_id)
                ->where('device_type', $user->device_type)
                ->first();

            $lang = $userLang->language ?? 'ru'; // fallback to Russian
            app()->setLocale($lang);
        
        $userId = $request->query('user_id');

        

        $validator = Validator::make(['user_id' => $userId], [
            'user_id' => 'required|integer|exists:users,id',
        ], [
             'user_id.required' => __('messages.driver.details.validation.user_id_required'),
            'user_id.integer'  => __('messages.driver.details.validation.user_id_integer'),
            'user_id.exists'   => __('messages.driver.details.validation.user_id_exists'),
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
              'message' => __('messages.driver.details.success'),
            'data'    => $data,
        ], 200);
    }


    public function deleteRide(Request $request)
    {
        /* =====================================================
        🔐 AUTH CHECK
        ====================================================== */
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.ride.delete.user_not_authenticated')
            ], 401);
        }

        /* =====================================================
        🌐 LANGUAGE
        ====================================================== */
        $userLang = UserLang::where('user_id', $user->id)
            ->where('device_id', $user->device_id)
            ->where('device_type', $user->device_type)
            ->first();

        app()->setLocale($userLang->language ?? 'ru');

        /* =====================================================
        ✅ VALIDATION
        ====================================================== */
        $validator = Validator::make($request->all(), [
            'ride_id' => 'required|exists:rides,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ], 201);
        }

        /* =====================================================
        🚗 FIND RIDE (OWNER CHECK)
        ====================================================== */
        $ride = Ride::where('id', $request->ride_id)
            ->where('user_id', $user->id)
            ->first();

        if (!$ride) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.ride.delete.ride_not_found'),
            ], 201);
        }

        /* =====================================================
        🔒 BUSINESS RULES
        ====================================================== */

        // 🚫 Started or completed ride
        $rideLocked = $ride->bookings()
            ->whereIn('active_status', [1, 2])
            ->exists();

        if ($rideLocked) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.ride.delete.ride_already_started'),
            ], 201);
        }

        // 🔢 Confirmed passengers
        $confirmedBookings = $ride->bookings()
            ->where('status', 'confirmed')
            ->count();

        // 🚫 Passengers exist → Delete not allowed
        if ($confirmedBookings > 0) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.ride.delete.delete_not_allowed_use_cancel'),
            ], 201);
        }

        /* =====================================================
        🗑️ DELETE RIDE
        ====================================================== */
        $ride->delete();

        return response()->json([
            'status'  => true,
            'message' => __('messages.ride.delete.success'),
        ], 200);
    }


    public function cancelRide(Request $request)
    {
        /* =====================================================
        🔐 AUTH CHECK
        ====================================================== */
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.ride.cancel.user_not_authenticated')
            ], 401);
        }

        /* =====================================================
        🌐 LANGUAGE (Driver)
        ====================================================== */
        $userLang = UserLang::where('user_id', $user->id)
            ->where('device_id', $user->device_id)
            ->where('device_type', $user->device_type)
            ->first();

        app()->setLocale($userLang->language ?? 'ru');

        /* =====================================================
        ✅ VALIDATION
        ====================================================== */
        $validator = Validator::make($request->all(), [
            'ride_id' => 'required|exists:rides,id',
            // 'reason'  => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ], 201);
        }

        /* =====================================================
         FIND RIDE (OWNER CHECK)
        ====================================================== */
        $ride = Ride::where('id', $request->ride_id)
            ->where('user_id', $user->id)
            ->first();

        if (!$ride) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.ride.cancel.ride_not_found'),
            ], 201);
        }

        /* =====================================================
        🔒 BUSINESS RULES
        ====================================================== */

        // 🚫 Started or completed
        $rideLocked = $ride->bookings()
            ->whereIn('active_status', [1, 2])
            ->exists();

        if ($rideLocked) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.ride.cancel.ride_already_started'),
            ], 201);
        }

        // 🚫 No confirmed passengers
        $confirmedBookings = $ride->bookings()
            ->where('status', 'confirmed')
            ->with('user')
            ->get();

        if ($confirmedBookings->count() === 0) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.ride.cancel.no_passengers_use_delete'),
            ], 201);
        }

        /* =====================================================
        🔄 CANCEL RIDE
        ====================================================== */

        // Cancel all bookings
        $ride->bookings()->update([
            'status'        => 'cancelled',
            'active_status' => 0,
        ]);

        /* =====================================================
        🔔 NOTIFY CONFIRMED PASSENGERS
        ====================================================== */

        $fcmService = new FCMService();
        $driverName = $user->name ?? __('messages.ride.notifications.driver');
        $originalLocale = app()->getLocale();

        foreach ($confirmedBookings as $booking) {

            $passenger = $booking->user;
            if (!$passenger || !$passenger->device_token) {
                continue;
            }

            // 🌐 Passenger language
            $passengerLang = UserLang::where('user_id', $passenger->id)
                ->where('device_id', $passenger->device_id)
                ->where('device_type', $passenger->device_type)
                ->first();

            app()->setLocale($passengerLang->language ?? 'ru');

            $notificationData = [
                'notification_type' => 10,
                'title' => __('messages.ride.notifications.ride_cancelled.title'),
                'body'  => __('messages.ride.notifications.ride_cancelled.body', [
                    'driver'      => $driverName,
                    'pickup'      => $ride->pickup_location,
                    'destination' => $ride->destination,
                ]),
            ];

            $fcmService->sendNotification([[
                'device_token' => $passenger->device_token,
                'device_type'  => $passenger->device_type ?? 'android',
                'user_id'      => $passenger->id,
            ]], $notificationData);
        }

        // Restore locale
        app()->setLocale($originalLocale);

        return response()->json([
            'status'  => true,
            'message' => __('messages.ride.cancel.success'),
        ], 200);
    }

    //Cancel ride function end







}
