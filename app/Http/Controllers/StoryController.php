<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Story;
use App\Models\StoryReport;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Models\UserLang;
use Illuminate\Support\Facades\App;
use App\Models\StoryView;
use App\Models\UserBlock;

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
                'message' => __('messages.story.user_not_authenticated')
            ], 401);
        }

        $userLang = UserLang::where('user_id', $user->id)
            ->where('device_id', $user->device_id)
            ->where('device_type', $user->device_type)
            ->first();

        $lang = $userLang->language ?? 'ru'; // fallback
        App::setLocale($lang);

        // 🔹 Validation with custom messages
        $validator = Validator::make($request->all(), [
            'media' => 'required|file|mimes:jpg,jpeg,png,mp4,mov',
            'type' => 'required|in:photo,video',
            'route' => 'nullable|string',
            'city' => 'nullable|string',
            'description' => 'nullable|string|max:500',
            'category' => 'nullable'
        ], [
            'media.required' => __('messages.story.validation.media_required'),
            'media.file' => __('messages.story.validation.media_file'),
            'media.mimes' => __('messages.story.validation.media_mimes'),
            'type.required' => __('messages.story.validation.type_required'),
            'type.in' => __('messages.story.validation.type_invalid'),
            'route.string' => __('messages.story.validation.route_string'),
            'city.string' => __('messages.story.validation.city_string'),
            'description.string' => __('messages.story.validation.description_string'),
            'description.max' => __('messages.story.validation.description_max'),
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
               'message' => __('api.story.no_media'),
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
          'message' => __('messages.story.upload_success'),
            'story' => $story
        ], 200);
    }

    // public function myStories(Request $request)
    // {
    //     $user = Auth::guard('api')->user();
    //     if (!$user) {
    //         return response()->json([
    //             'status' => false,
    //            'message' => __('messages.story.user_not_authenticated')
    //         ], 401);
    //     }

    //      $userLang = UserLang::where('user_id', $user->id)
    //         ->where('device_id', $user->device_id)
    //         ->where('device_type', $user->device_type)
    //         ->first();

    //     $lang = $userLang->language ?? 'ru'; // fallback
    //     App::setLocale($lang);

    //     $now = Carbon::now();
    //     $stories = Story::where('user_id', $user->id)
    //                     ->where('expires_at', '>', $now)
    //                     ->orderBy('created_at', 'desc')
    //                     ->get();

    //     return response()->json([
    //         'status' => true,
    //         'message' => __('messages.story.fetch_success'),
    //         'stories' => $stories
    //     ], 200);
    // }

    // with view user data 

    public function myStories(Request $request)
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => __('messages.story.user_not_authenticated')
            ], 401);
        }

        // 🌐 Language
        $userLang = UserLang::where('user_id', $user->id)
            ->where('device_id', $user->device_id)
            ->where('device_type', $user->device_type)
            ->first();

        $lang = $userLang->language ?? 'ru';
        App::setLocale($lang);

        $now = Carbon::now();

        $stories = Story::where('user_id', $user->id)
            ->where('expires_at', '>', $now)
            ->with([
                'viewers' => function ($q) {
                    $q->select('users.id', 'users.name', 'users.image');
                }
            ])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($story) {
                return [
                    'id' => $story->id,
                    'media' => $story->media,
                    'type' => $story->type,
                    'route' => $story->route,
                    'city' => $story->city,
                    'description' => $story->description,
                    'created_at' => $story->created_at,

                    // 👁 View count
                    'views_count' => $story->viewers->count(),

                    // 👤 Viewers list
                    'viewers' => $story->viewers->map(function ($user) {
                        return [
                            'id' => $user->id,
                            'name' => $user->name,
                            'image' => $user->image
                                ?  $user->image
                                : null,
                        ];
                    }),
                ];
            });

        return response()->json([
            'status' => true,
            'message' => __('messages.story.fetch_success'),
            'stories' => $stories
        ], 200);
    }



    // Fetch active stories (last 24 hours)
    // public function othersStories(Request $request)
    // {
    //     $user = Auth::guard('api')->user();
    //     if (!$user) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => __('messages.story.user_not_authenticated')

    //         ], 401);
    //     }

    //      $userLang = UserLang::where('user_id', $user->id)
    //         ->where('device_id', $user->device_id)
    //         ->where('device_type', $user->device_type)
    //         ->first();

    //     $lang = $userLang->language ?? 'ru'; // fallback
    //     App::setLocale($lang);

    //     $now = Carbon::now();

    //     $stories = Story::where('user_id', '!=', $user->id)
    //         ->where('expires_at', '>', $now)

    //         ->when($request->filled('route'), function ($q) use ($request) {

    //             // Normalize route
    //             $route = strtolower(trim($request->route)); // chd-mohali
    //             $parts = explode('-', $route);

    //             if (count($parts) === 2) {
    //                 $route1 = $parts[0] . '-' . $parts[1]; // chd-mohali
    //                 $route2 = $parts[1] . '-' . $parts[0]; // mohali-chd

    //                 $q->whereIn('route', [$route1, $route2]);
    //             } else {
    //                 $q->where('route', $route);
    //             }
    //         })

    //         ->when($request->filled('city'), function ($q) use ($request) {
    //             $q->where('city', $request->city);
    //         })

    //         ->orderBy('created_at', 'desc')
    //         ->get();

    //     return response()->json([
    //         'status'  => true,
    //       'message' => __('messages.story.fetch_success'),
    //         'stories' => $stories
    //     ], 200);
    //  }


    // final with block 

    public function othersStories(Request $request)
        {
            $user = Auth::guard('api')->user();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => __('messages.story.user_not_authenticated')

                ], 401);
            }

            $userLang = UserLang::where('user_id', $user->id)
                ->where('device_id', $user->device_id)
                ->where('device_type', $user->device_type)
                ->first();

            $lang = $userLang->language ?? 'ru'; // fallback
            App::setLocale($lang);

            $now = Carbon::now();

            /*
            |--------------------------------------------------------------------------
            | 🚫 BLOCK LOGIC
            |--------------------------------------------------------------------------
            */

            // Users I blocked
            $blockedByMe = UserBlock::where('user_id', $user->id)
                ->pluck('blocked_user_id')
                ->toArray();

            // Users who blocked me
            $blockedMe = UserBlock::where('blocked_user_id', $user->id)
                ->pluck('user_id')
                ->toArray();

            // Merge both
            $blockedUserIds = array_unique(array_merge($blockedByMe, $blockedMe));

            $stories = Story::where('user_id', '!=', $user->id)
             ->whereNotIn('user_id', $blockedUserIds)
                ->where('expires_at', '>', $now)

                
                ->when($request->filled('route'), function ($q) use ($request) {

                    // Normalize route
                    $route = strtolower(trim($request->route)); // chd-mohali
                    $parts = explode('-', $route);

                    if (count($parts) === 2) {
                        $route1 = $parts[0] . '-' . $parts[1]; // chd-mohali
                        $route2 = $parts[1] . '-' . $parts[0]; // mohali-chd

                        $q->whereIn('route', [$route1, $route2]);
                    } else {
                        $q->where('route', $route);
                    }
                })

                ->when($request->filled('city'), function ($q) use ($request) {
                    $q->where('city', $request->city);
                })

                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'status'  => true,
            'message' => __('messages.story.fetch_success'),
                'stories' => $stories
            ], 200);
    }





    // Report a story
    public function report(Request $request, $id)
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => __('messages.story.user_not_authenticated')
            ], 401);
        }

       $request->validate([
         'reason' => 'nullable|string|max:255',
        ], [
            'reason.string' => __('messages.story.validation.reason_string'),
            'reason.max' => __('messages.story.validation.reason_max'),
        ]);


        $story = Story::find($id);
        if (!$story) {
            return response()->json([
                'status' => false,
               'message' => __('messages.story.story_not_found'),
            ], 201);
        }

        // Prevent duplicate report
        $alreadyReported = StoryReport::where('story_id', $id)
            ->where('user_id', $user->id)
            ->exists();

        if ($alreadyReported) {
            return response()->json([
                'status' => false,
               'message' => __('messages.story.already_reported'),

            ], 201);
        }

        StoryReport::create([
            'story_id' => $id,
            'user_id'  => $user->id,
            'reason'   => $request->reason,
        ]);

        return response()->json([
            'status' => true,
            'message' => __('messages.story.report_success'),
        ], 200);
    }


    public function destroy($id)
    {
        // 🔹 Auth check
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => __('messages.story.user_not_authenticated'),
            ], 401);
        }

        // 🔹 Set language (same as rating)
        $userLang = UserLang::where('user_id', $user->id)
            ->where('device_id', $user->device_id)
            ->where('device_type', $user->device_type)
            ->first();

        $lang = $userLang->language ?? 'ru';
        App::setLocale($lang);

        // 🔹 Find story
        $story = Story::find($id);
        if (!$story) {
            return response()->json([
                'status' => false,
                'message' => __('messages.story.story_not_found'),
            ], 201);
        }

        // 🔹 Ownership check
        if ($story->user_id !== $user->id) {
            return response()->json([
                'status' => false,
                'message' => __('messages.story.delete_not_allowed'),
            ], 201);
        }

        // 🔹 Delete story
        $story->delete();

        return response()->json([
            'status' => true,
            'message' => __('messages.story.delete_success'),
        ], 200);
    }


    public function viewStory(Request $request, $id)
    {
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => __('messages.story.user_not_authenticated'),
            ], 401);
        }
         // 🔹 Set language (same as rating)
        $userLang = UserLang::where('user_id', $user->id)
            ->where('device_id', $user->device_id)
            ->where('device_type', $user->device_type)
            ->first();

        $lang = $userLang->language ?? 'ru';
        App::setLocale($lang);

        $story = Story::find($id);
        if (!$story) {
            return response()->json([
                'status' => false,
                'message' => __('messages.story.story_not_found'),
            ], 201);
        }

        // ❌ Prevent owner from viewing own story
        if ($story->user_id === $user->id) {
            return response()->json([
                'status' => false,
                'message' => __('messages.story.cannot_view_own_story'),
            ], 201);
        }

        // 🔒 Prevent duplicate view
        $alreadyViewed = StoryView::where('story_id', $id)
            ->where('user_id', $user->id)
            ->exists();

        if ($alreadyViewed) {
            return response()->json([
                'status' => true,
                'message' => __('messages.story.already_viewed'),
            ], 200);
        }

        // ✅ Store view
        StoryView::create([
            'story_id' => $id,
            'user_id' => $user->id,
        ]);

        return response()->json([
            'status' => true,
            'message' => __('messages.story.view_recorded'),
        ], 200);
    }




}
