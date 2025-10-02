<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PassengerRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
   use App\Models\RideBooking;
use Illuminate\Support\Facades\DB;

class PassengerRequestController extends Controller
{
    // Create a ride or parcel request commomn

    // public function createRequest(Request $request)
    // {
    //     // ✅ Get authenticated user
    //     $user = Auth::guard('api')->user();
    //     if (!$user) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'User not authenticated'
    //         ], 401);
    //     }

    //     // ✅ Validate input
    //    $validator = Validator::make($request->all(), [
    //     'type' => 'required|in:0,1', // 0 = ride, 1 = parcel
    //     'ride_date' => 'required|date_format:d-m-Y|after_or_equal:today',
    //     'number_of_seats' => 'nullable|integer|min:1',
    //     'services' => 'nullable|array',
    //     'services.*' => 'string|max:50',

    //     // Required for both ride and parcel
    //     'pickup_location' => 'required|string|max:255',
    //     'destination' => 'required|string|max:255',

    //     // Parcel fields
    //     'pickup_contact_name' => 'required_if:type,1|string|max:255',
    //     'pickup_contact_no' => 'required_if:type,1|string|max:20',
    //     'drop_contact_name' => 'required_if:type,1|string|max:255',
    //     'drop_contact_no' => 'required_if:type,1|string|max:20',
    //     'parcel_details' => 'required_if:type,1|string',
    //     ], [
    //         'type.required' => 'Request type is required.',
    //         'type.in' => 'Type must be 0 (ride) or 1 (parcel).',
    //         'ride_date.required' => 'Ride date is required.',
    //         'ride_date.date_format' => 'Ride date must be in DD-MM-YYYY format.',
    //         'ride_date.after_or_equal' => 'Ride date must be today or a future date.',
    //         'number_of_seats.integer' => 'Number of seats must be a number.',
    //         'number_of_seats.min' => 'Number of seats must be at least 1.',
    //         'services.array' => 'Services must be an array.',
    //         'services.*.string' => 'Each service must be a string.',
    //         'services.*.max' => 'Each service cannot exceed 50 characters.',
            
    //         // Messages for pickup/destination
    //         'pickup_location.required' => 'Pickup location is required.',
    //         'pickup_location.string' => 'Pickup location must be a string.',
    //         'pickup_location.max' => 'Pickup location cannot exceed 255 characters.',
            
    //         'destination.required' => 'Destination is required.',
    //         'destination.string' => 'Destination must be a string.',
    //         'destination.max' => 'Destination cannot exceed 255 characters.',

    //         // Parcel messages
    //         'pickup_contact_name.required_if' => 'Pickup contact name is required for parcel.',
    //         'pickup_contact_no.required_if' => 'Pickup contact number is required for parcel.',
    //         'drop_contact_name.required_if' => 'Drop contact name is required for parcel.',
    //         'drop_contact_no.required_if' => 'Drop contact number is required for parcel.',
    //         'parcel_details.required_if' => 'Parcel details are required for parcel.',
    //     ]); 


    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => $validator->errors()->first()
    //         ], 422);
    //     }

    //     // ✅ Prepare data for saving
    //     $requestData = $request->only([
    //         'type',
    //         'pickup_location',
    //         'destination',
    //         'number_of_seats',
    //         'pickup_contact_name',
    //         'pickup_contact_no',
    //         'drop_contact_name',
    //         'drop_contact_no',
    //         'parcel_details',
    //         'services'
    //     ]);

    //     $requestData['user_id'] = $user->id;
    //     $requestData['ride_date'] = Carbon::createFromFormat('d-m-Y', $request->ride_date)->format('Y-m-d');
    //     $requestData['number_of_seats'] = $requestData['number_of_seats'] ?? 1;
    //     $requestData['services'] = $requestData['services'] ?? [];

    //     // ✅ Create passenger request
    //     $passengerRequest = PassengerRequest::create($requestData);

    //     return response()->json([
    //         'status' => true,
    //         'message' => ($requestData['type'] == 0 ? 'Ride' : 'Parcel') . ' request created successfully',
    //         'data' => $passengerRequest
    //     ], 200);
    // }


    // Ride request API (type 0)
    public function createRideRequest(Request $request)
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json(['status'=>false,'message'=>'User not authenticated'],401);
        }

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
            'pickup_location.required' => 'Pickup location is required.',
            'pickup_location.string' => 'Pickup location must be a string.',
            'pickup_location.max' => 'Pickup location cannot exceed 255 characters.',

            'destination.required' => 'Destination is required.',
            'destination.string' => 'Destination must be a string.',
            'destination.max' => 'Destination cannot exceed 255 characters.',

            'ride_date.required' => 'Ride date is required.',
            'ride_date.date_format' => 'Ride date must be in DD-MM-YYYY format.',
            'ride_date.after_or_equal' => 'Ride date must be today or a future date.',

            'number_of_seats.integer' => 'Number of seats must be a number.',
            'number_of_seats.min' => 'Number of seats must be at least 1.',

            'services.array' => 'Services must be an array.',
            'services.*.string' => 'Each service must be a string.',
            'services.*.max' => 'Each service cannot exceed 50 characters.',

           'budget.required' => 'Budget must be required.',
            'budget.numeric' => 'Budget must be a valid number.',
            'budget.min' => 'Budget must be at least 0.',
            'preferred_time.date_format' => 'Preferred time must be in HH:MM format.',
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
            'message' => 'Ride request created successfully',
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
            return response()->json(['status'=>false,'message'=>'User not authenticated'],401);
        }

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
            'pickup_location.required' => 'Pickup location is required.',
            'destination.required' => 'Destination is required.',
            'ride_date.required' => 'Ride date is required.',
            'ride_date.date_format' => 'Ride date must be in DD-MM-YYYY format.',
            'ride_date.after_or_equal' => 'Ride date must be today or a future date.',
            'ride_time.required' => 'Ride time is required.',
            'ride_time.date_format' => 'Ride time must be in HH:MM format.',
            'pickup_contact_name.required' => 'Pickup contact name is required.',
            'pickup_contact_no.required' => 'Pickup contact number is required.',
            'drop_contact_name.required' => 'Drop contact name is required.',
            'drop_contact_no.required' => 'Drop contact number is required.',
            'parcel_details.required' => 'Parcel details are required.',
            'parcel_images.*.image' => 'Each file must be an image.',
            'parcel_images.*.mimes' => 'Image must be jpeg, png, jpg, or gif.',
            'parcel_images.*.max' => 'Each image may not exceed 2MB.',
            'budget.required' => 'Budget must be requiredr.',
            'budget.numeric' => 'Budget must be a valid number.',
            'budget.min' => 'Budget must be at least 0.',
            'preferred_time.date_format' => 'Preferred time must be in HH:MM format.',
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
            'message'=>'Parcel request created successfully',
            'data'=> $requestModel,
        ], 200);
    }

    // Get all ride requests (type = 0) with user info merged 
    public function getAllRideRequests(Request $request)
    {
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
            'pickup_location.required' => 'Pickup location is required.',
            'destination.required'     => 'Destination is required.',
            'ride_date.required'       => 'Ride date is required.',
            'number_of_seats.integer'  => 'Number of seats must be a valid number.',
            'number_of_seats.min'      => 'Number of seats must be at least 1.',
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
                'message' => 'Invalid ride_date format. Use DD-MM-YYYY.',
            ], 422);
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
            'message' => 'Ride requests retrieved successfully.',
            'data'    => $ridesData,
        ],200);
    }


    // Get all parcel requests (type = 1) with user info merged
    public function getAllParcelRequests(Request $request)
    {
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
            'pickup_location.required' => 'Pickup location is required.',
            'destination.required'     => 'Destination is required.',
            'ride_date.required'       => 'Ride date is required.',
            'number_of_seats.integer'  => 'Number of seats must be a valid number.',
            'number_of_seats.min'      => 'Number of seats must be at least 1.',
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
                'message' => 'Invalid ride_date format. Use DD-MM-YYYY.',
            ], 422);
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
            'message' => 'Parcel requests retrieved successfully.',
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
                'message' => 'User not authenticated'
            ], 401);
        }

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
            'message' => 'Passenger requests retrieved successfully',
            'data' => $requests
        ], 200);
    }

  
    // Driver accepts a passenger request
    // public function updateRequestInterestStatus(Request $request)
    // {
    //     $driver = Auth::guard('api')->user();

    //           if (!$driver) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'User not authenticated'
    //         ], 401);
    //     }

    //     $validator = Validator::make($request->all(), [
    //         'request_id' => 'required|exists:passenger_requests,id',
    //     ], [
    //         'request_id.required' => 'The request ID is required.',
    //         'request_id.exists'   => 'This request does not exist.',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => $validator->errors()->first()
    //         ], 201);
    //     }

    //     $requestModel = PassengerRequest::find($request->request_id);
    //     if($requestModel->status != 'pending'){
    //         return response()->json(['status'=>false,'message'=>'Request already interested or confirmed'],400);
    //     }

    //     $requestModel->driver_id = $driver->id;
    //     $requestModel->status = 'interested';
    //     $requestModel->save();

    //     // Merge user and vehicle details at the top level
    //     $responseData = [
    //         'id'              => $requestModel->id,
    //         'pickup_location' => $requestModel->pickup_location,
    //         'destination'     => $requestModel->destination,
    //         'ride_date'       => $requestModel->ride_date,
    //         'number_of_seats' => $requestModel->number_of_seats,
    //         'services'        => $requestModel->services_details,
    //         'budget'          => $requestModel->budget,
    //         'preferred_time'  => $requestModel->preferred_time,
    //         'user_id'         => $requestModel->user_id,
    //         'type'            => $requestModel->type,
    //         'status'          => $requestModel->status,
    //         'driver_id'       => $requestModel->driver_id,
    //         'created_at'      => $requestModel->created_at,
    //         'updated_at'      => $requestModel->updated_at,
    //     ];

    //     if ($requestModel->user) {
    //         $userData = $requestModel->user->only([
    //             'id', 'name', 'phone_number', 'image', 'is_phone_verify', 'email',
    //             'role', 'dob', 'gender', 'government_id', 'id_verified',
    //         ]);

    //         // Merge vehicle details if exists
    //         if ($requestModel->user->vehicle) {
    //             $vehicleData = $requestModel->user->vehicle->only([
    //                 'id', 'user_id', 'brand', 'model', 'number_plate', 'vehicle_image', 'vehicle_type'
    //             ]);
    //             $userData['vehicle'] = $vehicleData;
    //         }

    //         $responseData = array_merge($responseData, $userData);
    //     }

    //     return response()->json(['status'=>true,'message'=>'Driver is  interested in your request successfully','data'=>$responseData]);
    // }

    // public function updateRequestConfirmStatus(Request $request)
    // {
    //     $passenger = Auth::guard('api')->user();
    //     if (!$passenger) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'User not authenticated'
    //         ], 401);
    //     }

    //     $validator = Validator::make($request->all(), [
    //         'request_id' => 'required|exists:passenger_requests,id',
    //     ], [
    //         'request_id.required' => 'The request ID is required.',
    //         'request_id.exists'   => 'This request does not exist.',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => $validator->errors()->first()
    //         ], 201);
    //     }

    //     $requestModel = PassengerRequest::where('id', $request->request_id)
    //         ->where('user_id', $passenger->id)
    //         ->where('status', 'interested')
    //         ->first();

    //     if(!$requestModel){
    //         return response()->json(['status'=>false,'message'=>'Request not interested yet or already confirmed'],400);
    //     }

    //     DB::transaction(function() use ($requestModel, $passenger) {
    //         // Update request status
    //         $requestModel->status = 'confirmed';
    //         $requestModel->save();

    //         // Create booking entry (driver is user_id here)
    //         RideBooking::create([
    //             // 'ride_id'       => $requestModel->id, // passenger_request id
    //             'type'          => $requestModel->type,
    //             'user_id'       => $requestModel->driver_id, // driver ID
    //             'seats_booked'  => $requestModel->number_of_seats,
    //             'price'         => $requestModel->budget,
    //             'services'      => json_encode($requestModel->services ?? []),
    //             'status'        => 'confirmed',
    //             'active_status' => 1,
    //         ]);
    //     });

    //     // Prepare response
    //     $responseData = [
    //         'id'              => $requestModel->id,
    //         'pickup_location' => $requestModel->pickup_location,
    //         'destination'     => $requestModel->destination,
    //         'ride_date'       => $requestModel->ride_date,
    //         'number_of_seats' => $requestModel->number_of_seats,
    //         'services'        => $requestModel->services_details,
    //         'budget'          => $requestModel->budget,
    //         'preferred_time'  => $requestModel->preferred_time,
    //         'user_id'         => $requestModel->user_id,
    //         'type'            => $requestModel->type,
    //         'status'          => $requestModel->status,
    //         'driver_id'       => $requestModel->driver_id,
    //         'created_at'      => $requestModel->created_at,
    //         'updated_at'      => $requestModel->updated_at,
    //     ];

    //     if ($requestModel->user) {
    //         $userData = $requestModel->user->only([
    //             'id', 'name', 'phone_number', 'image', 'is_phone_verify', 'email',
    //             'role', 'dob', 'gender', 'government_id', 'id_verified',
    //         ]);

    //         if ($requestModel->user->vehicle) {
    //             $vehicleData = $requestModel->user->vehicle->only([
    //                 'id', 'user_id', 'brand', 'model', 'number_plate', 'vehicle_image', 'vehicle_type'
    //             ]);
    //             $userData['vehicle'] = $vehicleData;
    //         }

    //         $responseData = array_merge($responseData, $userData);
    //     }

    //     return response()->json([
    //         'status'=>true,
    //         'message'=>'Request confirmed successfully',
    //         'data'=>$responseData
    //     ]);
    // }


    public function updateRequestInterestStatus(Request $request)
    {
        $driver = Auth::guard('api')->user();
        if (!$driver) {
            return response()->json(['status'=>false,'message'=>'User not authenticated'],401);
        }
        

        $validator = Validator::make($request->all(), [
            'request_id' => 'required|exists:passenger_requests,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status'=>false,'message'=>$validator->errors()->first()],201);
        }

        $requestModel = PassengerRequest::find($request->request_id);

        $alreadyInterested = $requestModel->interests()->where('driver_id', $driver->id)->exists();
        if ($alreadyInterested) {
            return response()->json(['status'=>false,'message'=>'You already expressed interest'],201);
        }

        $requestModel->interests()->create([
            'driver_id' => $driver->id,
        ]);

        // ----------------------
        // ✅ Send notification to passenger
        // ----------------------
        $passenger = $requestModel->user; // passenger who made the request
      $driverName = $driver->name ? $driver->name : 'Driver';


        if ($passenger && $passenger->device_token) {
        
            $fcmService = new \App\Services\FCMService();

            $notificationData = [
                'notification_type' => 3, // new interest notification
                'title' => "Driver interested",
                'body'  => "{$driverName} has expressed interest in your ride request from {$requestModel->pickup_location} to {$requestModel->destination}.Please Confirm",
            ];

            $fcmService->sendNotification([
                [
                    'device_token' => $passenger->device_token,
                    'device_type'  => $passenger->device_type ?? 'android',
                    'user_id'      => $passenger->id,
                ]
            ], $notificationData);
        }

        return response()->json(['status'=>true,'message'=>'Driver expressed interest successfully'],200);
    }


    public function getInterestedDrivers($request_id)
    {
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

    // upadted  resposne and alos all reuet of user
    // public function getInterestedDrivers()
    // {
    //     $passenger = Auth::guard('api')->user();
    //     if (!$passenger) {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => 'You must be logged in.'
    //         ], 401);
    //     }

    //     // Fetch all ride requests by this passenger with interests + drivers + vehicles
    //     $requests = PassengerRequest::with(['interests.driver.vehicle'])
    //         ->where('user_id', $passenger->id)
    //         ->get();

    //     if ($requests->isEmpty()) {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => 'No ride requests found for this passenger.',
    //             'data'    => []
    //         ], 200);
    //     }

    //     // Build response
    //     $data = $requests->flatMap(function ($requestModel) {
    //         $rideDetails = [
    //             'ride_id'         => $requestModel->id,
    //             'pickup_location' => $requestModel->pickup_location,
    //             'destination'     => $requestModel->destination,
    //             'ride_date'       => $requestModel->ride_date,
    //             'ride_time'       => $requestModel->ride_time,
    //             'number_of_seats' => $requestModel->number_of_seats,
    //             'services'        => $requestModel->services_details,
    //             'budget'          => $requestModel->budget,
    //             'preferred_time'  => $requestModel->preferred_time,
    //             'user_id'         => $requestModel->user_id,
    //             'status'          => $requestModel->status,
    //         ];

    //         return $requestModel->interests->map(function ($interest) use ($rideDetails) {
    //             $driver = $interest->driver;
    //             if (!$driver) return null;

    //             $driverData = [
    //                 'driver_id'      => $driver->id, // ✅ explicitly add driver_id
    //                 'name'           => $driver->name,
    //                 'phone_number'   => $driver->phone_number,
    //                 'email'          => $driver->email,
    //                 'image'          => $driver->image,
    //                 'dob'          => $driver->dob,
    //                 'gender'          => $driver->gender,
    //                 'is_phone_verify'          => $driver->is_phone_verify,
    //                 'device_type'    => $driver->device_type,
    //                 'device_id'      => $driver->device_id,
    //             ];

    //             $vehicleData = $driver->vehicle ? [
    //                 'vehicle_number' => $driver->vehicle->vehicle_number,
    //                 'vehicle_type'   => $driver->vehicle->vehicle_type,
    //             ] : [];

    //             return array_merge(
    //                 $rideDetails,
    //                 [
    //                     'interest_id' => $interest->id,          
    //                     'request_id'  => $interest->passenger_request_id,  
    //                 ],
    //                 $driverData,
    //                 // $vehicleData
    //             );
    //         })->filter();
    //     })->values();


    //     return response()->json([
    //         'status'  => true,
    //         'message' => 'Interested drivers with ride details fetched successfully.',
    //         'data'    => $data
    //     ], 200);
    // }



    public function confirmDriverByPassenger(Request $request)
    {
        $passenger = Auth::guard('api')->user();
        if (!$passenger) {
            return response()->json([
                'status' => false,
                'message' => 'You must be logged in as a passenger to confirm a driver.'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'request_id' => 'required|exists:passenger_requests,id',
            'driver_id'  => 'required|exists:users,id',
            'status'     => 'required|in:confirmed,declined',
        ], [
            'request_id.required' => 'The request ID is required.',
            'request_id.exists'   => 'The specified ride request does not exist.',
            'driver_id.required'  => 'You must select a driver to confirm.',
            'driver_id.exists'    => 'The selected driver does not exist.',
            'status.required'     => 'Status is required.',
            'status.in'           => 'Status must be either confirmed or declined.',
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
                'message' => 'Ride request not found or does not belong to you.'
            ], 201);
        }

         // ✅ Prevent confirming again if already confirmed
        if ($requestModel->status === 'confirmed') {
            return response()->json([
                'status' => false,
                'message' => 'A driver has already been confirmed for this request.'
            ], 201);
        }

        $driverInterest = $requestModel->interests()
            ->where('driver_id', $request->driver_id)
            ->first();

        if (!$driverInterest) {
            return response()->json([
                'status' => false,
                'message' => 'The driver you selected did not express interest in this ride.'
            ], 201);
        }

        $driver = \App\Models\User::find($request->driver_id);
       $passengerName = $passenger->name ? $passenger->name : "Passenger";
        if ($request->status === 'declined') {
            // Delete the interest if declined
            $driverInterest->delete();

             // Send notification to driver
            if ($driver && $driver->device_token) {
                $fcmService = new \App\Services\FCMService();
                $fcmService->sendNotification([
                    [
                        'device_token' => $driver->device_token,
                        'device_type'  => $driver->device_type ?? 'android',
                        'user_id'      => $driver->id,
                    ]
                ], [
                    'notification_type' => 5, // passenger declined
                    'title' => "Ride Request Declined",
                    'body'  => "{$passengerName} has declined your interest for the ride from {$requestModel->pickup_location} to {$requestModel->destination}.",
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Driver interest has been declined and removed successfully.'
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
            $fcmService = new \App\Services\FCMService();
            $fcmService->sendNotification([
                [
                    'device_token' => $driver->device_token,
                    'device_type'  => $driver->device_type ?? 'android',
                    'user_id'      => $driver->id,
                ]
            ], [
                'notification_type' => 4, // passenger confirmed
                'title' => "Ride Request Confirmed",
                'body'  => "{$passengerName} has confirmed your interest for ride from {$requestModel->pickup_location} to {$requestModel->destination}.",
            ]);
        }
        return response()->json([
            'status' => true,
            'message' => 'Driver confirmed successfully and booking has been created.'
        ],200);
    }


    










}
