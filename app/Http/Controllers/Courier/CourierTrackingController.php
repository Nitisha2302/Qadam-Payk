<?php

namespace App\Http\Controllers\Courier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\CourierRequest;
use App\Models\CourierLiveTracking;

class CourierTrackingController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | DRIVER API
    | Driver sends live location
    |--------------------------------------------------------------------------
    */

    public function updateLocation(Request $request)
    {
        $driver = Auth::guard('api')->user();

        if (!$driver) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'courier_request_id' => 'required|exists:courier_requests,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $courier = CourierRequest::where('id', $request->courier_request_id)
            ->where('accepted_driver_id', $driver->id)
            ->first();

        if (!$courier) {
            return response()->json([
                'status' => false,
                'message' => 'Courier not assigned to you'
            ]);
        }

        // Update or Create latest tracking
        CourierLiveTracking::updateOrCreate(
            [
                'courier_request_id' => $courier->id,
                'driver_id' => $driver->id
            ],
            [
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'tracked_at' => now()
            ]
        );

        return response()->json([
            'status' => true,
            'message' => 'Location updated successfully'
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | USER API
    | User fetch driver live location
    |--------------------------------------------------------------------------
    */

    public function getLiveLocation($courier_id)
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $courier = CourierRequest::with('liveTracking')
            ->where('id', $courier_id)
            ->where('user_id', $user->id)
            ->first();

        if (!$courier) {
            return response()->json([
                'status' => false,
                'message' => 'Courier not found'
            ]);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'pickup_latitude' => $courier->pickup_latitude,
                'pickup_longitude' => $courier->pickup_longitude,

                'drop_latitude' => $courier->drop_latitude,
                'drop_longitude' => $courier->drop_longitude,

                'driver_latitude' => optional($courier->liveTracking)->latitude,
                'driver_longitude' => optional($courier->liveTracking)->longitude,
                'last_updated' => optional($courier->liveTracking)->tracked_at,
            ]
        ]);
    }
}