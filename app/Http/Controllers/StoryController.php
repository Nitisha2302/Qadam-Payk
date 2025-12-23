<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Story;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class StoryController extends Controller
{
    // Upload story
    public function store(Request $request)
    {
        // 🔹 Check authenticated user
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not authenticated',
            ], 401);
        }

        // 🔹 Validation with custom messages
        $validator = Validator::make($request->all(), [
            'media' => 'required|file|mimes:jpg,jpeg,png,mp4,mov',
            'type' => 'required|in:photo,video',
            'route' => 'nullable|string',
            'city' => 'nullable|string',
            'description' => 'nullable|string|max:500',
            'category' => 'nullable'
        ], [
            'media.required' => 'Please upload a photo or video.',
            'media.file' => 'Media must be a valid file.',
            'media.mimes' => 'Allowed file types: jpg, jpeg, png, mp4, mov.',
            'media.max' => 'File size should not exceed 20MB.',
            'type.required' => 'Type is required (photo/video).',
            'type.in' => 'Type must be either photo or video.',
            'description.max' => 'Description can not exceed 500 characters.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 201);
        }

        // 🔹 Save file manually
        if ($request->hasFile('media')) {
            $file = $request->file('media');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('assets/story_media/'), $filename);
            $path =  $filename;
        } else {
            return response()->json([
                'status' => false,
                'message' => 'No media uploaded'
            ], 201);
        }

        $story = Story::create([
            'user_id' => $user->id,
            'type' => $request->type,
            'media' => $path,
            'route' => $request->route,
            'city' => $request->city,
            'description' => $request->description,
            'category' => $request->category,
            'expires_at' => Carbon::now()->addDay(),
        ]);

        return response()->json([
            'status' => true,
            'message' => "story uploaded sucessfully.",
            'story' => $story
        ], 200);
    }

    public function myStories(Request $request)
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not authenticated',
            ], 401);
        }

        $now = Carbon::now();
        $stories = Story::where('user_id', $user->id)
                        ->where('expires_at', '>', $now)
                        ->orderBy('created_at', 'desc')
                        ->get();

        return response()->json([
            'status' => true,
             'message' => "story fetced sucessfully.",
            'stories' => $stories
        ], 200);
    }


    // Fetch active stories (last 24 hours)
    public function othersStories(Request $request)
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not authenticated',
            ], 401);
        }

        $now = Carbon::now();
        $stories = Story::where('user_id', '!=', $user->id) // exclude self
                        ->where('expires_at', '>', $now);

        if ($request->has('route')) {
            $stories->where('route', $request->route);
        }
        if ($request->has('city')) {
            $stories->where('city', $request->city);
        }

        $stories = $stories->orderBy('created_at', 'desc')->get();

        return response()->json([
            'status' => true,
             'message' => "story fetced sucessfully.",
            'stories' => $stories
        ], 200);
    }


    // Report a story
    public function report($id)
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not authenticated',
            ], 401);
        }

        $story = Story::find($id);
        if (!$story) {
            return response()->json([
                'status' => false,
                'message' => 'Story not found'
            ], 201);
        }

        $story->reported = true;
        $story->save();

        return response()->json([
            'status' => true,
             'message' => "Report Submitted sucessfully.",
            'message' => 'Story reported'
        ], 200);
    }



}
