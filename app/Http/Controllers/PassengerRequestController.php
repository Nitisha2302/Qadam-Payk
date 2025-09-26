<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PassengerRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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
        ]);

        if ($validator->fails()) {
            return response()->json(['status'=>false,'message'=>$validator->errors()->first()],201);
        }

        $data = $request->only(['pickup_location','destination','ride_date','number_of_seats','services']);
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
        ]);

        if ($validator->fails()) {
            return response()->json(['status'=>false,'message'=>$validator->errors()->first()],201);
        }

        $data = $request->only([
            'pickup_location','destination','ride_date','ride_time',
            'pickup_contact_name','pickup_contact_no','drop_contact_name','drop_contact_no','parcel_details'
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


    // // Get all ride requests (type = 0) with user info merged
    // public function getAllRideRequests()
    // {
    //     $rides = PassengerRequest::with('user')
    //         ->where('type', 0)
    //         ->where('status', 'pending')
    //         ->orderBy('ride_date', 'asc')
    //         ->get();

    //     $rides = $rides->map(function ($ride) {
    //         if ($ride->user) {
    //             $userData = $ride->user->only([
    //                 'id','image','name','phone_number','is_phone_verify','email','role','dob','gender',
    //                 'government_id','id_verified','apple_token','facebook_token','google_token','is_social',
    //                 'device_type','device_id','device_token','api_token','vehicle_number','vehicle_type',
    //                 'created_at','updated_at'
    //             ]);
    //             $ride = $ride->toArray();
    //             unset($ride['user']); // remove nested user
    //             return array_merge($ride, $userData);
    //         }
    //         return $ride;
    //     });

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'All ride requests retrieved successfully',
    //         'data' => $rides
    //     ]);
    // }

    // // Get all parcel requests (type = 1) with user info merged
    // public function getAllParcelRequests()
    // {
    //     $parcels = PassengerRequest::with('user')
    //         ->where('type', 1)
    //         ->where('status', 'pending')
    //         ->orderBy('ride_date', 'asc')
    //         ->get();

    //     $parcels = $parcels->map(function ($parcel) {
    //         if ($parcel->user) {
    //             $userData = $parcel->user->only([
    //                 'id','image','name','phone_number','is_phone_verify','email','role','dob','gender',
    //                 'government_id','id_verified','apple_token','facebook_token','google_token','is_social',
    //                 'device_type','device_id','device_token','api_token','vehicle_number','vehicle_type',
    //                 'created_at','updated_at'
    //             ]);
    //             $parcel = $parcel->toArray();
    //             unset($parcel['user']); // remove nested user
    //             return array_merge($parcel, $userData);
    //         }
    //         return $parcel;
    //     });

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'All parcel requests retrieved successfully',
    //         'data' => $parcels
    //     ]);
    // }



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

        // ✅ Merge user info
        $ridesData = $rides->map(function ($ride) {
            if ($ride->user) {
                $userData = $ride->user->only([
                    'id','image','name','phone_number','is_phone_verify','email','role','dob','gender',
                    'government_id','id_verified','apple_token','facebook_token','google_token','is_social',
                    'device_type','device_id','device_token','api_token','vehicle_number','vehicle_type',
                    'created_at','updated_at'
                ]);
                $rideArr = $ride->toArray();
                unset($rideArr['user']);
                return array_merge($rideArr, $userData);
            }
            return $ride;
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

        // ✅ Merge user info
        $parcelsData = $parcels->map(function ($parcel) {
            if ($parcel->user) {
                $userData = $parcel->user->only([
                    'id','image','name','phone_number','is_phone_verify','email','role','dob','gender',
                    'government_id','id_verified','apple_token','facebook_token','google_token','is_social',
                    'device_type','device_id','device_token','api_token','vehicle_number','vehicle_type',
                    'created_at','updated_at'
                ]);
                $parcelArr = $parcel->toArray();
                unset($parcelArr['user']);
                return array_merge($parcelArr, $userData);
            }
            return $parcel;
        });

        return response()->json([
            'status'  => true,
            'message' => 'Parcel requests retrieved successfully.',
            'data'    => $parcelsData,
        ],200);
    }


  
    // Driver accepts a passenger request
    public function acceptRequest(Request $request)
    {
        $driver = Auth::guard('api')->user();
        $validator = Validator::make($request->all(), [
            'request_id' => 'required|exists:passenger_requests,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status'=>false,'message'=>$validator->errors()->first()],201);
        }

        $requestModel = PassengerRequest::find($request->request_id);
        if($requestModel->status != 'pending'){
            return response()->json(['status'=>false,'message'=>'Request already accepted or confirmed'],400);
        }

        $requestModel->driver_id = $driver->id;
        $requestModel->status = 'accepted';
        $requestModel->save();

        return response()->json(['status'=>true,'message'=>'Request accepted successfully','data'=>$requestModel]);
    }

    // Passenger confirms a request after driver accepts
    public function confirmRequest(Request $request)
    {
        $passenger = Auth::guard('api')->user();
        $validator = Validator::make($request->all(), [
            'request_id' => 'required|exists:passenger_requests,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status'=>false,'message'=>$validator->errors()->first()],201);
        }

        $requestModel = PassengerRequest::where('id', $request->request_id)
            ->where('user_id', $passenger->id)
            ->where('status', 'accepted')
            ->first();

        if(!$requestModel){
            return response()->json(['status'=>false,'message'=>'Request not accepted yet or already confirmed'],400);
        }

        $requestModel->status = 'confirmed';
        $requestModel->save();

        return response()->json(['status'=>true,'message'=>'Request confirmed successfully','data'=>$requestModel]);
    }




}
