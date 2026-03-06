<?php

namespace App\Http\Controllers\Courier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CourierModeController extends Controller
{
    // Single API for Online / Offline
    public function updateMode(Request $request)
    {
        $user = Auth::guard('api')->user();

        // Unauthorized
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized.'
            ], 401);
        }

        // Validate request
        $validator = Validator::make($request->all(), [
            'is_online' => 'required|in:0,1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 201);
        }

        /*
        |--------------------------------------------------------------------------
        | If driver trying to go OFFLINE
        |--------------------------------------------------------------------------
        */
        if ($request->is_online == 0) {

            $activeTrip = CourierRequest::where('accepted_driver_id', $user->id)
                ->where('status', 'in_transit')
                ->exists();

            if ($activeTrip) {
                return response()->json([
                    'status' => false,
                    'message' => 'You cannot go offline while a delivery is in transit.'
                ], 201);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | If driver trying to go ONLINE
        |--------------------------------------------------------------------------
        */
        if ($request->is_online == 1) {

            // Optional ID verification
            // if ($user->id_verified != 1) {
            //     return response()->json([
            //         'status' => false,
            //         'message' => 'Your ID is not verified. You cannot go online.'
            //     ], 201);
            // }

            // Optional courier docs check
            // if ($user->courier_doc_status != 'approved') {
            //     return response()->json([
            //         'status' => false,
            //         'message' => 'Courier documents are not approved yet.'
            //     ], 201);
            // }
        }

        // Update status
        $user->is_online = $request->is_online;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => $request->is_online == 1 
                ? 'You are now online.' 
                : 'You are now offline.',
            'data' => [
                'is_online' => $user->is_online
            ]
        ]);
    }
}
// class CourierModeController extends Controller
// {
//     // ✅ Single API for Online / Offline
//     public function updateMode(Request $request)
//     {
//         $user = Auth::guard('api')->user();

//         // Unauthorized check
//         if (!$user) {
//             return response()->json([
//                 'status' => false,
//                 'message' => 'Unauthorized.'
//             ], 401);
//         }

//         // Validate request
//         $validator = Validator::make($request->all(), [
//             'is_online' => 'required|in:0,1'
//         ]);

//         if ($validator->fails()) {
//             return response()->json([
//                 'status' => false,
//                 'message' => $validator->errors()->first()
//             ], 201);
//         }

//         // If user wants to go online, check ID verified
//         if ($request->is_online == 1) {

//             // if ($user->id_verified != 1) {
//             //     return response()->json([
//             //         'status' => false,
//             //         'message' => 'Your ID is not verified. You cannot go online.'
//             //     ], 201);
//             // }

//             // optional: check courier docs approved
//             // if ($user->courier_doc_status != 'approved') {
//             //     return response()->json([
//             //         'status' => false,
//             //         'message' => 'Courier documents are not approved yet.'
//             //     ], 201);
//             // }
//         }

//         // Update online status
//         $user->is_online = $request->is_online;
//         $user->save();

//         return response()->json([
//             'status' => true,
//             'message' => $request->is_online == 1 ? 'You are now online.' : 'You are now offline.',
//             'data' => [
//                 'is_online' => $user->is_online
//             ]
//         ]);
//     }
// }
