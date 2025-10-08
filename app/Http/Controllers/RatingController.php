<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rating;
use App\Models\Ride;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RatingController extends Controller
{
    // Store a rating/review
    public function store(Request $request)
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        // Custom validation messages
        $validator = Validator::make($request->all(), [
            'ride_id' => 'required|exists:rides,id',
            'reviewed_id' => 'required|exists:users,id',
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:500',
        ], [
            'ride_id.required' => 'Please provide a valid ride.',
            'ride_id.exists' => 'The selected ride does not exist.',
            'reviewed_id.required' => 'Please select a user to review.',
            'reviewed_id.exists' => 'The selected user does not exist.',
            'rating.required' => 'Rating is required.',
            'rating.integer' => 'Rating must be a number.',
            'rating.min' => 'Rating must be at least 1 star.',
            'rating.max' => 'Rating cannot exceed 5 stars.',
            'review.string' => 'Review must be text.',
            'review.max' => 'Review cannot exceed 500 characters.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        // Prevent duplicate rating for same ride/user
        $existing = Rating::where('ride_id', $request->ride_id)
            ->where('reviewer_id', $user->id)
            ->where('reviewed_id', $request->reviewed_id)
            ->first();

        if ($existing) {
            return response()->json([
                'status' => false,
                'message' => 'You have already rated this ride/user.'
            ], 409);
        }

        $rating = Rating::create([
            'ride_id' => $request->ride_id,
            'reviewer_id' => $user->id,
            'reviewed_id' => $request->reviewed_id,
            'rating' => $request->rating,
            'review' => $request->review ?? null
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Rating submitted successfully.',
            'data' => $rating
        ], 201);
    }

    // List ratings received by the authenticated user
    // public function list(Request $request)
    // {
    //     $user = Auth::guard('api')->user();
    //     if (!$user) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'User not authenticated'
    //         ], 401);
    //     }

    //     $ratings = Rating::with('reviewer','ride')
    //         ->where('reviewed_id', $user->id)
    //         ->orderByDesc('created_at')
    //         ->get()
    //         ->map(function($r) {
    //             return [
    //                 'ride_id' => $r->ride_id,
    //                 'reviewer_id' => $r->reviewer_id,
    //                 'reviewer_name' => $r->reviewer->name ?? 'N/A',
    //                 'rating' => $r->rating,
    //                 'review' => $r->review,
    //                 'ride_date' => $r->ride->ride_date ?? null,
    //                 'ride_time' => $r->ride->ride_time ?? null,
    //                 'created_at' => $r->created_at->format('Y-m-d H:i:s'),
    //             ];
    //         });

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Ratings fetched successfully.',
    //         'data' => $ratings
    //     ], 200);
    // }

    // List ratings received by the authenticated user
    public function list(Request $request)
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        // Fetch all ratings for this user
        $ratings = Rating::with('reviewer','ride')
            ->where('reviewed_id', $user->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(function($r) {
                return [
                    'ride_id' => $r->ride_id,
                    'reviewer_id' => $r->reviewer_id,
                    'reviewer_name' => $r->reviewer->name ?? 'N/A',
                    'rating' => $r->rating,
                    'review' => $r->review,
                    'ride_date' => $r->ride->ride_date ?? null,
                    'ride_time' => $r->ride->ride_time ?? null,
                    'created_at' => $r->created_at->format('Y-m-d H:i:s'),
                ];
            });

        // Calculate average rating
        $averageRating = Rating::where('reviewed_id', $user->id)->avg('rating');
        $averageRating = round($averageRating, 2); // round to 2 decimals

        return response()->json([
            'status' => true,
            'message' => 'Ratings fetched successfully.',
            'average_rating' => $averageRating,
            'data' => $ratings
        ], 200);
    }

}
