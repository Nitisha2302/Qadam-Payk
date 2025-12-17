<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PassengerRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
   use App\Models\RideBooking;
   use App\Models\UserBlock;
use Illuminate\Support\Facades\DB;
use App\Models\UserLang;
use App\Services\FCMService;

class PassengerRequestController extends Controller
{
    // Create a ride or parcel request commomn

    // Ride request API (type 0)
    public function createRideRequest(Request $request)
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json(['status'=>false,'message'=>__('messages.createRideRequest.user_not_authenticated')],401);
        }

         // Determine language per device
        $userLang = UserLang::where('user_id', $user->id)
            ->where('device_id', $user->device_id)
            ->where('device_type', $user->device_type)
            ->first();

        $lang = $userLang->language ?? 'ru'; // fallback to Russian
        app()->setLocale($lang);

        $validator = Validator::make($request->all(), [
            'pickup_location' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'ride_date' => 'required|date_format:d-m-Y|after_or_equal:today',
            // 'ride_time' => 'required|date_format:H:i', // uncomment if needed
            'number_of_seats' => 'nullable|integer|min:1',
            'services' => 'nullable|array',
            'services.*'      => 'exists:services,id', // validate IDs exist
            'budget' => 'required|numeric|min:0',
            'preferred_time' => 'nullable|date_format:H:i',
        ], [
            'pickup_location.required' => __('messages.createRideRequest.validation.pickup_location_required'),
            'pickup_location.string' => __('messages.createRideRequest.validation.pickup_location_string'),
            'pickup_location.max' => __('messages.createRideRequest.validation.pickup_location_max'),

            'destination.required' => __('messages.createRideRequest.validation.destination_required'),
            'destination.string' => __('messages.createRideRequest.validation.destination_string'),
            'destination.max' => __('messages.createRideRequest.validation.destination_max'),

            'ride_date.required' => __('messages.createRideRequest.validation.ride_date_required'),
            'ride_date.date_format' => __('messages.createRideRequest.validation.ride_date_format'),
            'ride_date.after_or_equal' => __('messages.createRideRequest.validation.ride_date_after_or_equal'),

            'number_of_seats.integer' => __('messages.ride.validation.number_of_seats_integer'),
            'number_of_seats.min' => __('messages.ride.validation.number_of_seats_min'),

            'services.array' => __('messages.createRideRequest.validation.services_array'),
            'services.*.exists' => __('messages.createRideRequest.validation.services_exists'),

            'budget.required' => __('messages.createRideRequest.validation.budget_required'),
            'budget.numeric' => __('messages.createRideRequest.validation.budget_numeric'),
            'budget.min' => __('messages.createRideRequest.validation.budget_min'),

            'preferred_time.date_format' => __('messages.createRideRequest.validation.preferred_time_format'),
        ]);

        if ($validator->fails()) {
            return response()->json(['status'=>false,'message'=>$validator->errors()->first()],201);
        }

        $data = $request->only(['pickup_location','destination','ride_date','number_of_seats','services','budget','preferred_time']);
        $data['user_id'] = $user->id;
        $data['type'] = 0;
        $data['ride_date'] = Carbon::createFromFormat('d-m-Y', $data['ride_date'])->format('Y-m-d');
        $data['number_of_seats'] = $data['number_of_seats'] ?? 1;
        $data['services'] = $data['services'] ?? [];

        $requestModel = PassengerRequest::create($data);
        return response()->json([
            'status'  => true,
           'message' => __('messages.createRideRequest.success'),
            'data'    => [
                'id'              => $requestModel->id,
                'pickup_location' => $requestModel->pickup_location,
                'destination'     => $requestModel->destination,
                'ride_date'       => $requestModel->ride_date,
                'number_of_seats' => $requestModel->number_of_seats,
                'services'        => $requestModel->services_details, // ✅ full service objects
                'budget'          => $requestModel->budget,
                'preferred_time'  => $requestModel->preferred_time,
                'user_id'         => $requestModel->user_id,
                'type'            => $requestModel->type,
                'created_at'      => $requestModel->created_at,
                'updated_at'      => $requestModel->updated_at,
            ]
        ], 200);
    }



    // Parcel request API (type 1)
    public function createParcelRequest(Request $request)
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json(['status'=>false,'message'=>__('messages.createParcelRequest.user_not_authenticated')],401);
        }

        // Determine language per device
        $userLang = UserLang::where('user_id', $user->id)
            ->where('device_id', $user->device_id)
            ->where('device_type', $user->device_type)
            ->first();

        $lang = $userLang->language ?? 'ru'; // fallback to Russian
        app()->setLocale($lang);

        $validator = Validator::make($request->all(), [
            'pickup_location' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'ride_date' => 'required|date_format:d-m-Y|after_or_equal:today',
            'ride_time' => 'required|date_format:H:i',
            'pickup_contact_name' => 'required|string|max:255',
            'pickup_contact_no' => 'required|string|max:20',
            'drop_contact_name' => 'required|string|max:255',
            'drop_contact_no' => 'required|string|max:20',
            'parcel_details' => 'required|string',
            'parcel_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'budget' => 'required|numeric|min:0',
            'preferred_time' => 'nullable|date_format:H:i',
        ], [
            'pickup_location.required' => __('messages.createParcelRequest.validation.pickup_location_required'),
        'pickup_location.string' => __('messages.createParcelRequest.validation.pickup_location_string'),
        'pickup_location.max' => __('messages.createParcelRequest.validation.pickup_location_max'),

        'destination.required' => __('messages.createParcelRequest.validation.destination_required'),
        'destination.string' => __('messages.createParcelRequest.validation.destination_string'),
        'destination.max' => __('messages.createParcelRequest.validation.destination_max'),

        'ride_date.required' => __('messages.createParcelRequest.validation.ride_date_required'),
        'ride_date.date_format' => __('messages.createParcelRequest.validation.ride_date_format'),
        'ride_date.after_or_equal' => __('messages.createParcelRequest.validation.ride_date_after_or_equal'),

        'ride_time.required' => __('messages.createParcelRequest.validation.ride_time_required'),
        'ride_time.date_format' => __('messages.createParcelRequest.validation.ride_time_format'),

        'pickup_contact_name.required' => __('messages.createParcelRequest.validation.pickup_contact_name_required'),
        'pickup_contact_no.required' => __('messages.createParcelRequest.validation.pickup_contact_no_required'),
        'drop_contact_name.required' => __('messages.createParcelRequest.validation.drop_contact_name_required'),
        'drop_contact_no.required' => __('messages.createParcelRequest.validation.drop_contact_no_required'),

        'parcel_details.required' => __('messages.createParcelRequest.validation.parcel_details_required'),
        'parcel_images.*.image' => __('messages.createParcelRequest.validation.parcel_images_image'),
        'parcel_images.*.mimes' => __('messages.createParcelRequest.validation.parcel_images_mimes'),
        'parcel_images.*.max' => __('messages.createParcelRequest.validation.parcel_images_max'),

        'budget.required' => __('messages.createParcelRequest.validation.budget_required'),
        'budget.numeric' => __('messages.createParcelRequest.validation.budget_numeric'),
        'budget.min' => __('messages.createParcelRequest.validation.budget_min'),

        'preferred_time.date_format' => __('messages.createParcelRequest.validation.preferred_time_format'),
        ]);

        if ($validator->fails()) {
            return response()->json(['status'=>false,'message'=>$validator->errors()->first()],201);
        }

        $data = $request->only([
            'pickup_location','destination','ride_date','ride_time',
            'pickup_contact_name','pickup_contact_no','drop_contact_name','drop_contact_no','parcel_details','budget','preferred_time'
        ]);

        $data['user_id'] = $user->id;
        $data['type'] = 1;
        $data['ride_date'] = Carbon::createFromFormat('d-m-Y', $data['ride_date'])->format('Y-m-d');

        // Handle multiple parcel images
        if ($request->hasFile('parcel_images')) {
            $images = [];
            foreach ($request->file('parcel_images') as $image) {
                $filename = time().'_'.uniqid().'.'.$image->getClientOriginalExtension();
                $image->move(public_path('assets/parcel_image/'), $filename);
                $images[] = $filename;
            }
            $data['parcel_images'] = json_encode($images); // saves as ["A.png","B.png"]
        } else {
            $data['parcel_images'] = null;
        }

        $requestModel = PassengerRequest::create($data);

        return response()->json([
            'status'=>true,
             'message' => __('messages.createParcelRequest.success'),
            'data'=> $requestModel,
        ], 200);
    }

    
    // Get all ride requests (type = 0) with user info merged 
    // public function getAllRideRequests(Request $request)
    // {
     
    //     $user = Auth::guard('api')->user();
    //     if (!$user) {
    //         return response()->json(['status'=>false,'message'=>__('messages.createParcelRequest.user_not_authenticated')],401);
    //     }

    //     // Determine language per device
    //     $userLang = UserLang::where('user_id', $user->id)
    //         ->where('device_id', $user->device_id)
    //         ->where('device_type', $user->device_type)
    //         ->first();

    //     $lang = $userLang->language ?? 'ru'; // fallback to Russian
    //     app()->setLocale($lang);

    //     // ✅ Read filters from query parameters
    //     $pickup_location = $request->query('pickup_location');
    //     $destination     = $request->query('destination');
    //     $ride_date       = $request->query('ride_date');
    //     $number_of_seats = $request->query('number_of_seats', 1); // default 1

    //     // ✅ Validate inputs
    //     $validator = Validator::make([
    //         'pickup_location' => $pickup_location,
    //         'destination'     => $destination,
    //         'ride_date'       => $ride_date,
    //         'number_of_seats' => $number_of_seats,
    //     ], [
    //         'pickup_location' => 'required|string|max:255',
    //         'destination'     => 'required|string|max:255',
    //         'ride_date'       => 'required|date_format:d-m-Y|after_or_equal:today',
    //         'number_of_seats' => 'nullable|integer|min:1',
    //     ], [
    //        'pickup_location.required' => __('messages.getAllRideRequests.validation.pickup_location_required'),
    //         'pickup_location.string'   => __('messages.getAllRideRequests.validation.pickup_location_string'),
    //         'pickup_location.max'      => __('messages.getAllRideRequests.validation.pickup_location_max'),

    //         'destination.required' => __('messages.getAllRideRequests.validation.destination_required'),
    //         'destination.string'   => __('messages.getAllRideRequests.validation.destination_string'),
    //         'destination.max'      => __('messages.getAllRideRequests.validation.destination_max'),

    //         'ride_date.required' => __('messages.getAllRideRequests.validation.ride_date_required'),
    //         'ride_date.date_format' => __('messages.getAllRideRequests.validation.ride_date_format'),
    //         'ride_date.after_or_equal' => __('messages.getAllRideRequests.validation.ride_date_after_or_equal'),

    //         'number_of_seats.integer' => __('messages.getAllRideRequests.validation.number_of_seats_integer'),
    //         'number_of_seats.min'     => __('messages.getAllRideRequests.validation.number_of_seats_min'),
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => $validator->errors()->first(),
    //         ], 201);
    //     }

    //     $query = PassengerRequest::with('user')
    //         ->where('type', 0)
    //         ->where('status', 'pending')
    //         ->where('pickup_location', 'like', '%'.$pickup_location.'%')
    //         ->where('destination', 'like', '%'.$destination.'%')
    //         ->where('number_of_seats', '>=', $number_of_seats);

    //     // ✅ Filter by ride_date
    //     try {
    //         $rideDate = Carbon::createFromFormat('d-m-Y', $ride_date)->format('Y-m-d');
    //         $query->whereDate('ride_date', $rideDate);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status'  => false,
    //              'message' => __('messages.getAllRideRequests.invalid_ride_date_format'),
    //         ], 422);
    //     }

    //     $rides = $query->orderBy('ride_date', 'asc')
    //                 ->orderBy('ride_time', 'asc')
    //                 ->get();

    //     $ridesData = $rides->map(function ($ride) {
    //         $rideArray = [
    //             'id'              => $ride->id,
    //             'pickup_location' => $ride->pickup_location,
    //             'destination'     => $ride->destination,
    //             'ride_date'       => $ride->ride_date,
    //             'number_of_seats' => $ride->number_of_seats,
    //             'services'        => $ride->services_details,
    //             'budget'          => $ride->budget,
    //             'preferred_time'  => $ride->preferred_time,
    //             'type'            => $ride->type,
    //             'status'          => $ride->status,
    //             'created_at'      => $ride->created_at,
    //             'updated_at'      => $ride->updated_at,
    //             'user_id'         => $ride->user_id, // keep user_id
    //         ];

    //         // Merge user fields directly into ride array
    //         if ($ride->user) {
    //             $userData = $ride->user->only([
    //                 'image','name','phone_number','is_phone_verify','email',
    //                 'role','dob','gender','government_id','id_verified',
    //                 'vehicle_number','vehicle_type'
    //             ]);
    //             $rideArray = array_merge($rideArray, $userData);
    //         }

    //         return $rideArray;
    //     });



    //     return response()->json([
    //         'status'  => true,
    //          'message' => __('messages.getAllRideRequests.ride_requests_retrieved'),
    //         'data'    => $ridesData,
    //     ],200);
    // }


    // Get all parcel requests (type = 1) with user info merged
    // public function getAllParcelRequests(Request $request)
    // {

    //     $user = Auth::guard('api')->user();
    //     if (!$user) {
    //         return response()->json(['status'=>false,'message'=>__('messages.createParcelRequest.user_not_authenticated')],401);
    //     }

    //     // Determine language per device
    //     $userLang = UserLang::where('user_id', $user->id)
    //         ->where('device_id', $user->device_id)
    //         ->where('device_type', $user->device_type)
    //         ->first();

    //     $lang = $userLang->language ?? 'ru'; // fallback to Russian
    //     app()->setLocale($lang);
    //     // ✅ Read filters from query parameters
    //     $pickup_location = $request->query('pickup_location');
    //     $destination     = $request->query('destination');
    //     $ride_date       = $request->query('ride_date');
    //     $number_of_seats = $request->query('number_of_seats', 1);

    //     // ✅ Validate inputs
    //     $validator = Validator::make([
    //         'pickup_location' => $pickup_location,
    //         'destination'     => $destination,
    //         'ride_date'       => $ride_date,
    //         'number_of_seats' => $number_of_seats,
    //     ], [
    //         'pickup_location' => 'required|string|max:255',
    //         'destination'     => 'required|string|max:255',
    //         'ride_date'       => 'required|date_format:d-m-Y|after_or_equal:today',
    //         'number_of_seats' => 'nullable|integer|min:1',
    //     ], [
    //         'pickup_location.required' => __('messages.getAllParcelRequests.validation.pickup_location_required'),
    //         'pickup_location.string'   => __('messages.getAllParcelRequests.validation.pickup_location_string'),
    //         'pickup_location.max'      => __('messages.getAllParcelRequests.validation.pickup_location_max'),

    //         'destination.required' => __('messages.getAllParcelRequests.validation.destination_required'),
    //         'destination.string'   => __('messages.getAllParcelRequests.validation.destination_string'),
    //         'destination.max'      => __('messages.getAllParcelRequests.validation.destination_max'),

    //         'ride_date.required' => __('messages.getAllParcelRequests.validation.ride_date_required'),
    //         'ride_date.date_format' => __('messages.getAllParcelRequests.validation.ride_date_format'),
    //         'ride_date.after_or_equal' => __('messages.getAllParcelRequests.validation.ride_date_after_or_equal'),

    //         'number_of_seats.integer' => __('messages.getAllParcelRequests.validation.number_of_seats_integer'),
    //         'number_of_seats.min'     => __('messages.getAllParcelRequests.validation.number_of_seats_min'),
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => $validator->errors()->first(),
    //         ], 201);
    //     }

    //     $query = PassengerRequest::with('user')
    //         ->where('type', 1)
    //         ->where('status', 'pending')
    //         ->where('pickup_location', 'like', '%'.$pickup_location.'%')
    //         ->where('destination', 'like', '%'.$destination.'%')
    //         ->where('number_of_seats', '>=', $number_of_seats);

    //     // ✅ Filter by ride_date
    //     try {
    //         $rideDate = Carbon::createFromFormat('d-m-Y', $ride_date)->format('Y-m-d');
    //         $query->whereDate('ride_date', $rideDate);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status'  => false,
    //            'message' => __('messages.getAllParcelRequests.invalid_ride_date_format'),
    //         ], 422);
    //     }

    //     $parcels = $query->orderBy('ride_date', 'asc')
    //                     ->orderBy('ride_time', 'asc')
    //                     ->get();

    //        $parcelsData = $parcels->map(function ($parcel) {
    //         $parcelArray = [
    //             'id'              => $parcel->id,
    //             'pickup_location' => $parcel->pickup_location,
    //             'destination'     => $parcel->destination,
    //             'ride_date'       => $parcel->ride_date,
    //             'number_of_seats' => $parcel->number_of_seats,
    //             'services'        => $parcel->services_details,
    //             'budget'          => $parcel->budget,
    //             'preferred_time'  => $parcel->preferred_time,
    //             'type'            => $parcel->type,
    //             'status'          => $parcel->status,
    //             'created_at'      => $parcel->created_at,
    //             'updated_at'      => $parcel->updated_at,
    //             'user_id'         => $parcel->user_id, // keep user_id
    //         ];

    //         // Merge user fields directly into ride array
    //         if ($parcel->user) {
    //             $userData = $parcel->user->only([
    //                 'image','name','phone_number','is_phone_verify','email',
    //                 'role','dob','gender','government_id','id_verified',
    //                 'vehicle_number','vehicle_type'
    //             ]);
    //             $parcelArray = array_merge($parcelArray, $userData);
    //         }

    //         return $parcelArray;
    //     });


    //     return response()->json([
    //         'status'  => true,
    //        'message' => __('messages.getAllParcelRequests.parcel_requests_retrieved'),
    //         'data'    => $parcelsData,
    //     ],200);
    // }


    // with auth user or not both  previous is correct


    public function getAllRideRequests(Request $request)
    {
     
        $user = Auth::guard('api')->user();
        // if (!$user) {
        //     return response()->json(['status'=>false,'message'=>__('messages.createParcelRequest.user_not_authenticated')],401);
        // }

        // Determine language per device
      // ✅ Set default language if user not logged in
            $lang = 'ru';

            if ($user) {
                // ✅ Detect user's language only if logged in
                $userLang = UserLang::where('user_id', $user->id)
                    ->where('device_id', $user->device_id)
                    ->where('device_type', $user->device_type)
                    ->first();

                $lang = $userLang->language ?? 'ru';
            }

            app()->setLocale($lang);

        // ✅ Read filters from query parameters
        $pickup_location = $request->query('pickup_location');
        $destination     = $request->query('destination');
        $ride_date       = $request->query('ride_date');
        $number_of_seats = $request->query('number_of_seats', 1); // default 1

        // ✅ Validate inputs
        $validator = Validator::make([
            'pickup_location' => $pickup_location,
            'destination'     => $destination,
            'ride_date'       => $ride_date,
            'number_of_seats' => $number_of_seats,
        ], [
            'pickup_location' => 'required|string|max:255',
            'destination'     => 'required|string|max:255',
            'ride_date'       => 'required|date_format:d-m-Y|after_or_equal:today',
            'number_of_seats' => 'nullable|integer|min:1',
        ], [
           'pickup_location.required' => __('messages.getAllRideRequests.validation.pickup_location_required'),
            'pickup_location.string'   => __('messages.getAllRideRequests.validation.pickup_location_string'),
            'pickup_location.max'      => __('messages.getAllRideRequests.validation.pickup_location_max'),

            'destination.required' => __('messages.getAllRideRequests.validation.destination_required'),
            'destination.string'   => __('messages.getAllRideRequests.validation.destination_string'),
            'destination.max'      => __('messages.getAllRideRequests.validation.destination_max'),

            'ride_date.required' => __('messages.getAllRideRequests.validation.ride_date_required'),
            'ride_date.date_format' => __('messages.getAllRideRequests.validation.ride_date_format'),
            'ride_date.after_or_equal' => __('messages.getAllRideRequests.validation.ride_date_after_or_equal'),

            'number_of_seats.integer' => __('messages.getAllRideRequests.validation.number_of_seats_integer'),
            'number_of_seats.min'     => __('messages.getAllRideRequests.validation.number_of_seats_min'),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ], 201);
        }

        $query = PassengerRequest::with('user')
            ->where('type', 0)
            ->where('status', 'pending')
            ->where('pickup_location', 'like', '%'.$pickup_location.'%')
            ->where('destination', 'like', '%'.$destination.'%')
            ->where('number_of_seats', '>=', $number_of_seats);

        // ✅ Filter by ride_date
        try {
            $rideDate = Carbon::createFromFormat('d-m-Y', $ride_date)->format('Y-m-d');
            $query->whereDate('ride_date', $rideDate);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                 'message' => __('messages.getAllRideRequests.invalid_ride_date_format'),
            ], 422);
        }

         // ✅ EXCLUDE blocked users (both directions)
        if ($user) {
            $blockedUserIds = UserBlock::where('user_id', $user->id)
                ->pluck('blocked_user_id')
                ->toArray();

            $blockedByUserIds = UserBlock::where('blocked_user_id', $user->id)
                ->pluck('user_id')
                ->toArray();

            $allBlockedIds = array_unique(array_merge($blockedUserIds, $blockedByUserIds));

            if (!empty($allBlockedIds)) {
                $query->whereNotIn('user_id', $allBlockedIds);
            }
        }


        $rides = $query->orderBy('ride_date', 'asc')
                    ->orderBy('ride_time', 'asc')
                    ->get();

        $ridesData = $rides->map(function ($ride) {
            $rideArray = [
                'id'              => $ride->id,
                'pickup_location' => $ride->pickup_location,
                'destination'     => $ride->destination,
                'ride_date'       => $ride->ride_date,
                'number_of_seats' => $ride->number_of_seats,
                'services'        => $ride->services_details,
                'budget'          => $ride->budget,
                'preferred_time'  => $ride->preferred_time,
                'type'            => $ride->type,
                'status'          => $ride->status,
                'created_at'      => $ride->created_at,
                'updated_at'      => $ride->updated_at,
                'user_id'         => $ride->user_id, // keep user_id
            ];

            // Merge user fields directly into ride array
            if ($ride->user) {
                $userData = $ride->user->only([
                    'image','name','phone_number','is_phone_verify','email',
                    'role','dob','gender','government_id','id_verified',
                    'vehicle_number','vehicle_type'
                ]);
                $rideArray = array_merge($rideArray, $userData);
            }

            return $rideArray;
        });



        return response()->json([
            'status'  => true,
             'message' => __('messages.getAllRideRequests.ride_requests_retrieved'),
            'data'    => $ridesData,
        ],200);
    }

    public function getAllParcelRequests(Request $request)
    {

        $user = Auth::guard('api')->user();
        // if (!$user) {
        //     return response()->json(['status'=>false,'message'=>__('messages.createParcelRequest.user_not_authenticated')],401);
        // }

        // ✅ Set default language if user not logged in
        $lang = 'ru';

        if ($user) {
            // ✅ Detect user's language only if logged in
            $userLang = UserLang::where('user_id', $user->id)
                ->where('device_id', $user->device_id)
                ->where('device_type', $user->device_type)
                ->first();

            $lang = $userLang->language ?? 'ru';
        }

        app()->setLocale($lang);
        // ✅ Read filters from query parameters
        $pickup_location = $request->query('pickup_location');
        $destination     = $request->query('destination');
        $ride_date       = $request->query('ride_date');
        $number_of_seats = $request->query('number_of_seats', 1);

        // ✅ Validate inputs
        $validator = Validator::make([
            'pickup_location' => $pickup_location,
            'destination'     => $destination,
            'ride_date'       => $ride_date,
            'number_of_seats' => $number_of_seats,
        ], [
            'pickup_location' => 'required|string|max:255',
            'destination'     => 'required|string|max:255',
            'ride_date'       => 'required|date_format:d-m-Y|after_or_equal:today',
            'number_of_seats' => 'nullable|integer|min:1',
        ], [
            'pickup_location.required' => __('messages.getAllParcelRequests.validation.pickup_location_required'),
            'pickup_location.string'   => __('messages.getAllParcelRequests.validation.pickup_location_string'),
            'pickup_location.max'      => __('messages.getAllParcelRequests.validation.pickup_location_max'),

            'destination.required' => __('messages.getAllParcelRequests.validation.destination_required'),
            'destination.string'   => __('messages.getAllParcelRequests.validation.destination_string'),
            'destination.max'      => __('messages.getAllParcelRequests.validation.destination_max'),

            'ride_date.required' => __('messages.getAllParcelRequests.validation.ride_date_required'),
            'ride_date.date_format' => __('messages.getAllParcelRequests.validation.ride_date_format'),
            'ride_date.after_or_equal' => __('messages.getAllParcelRequests.validation.ride_date_after_or_equal'),

            'number_of_seats.integer' => __('messages.getAllParcelRequests.validation.number_of_seats_integer'),
            'number_of_seats.min'     => __('messages.getAllParcelRequests.validation.number_of_seats_min'),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ], 201);
        }

        $query = PassengerRequest::with('user')
            ->where('type', 1)
            ->where('status', 'pending')
            ->where('pickup_location', 'like', '%'.$pickup_location.'%')
            ->where('destination', 'like', '%'.$destination.'%')
            ->where('number_of_seats', '>=', $number_of_seats);

        // ✅ Filter by ride_date
        try {
            $rideDate = Carbon::createFromFormat('d-m-Y', $ride_date)->format('Y-m-d');
            $query->whereDate('ride_date', $rideDate);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
               'message' => __('messages.getAllParcelRequests.invalid_ride_date_format'),
            ], 422);
        }

        // ✅ EXCLUDE blocked users (same logic)
        if ($user) {
            // ✅ Users that the logged-in user has blocked
            $blockedUserIds = UserBlock::where('user_id', $user->id)
                ->pluck('blocked_user_id')
                ->toArray();

            // ✅ Users who have blocked the logged-in user
            $blockedByUserIds = UserBlock::where('blocked_user_id', $user->id)
                ->pluck('user_id')
                ->toArray();

            // ✅ Combine both lists (unique)
            $allBlockedIds = array_unique(array_merge($blockedUserIds, $blockedByUserIds));
            // ✅ Exclude both sides — i.e. don't show any requests created by blocked users OR by users who blocked me
            if (!empty($allBlockedIds)) {
                $query->whereNotIn('user_id', $allBlockedIds)
                    ->whereNotIn('driver_id', $allBlockedIds); // in case driver_id stores request owner in some cases
            }
        }


        $parcels = $query->orderBy('ride_date', 'asc')
                        ->orderBy('ride_time', 'asc')
                        ->get();

           $parcelsData = $parcels->map(function ($parcel) {
            $parcelArray = [
                'id'              => $parcel->id,
                'pickup_location' => $parcel->pickup_location,
                'destination'     => $parcel->destination,
                'ride_date'       => $parcel->ride_date,
                'number_of_seats' => $parcel->number_of_seats,
                'services'        => $parcel->services_details,
                'budget'          => $parcel->budget,
                'preferred_time'  => $parcel->preferred_time,
                'type'            => $parcel->type,
                'status'          => $parcel->status,
                'created_at'      => $parcel->created_at,
                'updated_at'      => $parcel->updated_at,
                'user_id'         => $parcel->user_id, // keep user_id
            ];

            // Merge user fields directly into ride array
            if ($parcel->user) {
                $userData = $parcel->user->only([
                    'image','name','phone_number','is_phone_verify','email',
                    'role','dob','gender','government_id','id_verified',
                    'vehicle_number','vehicle_type'
                ]);
                $parcelArray = array_merge($parcelArray, $userData);
            }

            return $parcelArray;
        });


        return response()->json([
            'status'  => true,
           'message' => __('messages.getAllParcelRequests.parcel_requests_retrieved'),
            'data'    => $parcelsData,
        ],200);
    }

    // List all requests of the current passenger
    public function listCurrentPassengerRequests(Request $request)
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => __('messages.listCurrentPassengerRequests.user_not_authenticated')
            ], 401);
        }
        // Determine language per device
        $userLang = UserLang::where('user_id', $user->id)
            ->where('device_id', $user->device_id)
            ->where('device_type', $user->device_type)
            ->first();

        $lang = $userLang->language ?? 'ru'; // fallback to Russian
        app()->setLocale($lang);

        // $requests = PassengerRequest::where('user_id', $user->id)
        //     ->orderBy('ride_date', 'asc')
        //     ->get();

      $requests = PassengerRequest::where('user_id', $user->id)
        ->orderBy('ride_date', 'asc')
        ->get()
        ->map(function ($requestModel) {
            $data = $requestModel->toArray();
            // overwrite 'services' with expanded details
            $data['services'] = $requestModel->services_details;
            return $data;
        });
        return response()->json([
            'status' => true,
                   'message' => __('messages.listCurrentPassengerRequests.requests_retrieved'),
            'data' => $requests
        ], 200);
    }

  
    // Driver accepts a passenger request

    public function updateRequestInterestStatus(Request $request)
    {
        $driver = Auth::guard('api')->user();
        if (!$driver) {
            return response()->json(['status'=>false,'message'=>__('messages.listCurrentPassengerRequests.user_not_authenticated')],401);
        }

        // Determine language per device
        $userLang = UserLang::where('user_id', $driver->id)
            ->where('device_id', $driver->device_id)
            ->where('device_type', $driver->device_type)
            ->first();

        $lang = $userLang->language ?? 'ru'; // fallback to Russian
        app()->setLocale($lang);
        

        // Validate input
        $validator = Validator::make($request->all(), [
            'request_id' => 'required|exists:passenger_requests,id',
        ], [
            'request_id.required' => __('messages.updateRequestInterestStatus.validation.request_id_required'),
            'request_id.exists'   => __('messages.updateRequestInterestStatus.validation.request_id_exists'),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first()
            ], 201);
        }


        $requestModel = PassengerRequest::find($request->request_id);

        $alreadyInterested = $requestModel->interests()->where('driver_id', $driver->id)->exists();
        if ($alreadyInterested) {
            return response()->json(['status'=>false,'message'=>__('messages.updateRequestInterestStatus.already_interested')],201);
        }

        $requestModel->interests()->create([
            'driver_id' => $driver->id,
        ]);

        // ----------------------
        // ✅ Send notification to passenger
        // ----------------------
        $passenger = $requestModel->user; // passenger who made the request
        $driverName = $driver->name ? $driver->name :  __('messages.updateRequestInterestStatus.default_driver_name');


        // if ($passenger && $passenger->device_token) {
        
        //     $fcmService = new \App\Services\FCMService();

        //     $notificationData = [
        //         'notification_type' => 3, // new interest notification
        //         'title' => "Driver interested",
        //         'body'  => "{$driverName} has expressed interest in your ride request from {$requestModel->pickup_location} to {$requestModel->destination}.Please Confirm",
        //     ];

        //     $fcmService->sendNotification([
        //         [
        //             'device_token' => $passenger->device_token,
        //             'device_type'  => $passenger->device_type ?? 'android',
        //             'user_id'      => $passenger->id,
        //         ]
        //     ], $notificationData);
        // }


        if ($passenger && $passenger->device_token) {

            // ✅ Get passenger's language
            $passengerLang = UserLang::where('user_id', $passenger->id)
                ->where('device_id', $passenger->device_id)
                ->where('device_type', $passenger->device_type)
                ->first();

            $passengerLocale = $passengerLang->language ?? 'ru';

            // ✅ Temporarily switch app locale to passenger language for notification
            app()->setLocale($passengerLocale);

            // ✅ Prepare translated notification
            $notificationData = [
                'notification_type' => 3,
                'title' => __('messages.updateRequestInterestStatus.notification.title'),
                'body'  => __('messages.updateRequestInterestStatus.notification.body', [
                    'driverName' => $driverName,
                    'pickup'     => $requestModel->pickup_location,
                    'destination'=> $requestModel->destination,
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

            // ✅ Restore locale back to driver (optional)
            app()->setLocale($lang);
        }

    

        return response()->json(['status'=>true,'message'=>__('messages.updateRequestInterestStatus.success')],200);
    }


    public function getInterestedDrivers($request_id)
    {

        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => __('messages.getInterestedDrivers.user_not_authenticated')
            ], 401);
        }
        
        // Determine language per device
        $userLang = UserLang::where('user_id', $user->id)
            ->where('device_id', $user->device_id)
            ->where('device_type', $user->device_type)
            ->first();

        $lang = $userLang->language ?? 'ru'; // fallback to Russian
        app()->setLocale($lang);

        $requestModel = PassengerRequest::with(['interests.driver.vehicle'])->find($request_id);

        if (!$requestModel) {
            return response()->json([
                'status'  => false,
                'message' => 'Request not found.'
            ], 404);
        }



    
       // Ride details
        $rideDetails = [
            'ride_id'         => $requestModel->id,
            'pickup_location' => $requestModel->pickup_location,
            'destination'     => $requestModel->destination,
            'ride_date'       => $requestModel->ride_date,
            'ride_time'       => $requestModel->ride_time,
            'number_of_seats' => $requestModel->number_of_seats,
            'services'        => $requestModel->services_details,
            'budget'          => $requestModel->budget,
            'preferred_time'  => $requestModel->preferred_time,
            'user_id'         => $requestModel->user_id,
            'status'          => $requestModel->status,
        ];
        // Merge ride + driver + vehicle
        $data = $requestModel->interests->map(function ($interest) use ($rideDetails) {
            $driver = $interest->driver;
            if (!$driver) return null;

             $driverData = [
                'driver_id'      => $driver->id,
                'name'           => $driver->name,
                'phone_number'   => $driver->phone_number,
                'email'          => $driver->email,
                'image'          => $driver->image,
                'dob'            => $driver->dob,
                'gender'         => $driver->gender,
                'id_verified'    => $driver->id_verified,
                'is_phone_verify' => $driver->is_phone_verify,
                'device_type'    => $driver->device_type,
                'device_id'      => $driver->device_id,
                
            ];

            $vehicleData = $driver->vehicle ? [
                'vehicle_number' => $driver->vehicle->vehicle_number,
                'vehicle_type'   => $driver->vehicle->vehicle_type,
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
        })
        ->filter()
        ->values();
    

        return response()->json([
            'status'  => true,
            'message' => 'Interested drivers with ride details fetched successfully.',
            'data'    => $data
        ], 200);
    }   


    // public function confirmDriverByPassenger(Request $request)
    // {
    //     $passenger = Auth::guard('api')->user();
    //     if (!$passenger) {
    //         return response()->json([
    //             'status' => false,
    //            'message' => __('messages.confirmDriverByPassenger.passenger_not_authenticated')
    //         ], 401);
    //     }
    //     // Determine language per device
    //     $userLang = UserLang::where('user_id', $passenger->id)
    //         ->where('device_id', $passenger->device_id)
    //         ->where('device_type', $passenger->device_type)
    //         ->first();

    //     $lang = $userLang->language ?? 'ru'; // fallback to Russian
    //     app()->setLocale($lang);

    //     $validator = Validator::make($request->all(), [
    //         'request_id' => 'required|exists:passenger_requests,id',
    //         'driver_id'  => 'required|exists:users,id',
    //         'status'     => 'required|in:confirmed,declined',
    //     ], [
    //         'request_id.required' => __('messages.confirmDriverByPassenger.validation.request_id_required'),
    //         'request_id.exists'   => __('messages.confirmDriverByPassenger.validation.request_id_exists'),
    //         'driver_id.required'  => __('messages.confirmDriverByPassenger.validation.driver_id_required'),
    //         'driver_id.exists'    => __('messages.confirmDriverByPassenger.validation.driver_id_exists'),
    //         'status.required'     => __('messages.confirmDriverByPassenger.validation.status_required'),
    //         'status.in'           => __('messages.confirmDriverByPassenger.validation.status_in'),
    //     ]);


    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => $validator->errors()->first()
    //         ], 201);
    //     }

    //     $requestModel = PassengerRequest::with('interests')
    //         ->where('id', $request->request_id)
    //         ->where('user_id', $passenger->id)
    //         ->first();

    //     if (!$requestModel) {
    //         return response()->json([
    //             'status' => false,
    //            'message' => __('messages.confirmDriverByPassenger.request_not_found')
    //         ], 201);
    //     }

    //      // ✅ Prevent confirming again if already confirmed
    //     if ($requestModel->status === 'confirmed') {
    //         return response()->json([
    //             'status' => false,
    //             'message' => __('messages.confirmDriverByPassenger.already_confirmed')
    //         ], 201);
    //     }

    //     $driverInterest = $requestModel->interests()
    //         ->where('driver_id', $request->driver_id)
    //         ->first();

    //     if (!$driverInterest) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => __('messages.confirmDriverByPassenger.driver_not_interested')
    //         ], 201);
    //     }

    //    $driver = \App\Models\User::find($request->driver_id);
    //    $passengerName = $passenger->name ? $passenger->name : "Passenger";
    //     if ($request->status === 'declined') {
    //         // Delete the interest if declined
    //         $driverInterest->delete();

    //          // Send notification to driver
    //         if ($driver && $driver->device_token) {
    //             $fcmService = new \App\Services\FCMService();
    //             $fcmService->sendNotification([
    //                 [
    //                     'device_token' => $driver->device_token,
    //                     'device_type'  => $driver->device_type ?? 'android',
    //                     'user_id'      => $driver->id,
    //                 ]
    //             ], [
    //                 'notification_type' => 5, // passenger declined
    //                 'title' => "Ride Request Declined",
    //                 'body'  => "{$passengerName} has declined your interest for the ride from {$requestModel->pickup_location} to {$requestModel->destination}.",
    //             ]);
    //         }

    //         return response()->json([
    //             'status' => true,
    //             'message' => __('messages.confirmDriverByPassenger.declined_success')
    //         ], 200);
    //     }
    //    // If confirmed, create booking, update request, and remove all other interests
    //     DB::transaction(function () use ($requestModel, $request) {
    //         $requestModel->driver_id = $request->driver_id;
    //         $requestModel->status = 'confirmed';
    //         $requestModel->save();

    //         RideBooking::create([
    //             'type' => $requestModel->type,
    //             'user_id' => $request->driver_id,
    //             'seats_booked' => $requestModel->number_of_seats,
    //             'price' => $requestModel->budget,
    //             'services' => json_encode($requestModel->services ?? []),
    //                'ride_date'     => \Carbon\Carbon::parse($requestModel->ride_date)->format('Y-m-d'), // ✅ fixed format
    //            'ride_time'     => $requestModel->ride_time ?? null,
    //             'request_id'     => $requestModel->id ?? null,
    //             'status' => 'confirmed',
    //         ]);
    //         // Delete all other driver interests for this request
    //         $requestModel->interests()
    //             ->where('driver_id', '!=', $request->driver_id)
    //             ->delete();
    //        });
      

    //       // Send notification to driver
    //     if ($driver && $driver->device_token) {
    //         $fcmService = new \App\Services\FCMService();
    //         $fcmService->sendNotification([
    //             [
    //                 'device_token' => $driver->device_token,
    //                 'device_type'  => $driver->device_type ?? 'android',
    //                 'user_id'      => $driver->id,
    //             ]
    //         ], [
    //             'notification_type' => 4, // passenger confirmed
    //             'title' => "Ride Request Confirmed",
    //             'body'  => "{$passengerName} has confirmed your interest for ride from {$requestModel->pickup_location} to {$requestModel->destination}.",
    //         ]);
    //     }
    //     return response()->json([
    //         'status' => true,
    //          'message' => __('messages.confirmDriverByPassenger.success')
    //     ],200);
    // }


    public function confirmDriverByPassenger(Request $request)
    {
        $passenger = Auth::guard('api')->user();
        if (!$passenger) {
            return response()->json([
                'status' => false,
               'message' => __('messages.confirmDriverByPassenger.passenger_not_authenticated')
            ], 401);
        }
        // Determine language per device
        $userLang = UserLang::where('user_id', $passenger->id)
            ->where('device_id', $passenger->device_id)
            ->where('device_type', $passenger->device_type)
            ->first();

        $lang = $userLang->language ?? 'ru'; // fallback to Russian
        app()->setLocale($lang);

        $validator = Validator::make($request->all(), [
            'request_id' => 'required|exists:passenger_requests,id',
            'driver_id'  => 'required|exists:users,id',
            'status'     => 'required|in:confirmed,declined',
        ], [
            'request_id.required' => __('messages.confirmDriverByPassenger.validation.request_id_required'),
            'request_id.exists'   => __('messages.confirmDriverByPassenger.validation.request_id_exists'),
            'driver_id.required'  => __('messages.confirmDriverByPassenger.validation.driver_id_required'),
            'driver_id.exists'    => __('messages.confirmDriverByPassenger.validation.driver_id_exists'),
            'status.required'     => __('messages.confirmDriverByPassenger.validation.status_required'),
            'status.in'           => __('messages.confirmDriverByPassenger.validation.status_in'),
        ]);


        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 201);
        }

        $requestModel = PassengerRequest::with('interests')
            ->where('id', $request->request_id)
            ->where('user_id', $passenger->id)
            ->first();

        if (!$requestModel) {
            return response()->json([
                'status' => false,
               'message' => __('messages.confirmDriverByPassenger.request_not_found')
            ], 201);
        }

         // ✅ Prevent confirming again if already confirmed
        if ($requestModel->status === 'confirmed') {
            return response()->json([
                'status' => false,
                'message' => __('messages.confirmDriverByPassenger.already_confirmed')
            ], 201);
        }

        $driverInterest = $requestModel->interests()
            ->where('driver_id', $request->driver_id)
            ->first();

        if (!$driverInterest) {
            return response()->json([
                'status' => false,
                'message' => __('messages.confirmDriverByPassenger.driver_not_interested')
            ], 201);
        }

       $driver = \App\Models\User::find($request->driver_id);
       $passengerName = $passenger->name ? $passenger->name : "Passenger";
        if ($request->status === 'declined') {
            // Delete the interest if declined
            $driverInterest->delete();

            if ($driver && $driver->device_token) {
                // ✅ Get driver's preferred language
                $driverLang = UserLang::where('user_id', $driver->id)
                    ->where('device_id', $driver->device_id)
                    ->where('device_type', $driver->device_type)
                    ->first();

                $driverLocale = $driverLang->language ?? 'ru'; // default fallback
                $originalLocale = app()->getLocale(); // store passenger locale

                // ✅ Switch to driver language for notification
                app()->setLocale($driverLocale);

                $notificationData = [
                    'notification_type' => 5, // passenger declined
                    'title' => __('messages.confirmDriverByPassenger.notification.declined.title'),
                    'body'  => __('messages.confirmDriverByPassenger.notification.declined.body', [
                        'passengerName' => $passengerName,
                        'pickup'        => $requestModel->pickup_location,
                        'destination'   => $requestModel->destination,
                    ]),
                ];

                $fcmService = new \App\Services\FCMService();
                $fcmService->sendNotification([
                    [
                        'device_token' => $driver->device_token,
                        'device_type'  => $driver->device_type ?? 'android',
                        'user_id'      => $driver->id,
                    ]
                ], $notificationData);

                // ✅ Restore original passenger locale
                app()->setLocale($originalLocale);
            }


            return response()->json([
                'status' => true,
                'message' => __('messages.confirmDriverByPassenger.declined_success')
            ], 200);
        }
       // If confirmed, create booking, update request, and remove all other interests
        DB::transaction(function () use ($requestModel, $request) {
            $requestModel->driver_id = $request->driver_id;
            $requestModel->status = 'confirmed';
            $requestModel->save();

            RideBooking::create([
                'type' => $requestModel->type,
                'user_id' => $request->driver_id,
                'seats_booked' => $requestModel->number_of_seats,
                'price' => $requestModel->budget,
                'services' => json_encode($requestModel->services ?? []),
                   'ride_date'     => \Carbon\Carbon::parse($requestModel->ride_date)->format('Y-m-d'), // ✅ fixed format
               'ride_time'     => $requestModel->ride_time ?? null,
                'request_id'     => $requestModel->id ?? null,
                'status' => 'confirmed',
            ]);
            // Delete all other driver interests for this request
            $requestModel->interests()
                ->where('driver_id', '!=', $request->driver_id)
                ->delete();
           });
      

          // Send notification to driver
        if ($driver && $driver->device_token) {

            // ✅ Get driver's language settings
            $driverLang = UserLang::where('user_id', $driver->id)
                ->where('device_id', $driver->device_id)
                ->where('device_type', $driver->device_type)
                ->first();

            $driverLocale = $driverLang->language ?? 'ru'; // fallback
            $originalLocale = app()->getLocale();

            // ✅ Switch to driver’s language
            app()->setLocale($driverLocale);

            $notificationData = [
                'notification_type' => 4, // passenger confirmed
                'title' => __('messages.confirmDriverByPassenger.notification.confirmed.title'),
                'body'  => __('messages.confirmDriverByPassenger.notification.confirmed.body', [
                    'passengerName' => $passengerName,
                    'pickup'        => $requestModel->pickup_location,
                    'destination'   => $requestModel->destination,
                ]),
            ];

            $fcmService = new \App\Services\FCMService();
            $fcmService->sendNotification([
                [
                    'device_token' => $driver->device_token,
                    'device_type'  => $driver->device_type ?? 'android',
                    'user_id'      => $driver->id,
                ]
            ], $notificationData);

            // ✅ Restore passenger language
            app()->setLocale($originalLocale);
        }
        return response()->json([
            'status' => true,
             'message' => __('messages.confirmDriverByPassenger.success')
        ],200);
    }


    public function editPassengerRideRequest(Request $request)
    {
        /* =====================================================
        🔐 AUTH CHECK
        ===================================================== */
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.createRideRequest.user_not_authenticated')
            ], 401);
        }

        /* =====================================================
        🌐 LANGUAGE
        ===================================================== */
        $userLang = UserLang::where('user_id', $user->id)
            ->where('device_id', $user->device_id)
            ->where('device_type', $user->device_type)
            ->first();

        app()->setLocale($userLang->language ?? 'ru');

        /* =====================================================
        ✅ VALIDATION (MATCH STORE API)
        ===================================================== */
        $validator = Validator::make($request->all(), [
            'request_id' => 'required|exists:passenger_requests,id',

            // Always editable
            'pickup_contact_name' => 'nullable|string|max:255',
            'pickup_contact_no'   => 'nullable|string|max:30',
            'drop_contact_name'   => 'nullable|string|max:255',
            'drop_contact_no'     => 'nullable|string|max:30',

            // Same rules as store
            'pickup_location' => 'nullable|string|max:255',
            'destination'     => 'nullable|string|max:255',
            'ride_date'       => 'nullable|date_format:d-m-Y|after_or_equal:today',
            'number_of_seats' => 'nullable|integer|min:1',
            'services'        => 'nullable|array',
            'services.*'      => 'exists:services,id',
            'budget'          => 'nullable|numeric|min:0',
            'preferred_time'  => 'nullable|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first()
            ], 201);
        }

        /* =====================================================
        🔍 FETCH REQUEST (OWNER CHECK)
        ===================================================== */
        $requestModel = PassengerRequest::where('id', $request->request_id)
            ->where('user_id', $user->id)
            ->first();

        if (!$requestModel) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.createRideRequest.request_not_found')
            ], 201);
        }

        /* =====================================================
        🧹 NORMALIZE INPUT (MATCH STORE API)
        ===================================================== */
        // if ($request->ride_date) {
        //     $rideDate = trim($request->ride_date, '"');
        //     $request->merge([
        //         'ride_date' => Carbon::createFromFormat('d-m-Y', $rideDate)->format('Y-m-d')
        //     ]);
        // }

        if ($request->preferred_time) {
            $request->merge([
                'preferred_time' => Carbon::createFromFormat('H:i', $request->preferred_time)->format('H:i:s')
            ]);
        }

        if ($request->has('number_of_seats')) {
            $request->merge([
                'number_of_seats' => (int) $request->number_of_seats
            ]);
        }

        if ($request->has('budget')) {
            $request->merge([
                'budget' => (float) $request->budget
            ]);
        }

        // 🚫 BLOCK TIME CHANGE
        if ($request->preferred_time !== null &&
            $request->preferred_time !== $requestModel->preferred_time) {
            return response()->json([
                'status' => false,
                'message' => __('messages.createRideRequest.edit_restrictions.only_contacts_allowed')
            ], 403);
        }

        $reqServices = collect($request->services ?? [])->map('strval')->sort()->values()->toArray();
        $dbServices  = collect($requestModel->services ?? [])->map('strval')->sort()->values()->toArray();

        if (count(array_diff($reqServices, $dbServices)) > 0 || count(array_diff($dbServices, $reqServices)) > 0) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.createRideRequest.edit_restrictions.only_contacts_allowed')
            ], 403);
        }



        /* =====================================================
        🔒 BOOKING CHECK
        ===================================================== */
        $hasConfirmedBooking = RideBooking::where('request_id', $requestModel->id)
            ->where('status', 'confirmed')
            ->exists();

        /* =====================================================
        🚫 RESTRICTIONS
        ===================================================== */
        if ($hasConfirmedBooking) {

            if (
                $request->pickup_location !== null &&
                $request->pickup_location !== $requestModel->pickup_location
            ) {
                return response()->json([
                    'status'  => false,
                    'message' => __('messages.createRideRequest.edit_restrictions.only_contacts_allowed')
                ], 403);
            }

            if (
                $request->destination !== null &&
                $request->destination !== $requestModel->destination
            ) {
                return response()->json([
                    'status'  => false,
                    'message' => __('messages.createRideRequest.edit_restrictions.only_contacts_allowed')
                ], 403);
            }

            if ($request->ride_date && $request->ride_date !== $requestModel->ride_date) {
                return response()->json([
                    'status'  => false,
                    'message' => __('messages.createRideRequest.edit_restrictions.only_contacts_allowed')
                ], 403);
            }

            if ($request->number_of_seats !== null && $request->number_of_seats !== (int)$requestModel->number_of_seats) {
                return response()->json([
                    'status'  => false,
                    'message' => __('messages.createRideRequest.edit_restrictions.only_contacts_allowed')
                ], 403);
            }

            if ($request->budget !== null && (float)$request->budget !== (float)$requestModel->budget) {
                return response()->json([
                    'status'  => false,
                    'message' => __('messages.createRideRequest.edit_restrictions.only_contacts_allowed')
                ], 403);
            }

            $reqServices = collect($request->services ?? [])->map('strval')->sort()->values()->toArray();
            $dbServices  = collect($requestModel->services ?? [])->map('strval')->sort()->values()->toArray();

            if (count(array_diff($reqServices, $dbServices)) > 0 || count(array_diff($dbServices, $reqServices)) > 0) {
                return response()->json([
                    'status'  => false,
                    'message' => __('messages.createRideRequest.edit_restrictions.only_contacts_allowed')
                ], 403);
            }

        }
        $requestDate=Carbon::createFromFormat('d-m-Y', $request->ride_date)->format('Y-m-d');
        $requestModalDate=Carbon::createFromFormat('d-m-Y', $requestModel->ride_date)->format('Y-m-d');
        /* =====================================================
        ✅ SAFE UPDATE (LIKE STORE API)
        ===================================================== */
        $requestModel->update([
            // Always editable
            'pickup_contact_name' => $request->pickup_contact_name ?? $requestModel->pickup_contact_name,
            'pickup_contact_no'   => $request->pickup_contact_no ?? $requestModel->pickup_contact_no,
            'drop_contact_name'   => $request->drop_contact_name ?? $requestModel->drop_contact_name,
            'drop_contact_no'     => $request->drop_contact_no ?? $requestModel->drop_contact_no,

            // Editable only if no confirmed booking
            'pickup_location' => !$hasConfirmedBooking ? $request->pickup_location : $requestModel->pickup_location,
            'destination'     => !$hasConfirmedBooking ? $request->destination : $requestModel->destination,
            'ride_date'       => !$hasConfirmedBooking ? $requestDate : $requestModalDate,
            'number_of_seats' => !$hasConfirmedBooking ? $request->number_of_seats : $requestModel->number_of_seats,
            'budget'          => !$hasConfirmedBooking ? $request->budget : $requestModel->budget,
            'preferred_time'  => !$hasConfirmedBooking ? $request->preferred_time : $requestModel->preferred_time,
            'services'        => !$hasConfirmedBooking ? $request->services : $requestModel->services,
        ]);

        /* =====================================================
        ✅ RESPONSE (MATCH STORE API FORMAT)
        ===================================================== */
        return response()->json([
            'status'  => true,
            'message' => __('messages.createRideRequest.update_success'),
            'data'    => [
                'id'              => $requestModel->id,
                'pickup_location' => $requestModel->pickup_location,
                'destination'     => $requestModel->destination,
                'ride_date'       => $requestModel->ride_date,
                'number_of_seats' => $requestModel->number_of_seats,
                'services'        => $requestModel->services_details,
                'budget'          => $requestModel->budget,
                'preferred_time'  => $requestModel->preferred_time,
                'pickup_contact_name' => $requestModel->pickup_contact_name,
                'pickup_contact_no'   => $requestModel->pickup_contact_no,
                'drop_contact_name'   => $requestModel->drop_contact_name,
                'drop_contact_no'     => $requestModel->drop_contact_no,
                'user_id'         => $requestModel->user_id,
                'type'            => $requestModel->type,
                'created_at'      => $requestModel->created_at,
                'updated_at'      => $requestModel->updated_at,
            ]
        ], 200);
    }



    public function editPassengerParcelRequest(Request $request)
    {
        /* =====================================================
        🔐 AUTH CHECK
        ===================================================== */
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.createParcelRequest.user_not_authenticated')
            ], 401);
        }

        /* =====================================================
        🌐 LANGUAGE
        ===================================================== */
        $userLang = UserLang::where('user_id', $user->id)
            ->where('device_id', $user->device_id)
            ->where('device_type', $user->device_type)
            ->first();

        app()->setLocale($userLang->language ?? 'ru');

        /* =====================================================
        ✅ VALIDATION (MATCH STORE API)
        ===================================================== */
        $validator = Validator::make($request->all(), [
            'request_id' => 'required|exists:passenger_requests,id',

            // Always editable
            'pickup_contact_name' => 'nullable|string|max:255',
            'pickup_contact_no'   => 'nullable|string|max:30',
            'drop_contact_name'   => 'nullable|string|max:255',
            'drop_contact_no'     => 'nullable|string|max:30',

            // Same rules as store
            'pickup_location' => 'nullable|string|max:255',
            'destination'     => 'nullable|string|max:255',
            'ride_date'       => 'nullable|date_format:d-m-Y|after_or_equal:today',
            'number_of_seats' => 'nullable|integer|min:1',
            'services'        => 'nullable|array',
            'services.*'      => 'exists:services,id',
            'budget'          => 'nullable|numeric|min:0',
            'preferred_time'  => 'nullable|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first()
            ], 201);
        }

        /* =====================================================
        🔍 FETCH REQUEST (OWNER CHECK)
        ===================================================== */
        $requestModel = PassengerRequest::where('id', $request->request_id)
            ->where('user_id', $user->id)
            ->first();

        if (!$requestModel) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.createParcelRequest.request_not_found')
            ], 201);
        }

        /* =====================================================
        🧹 NORMALIZE INPUT (MATCH STORE API)
        ===================================================== */
        // if ($request->ride_date) {
        //     $rideDate = trim($request->ride_date, '"');
        //     $request->merge([
        //         'ride_date' => Carbon::createFromFormat('d-m-Y', $rideDate)->format('Y-m-d')
        //     ]);
        // }

        if ($request->preferred_time) {
            $request->merge([
                'preferred_time' => Carbon::createFromFormat('H:i', $request->preferred_time)->format('H:i:s')
            ]);
        }

        if ($request->has('number_of_seats')) {
            $request->merge([
                'number_of_seats' => (int) $request->number_of_seats
            ]);
        }

        if ($request->has('budget')) {
            $request->merge([
                'budget' => (float) $request->budget
            ]);
        }

        // 🚫 BLOCK TIME CHANGE
        if ($request->preferred_time !== null &&
            $request->preferred_time !== $requestModel->preferred_time) {
            return response()->json([
                'status' => false,
                'message' => __('messages.createParcelRequest.edit_restrictions.only_contacts_allowed')
            ], 403);
        }

        $reqServices = collect($request->services ?? [])->map('strval')->sort()->values()->toArray();
        $dbServices  = collect($requestModel->services ?? [])->map('strval')->sort()->values()->toArray();

        if (count(array_diff($reqServices, $dbServices)) > 0 || count(array_diff($dbServices, $reqServices)) > 0) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.createParcelRequest.edit_restrictions.only_contacts_allowed')
            ], 403);
        }



        /* =====================================================
        🔒 BOOKING CHECK
        ===================================================== */
        $hasConfirmedBooking = RideBooking::where('request_id', $requestModel->id)
            ->where('status', 'confirmed')
            ->exists();

        /* =====================================================
        🚫 RESTRICTIONS
        ===================================================== */
        if ($hasConfirmedBooking) {

            if (
                $request->pickup_location !== null &&
                $request->pickup_location !== $requestModel->pickup_location
            ) {
                return response()->json([
                    'status'  => false,
                    'message' => __('messages.createParcelRequest.edit_restrictions.only_contacts_allowed')
                ], 403);
            }

            if (
                $request->destination !== null &&
                $request->destination !== $requestModel->destination
            ) {
                return response()->json([
                    'status'  => false,
                    'message' => __('messages.createParcelRequest.edit_restrictions.only_contacts_allowed')
                ], 403);
            }

            if ($request->ride_date && $request->ride_date !== $requestModel->ride_date) {
                return response()->json([
                    'status'  => false,
                    'message' => __('messages.createParcelRequest.edit_restrictions.only_contacts_allowed')
                ], 403);
            }

            if ($request->number_of_seats !== null && $request->number_of_seats !== (int)$requestModel->number_of_seats) {
                return response()->json([
                    'status'  => false,
                    'message' => __('messages.createParcelRequest.edit_restrictions.only_contacts_allowed')
                ], 403);
            }

            if ($request->budget !== null && (float)$request->budget !== (float)$requestModel->budget) {
                return response()->json([
                    'status'  => false,
                    'message' => __('messages.createParcelRequest.edit_restrictions.only_contacts_allowed')
                ], 403);
            }

            $reqServices = collect($request->services ?? [])->map('strval')->sort()->values()->toArray();
            $dbServices  = collect($requestModel->services ?? [])->map('strval')->sort()->values()->toArray();

            if (count(array_diff($reqServices, $dbServices)) > 0 || count(array_diff($dbServices, $reqServices)) > 0) {
                return response()->json([
                    'status'  => false,
                    'message' => __('messages.createParcelRequest.edit_restrictions.only_contacts_allowed')
                ], 403);
            }

        }
        $requestDate=Carbon::createFromFormat('d-m-Y', $request->ride_date)->format('Y-m-d');
        $requestModalDate=Carbon::createFromFormat('d-m-Y', $requestModel->ride_date)->format('Y-m-d');
        /* =====================================================
        ✅ SAFE UPDATE (LIKE STORE API)
        ===================================================== */
        $requestModel->update([
            // Always editable
            'pickup_contact_name' => $request->pickup_contact_name ?? $requestModel->pickup_contact_name,
            'pickup_contact_no'   => $request->pickup_contact_no ?? $requestModel->pickup_contact_no,
            'drop_contact_name'   => $request->drop_contact_name ?? $requestModel->drop_contact_name,
            'drop_contact_no'     => $request->drop_contact_no ?? $requestModel->drop_contact_no,

            // Editable only if no confirmed booking
            'pickup_location' => !$hasConfirmedBooking ? $request->pickup_location : $requestModel->pickup_location,
            'destination'     => !$hasConfirmedBooking ? $request->destination : $requestModel->destination,
            'ride_date'       => !$hasConfirmedBooking ? $requestDate : $requestModalDate,
            'number_of_seats' => !$hasConfirmedBooking ? $request->number_of_seats : $requestModel->number_of_seats,
            'budget'          => !$hasConfirmedBooking ? $request->budget : $requestModel->budget,
            'preferred_time'  => !$hasConfirmedBooking ? $request->preferred_time : $requestModel->preferred_time,
            'services'        => !$hasConfirmedBooking ? $request->services : $requestModel->services,
        ]);

        /* =====================================================
        ✅ RESPONSE (MATCH STORE API FORMAT)
        ===================================================== */
        return response()->json([
            'status'  => true,
            'message' => __('messages.createParcelRequest.update_success'),
            'data'    => [
                'id'              => $requestModel->id,
                'pickup_location' => $requestModel->pickup_location,
                'destination'     => $requestModel->destination,
                'ride_date'       => $requestModel->ride_date,
                'number_of_seats' => $requestModel->number_of_seats,
                'services'        => $requestModel->services_details,
                'budget'          => $requestModel->budget,
                'preferred_time'  => $requestModel->preferred_time,
                'pickup_contact_name' => $requestModel->pickup_contact_name,
                'pickup_contact_no'   => $requestModel->pickup_contact_no,
                'drop_contact_name'   => $requestModel->drop_contact_name,
                'drop_contact_no'     => $requestModel->drop_contact_no,
                'user_id'         => $requestModel->user_id,
                'type'            => $requestModel->type,
                'created_at'      => $requestModel->created_at,
                'updated_at'      => $requestModel->updated_at,
            ]
        ], 200);
    }


    // public function deletePassengerRideRequest(Request $request)
    // {
    //     /* =====================================================
    //     🔐 AUTH CHECK
    //     ===================================================== */
    //     $user = Auth::guard('api')->user();
    //     if (!$user) {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => __('messages.createParcelRequest.user_not_authenticated')
    //         ], 401);
    //     }

    //     /* =====================================================
    //     🌐 LANGUAGE
    //     ===================================================== */
    //     $userLang = UserLang::where('user_id', $user->id)
    //         ->where('device_id', $user->device_id)
    //         ->where('device_type', $user->device_type)
    //         ->first();

    //     app()->setLocale($userLang->language ?? 'ru');

    //     /* =====================================================
    //     ✅ VALIDATION
    //     ===================================================== */
    //     $validator = Validator::make($request->all(), [
    //         'request_id' => 'required|exists:passenger_requests,id',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => $validator->errors()->first()
    //         ], 201);
    //     }

    //     /* =====================================================
    //     🔍 FETCH REQUEST (OWNER CHECK)
    //     ===================================================== */
    //     $requestModel = PassengerRequest::where('id', $request->request_id)
    //         ->where('user_id', $user->id)
    //         ->first();

    //     if (!$requestModel) {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => __('messages.createParcelRequest.request_not_found')
    //         ], 201);
    //     }

    //     /* =====================================================
    //     🔒 CONFIRMED BOOKING CHECK
    //     ===================================================== */
    //     $hasConfirmedBooking = RideBooking::where('request_id', $requestModel->id)
    //         ->where('status', 'confirmed')
    //         ->exists();

    //     if ($hasConfirmedBooking) {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => __('messages.createParcelRequest.delete_restrictions.confirmed_booking_exists')
    //         ], 403);
    //     }

    //     /* =====================================================
    //     🗑️ DELETE REQUEST
    //     ===================================================== */
    //     $requestModel->delete();

    //     /* =====================================================
    //     ✅ RESPONSE
    //     ===================================================== */
    //     return response()->json([
    //         'status'  => true,
    //         'message' => __('messages.createParcelRequest.delete_success')
    //     ], 200);
    // }

    public function deletePassengerRideRequest(Request $request)
    {
        /* =====================================================
        🔐 AUTH CHECK
        ===================================================== */
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.createParcelRequest.user_not_authenticated')
            ], 401);
        }

        /* =====================================================
        🌐 LANGUAGE
        ===================================================== */
        $userLang = UserLang::where('user_id', $user->id)
            ->where('device_id', $user->device_id)
            ->where('device_type', $user->device_type)
            ->first();

        app()->setLocale($userLang->language ?? 'ru');

        /* =====================================================
        ✅ VALIDATION
        ===================================================== */
        $validator = Validator::make($request->all(), [
            'request_id' => 'required|exists:passenger_requests,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first()
            ], 201);
        }

        /* =====================================================
        🔍 FETCH REQUEST (OWNER CHECK)
        ===================================================== */
        $requestModel = PassengerRequest::where('id', $request->request_id)
            ->where('user_id', $user->id)
            ->first();

        if (!$requestModel) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.createParcelRequest.request_not_found')
            ], 201);
        }

        /* =====================================================
        🔒 CONFIRMED BOOKING CHECK
        ===================================================== */
        $hasConfirmedBooking = RideBooking::where('request_id', $requestModel->id)
            ->where('status', 'confirmed')
            ->exists();

        if ($hasConfirmedBooking) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.createParcelRequest.delete_restrictions.confirmed_booking_exists')
            ], 201);
        }

        /* =====================================================
        🧹 DELETE RELATED BOOKINGS (SAFE)
        ===================================================== */
        RideBooking::where('request_id', $requestModel->id)->delete();

        /* =====================================================
        🗑️ DELETE REQUEST
        ===================================================== */
        $requestModel->delete();

        /* =====================================================
        ✅ RESPONSE
        ===================================================== */
        return response()->json([
            'status'  => true,
            'message' => __('messages.createParcelRequest.delete_success')
        ], 200);
    }


    public function cancelPassengerRideRequest(Request $request)
    {
        /* =====================================================
        🔐 AUTH CHECK
        ===================================================== */
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.createParcelRequest.user_not_authenticated')
            ], 401);
        }

        /* =====================================================
        🌐 LANGUAGE
        ===================================================== */
        $userLang = UserLang::where('user_id', $user->id)
            ->where('device_id', $user->device_id)
            ->where('device_type', $user->device_type)
            ->first();

        app()->setLocale($userLang->language ?? 'ru');

        /* =====================================================
        ✅ VALIDATION
        ===================================================== */
        $validator = Validator::make($request->all(), [
            'request_id' => 'required|exists:passenger_requests,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first()
            ], 201);
        }

        /* =====================================================
        🔍 FETCH REQUEST (OWNER CHECK)
        ===================================================== */
        $requestModel = PassengerRequest::where('id', $request->request_id)
            ->where('user_id', $user->id)
            ->first();

        if (!$requestModel) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.createParcelRequest.request_not_found')
            ], 201);
        }

        /* =====================================================
        📦 FETCH BOOKINGS (FOR NOTIFICATION)
        ===================================================== */
        $bookings = RideBooking::where('request_id', $requestModel->id)
            ->with(['ride.user', 'request.user', 'user']) // preload needed relations
            ->get();


        if ($bookings->isEmpty()) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.createParcelRequest.cancel_restrictions.no_booking_found')
            ], 201);
        }

        // 2️⃣ 🚫 BLOCK ACTIVE / COMPLETED
        $hasLockedBooking = $bookings->whereIn('active_status', [1, 2])->count() > 0;

        if ($hasLockedBooking) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.createParcelRequest.cancel_restrictions.booking_in_progress')
            ], 403);
        }

        /* =====================================================
        🔄 CANCEL REQUEST (REQUEST STAYS)
        ===================================================== */
        $requestModel->update([
            'status'    => 'pending',   // mark request as cancelled
            'driver_id' => null,          // remove driver reference
        ]);

        /* =====================================================
        🗑️ DELETE ALL BOOKINGS (CONFIRMED INCLUDED)
        ===================================================== */
        RideBooking::where('request_id', $requestModel->id)->delete();

        /* =====================================================
        🗑️ DELETE RELATED DRIVER INTERESTS
        ===================================================== */
        \DB::table('passenger_request_driver_interests')
            ->where('passenger_request_id', $requestModel->id)
            ->delete();

        /* =====================================================
        🔔 NOTIFY DRIVERS
        ===================================================== */
        $fcmService     = new FCMService();
        $originalLocale = app()->getLocale();
        // ✅ DEFINE PASSENGER NAME
        $passengerName = $user->name ?? __('messages.common.passenger');
        foreach ($bookings as $booking) {

            $driver = $booking->driver;
            if (!$driver || !$driver->device_token) {
                continue;
            }

            // Driver language
            $driverLang = UserLang::where('user_id', $driver->id)
                ->where('device_id', $driver->device_id)
                ->where('device_type', $driver->device_type)
                ->first();

            app()->setLocale($driverLang->language ?? 'ru');

            $notificationData = [
                'notification_type' => 12,
                'title' => __('messages.createParcelRequest.notifications.request_cancelled.title'),
                'body'  => __('messages.createParcelRequest.notifications.request_cancelled.body', [
                    'passenger'  => $passengerName,
                    'pickup'      => $requestModel->pickup_location,
                    'destination' => $requestModel->destination,
                ]),
            ];

            $fcmService->sendNotification([[
                'device_token' => $driver->device_token,
                'device_type'  => $driver->device_type ?? 'android',
                'user_id'      => $driver->id,
            ]], $notificationData);
        }

        app()->setLocale($originalLocale);

        /* =====================================================
        ✅ RESPONSE
        ===================================================== */
        return response()->json([
            'status'  => true,
            'message' => __('messages.createParcelRequest.cancel_success')
        ], 200);
    }


    





    


   
    






    










}
