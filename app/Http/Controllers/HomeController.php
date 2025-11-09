<?php

namespace App\Http\Controllers;
use App\Models\City; 
use App\Models\CarModel; 
use App\Models\Service;
use App\Models\Enquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\UserLang;
use App\Models\Report;

class HomeController extends Controller
{
    // public function getCity()
    // {
    //     // Fetch all height_key records
    //     $cities = City::all();

    //     // Return a structured JSON response
    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Cities fetched successfully.',
    //         'data' => $cities
    //     ],200);
    // }


    // with translation 

    // public function getCity(Request $request)
    // {
    //     // Step 1️⃣: Default language = Russian
    //     $lang = 'ru';
    //     $user = null;

    //     // Step 2️⃣: Try to detect user via token (if present)
    //     if ($request->bearerToken()) {
    //         $user = Auth::guard('api')->user();

    //         if ($user) {
    //             // Check if user has preferred language saved
    //             $userLang = UserLang::where('user_id', $user->id)
    //                 ->where('device_id', $user->device_id)
    //                 ->where('device_type', $user->device_type)
    //                 ->first();

    //             $lang = $userLang->language ?? 'ru';
    //         }
    //     } 
    //     // Step 3️⃣: If no token, try request->language
    //     elseif ($request->has('language')) {
    //         $lang = $request->language;
    //     }

    //     // Step 4️⃣: Apply fallback (always safe)
    //     app()->setLocale($lang);

    //     // Step 5️⃣: Fetch cities by language_code (if set in DB)
    //     $cities = City::when($lang, function ($query) use ($lang) {
    //         return $query->where(function ($q) use ($lang) {
    //             $q->where('language_code', $lang)
    //             ->orWhereNull('language_code');
    //         });
    //     })->get();

    //     // Step 6️⃣: Return localized message
    //     return response()->json([
    //         'status'  => true,
    //         'message' => __('messages.city.fetched_successfully'),
    //         'language_used' => $lang,
    //         'data'    => $cities,
    //     ], 200);
    //  }


    public function getCity(Request $request)
    {
        // Step 1️⃣: Default language = Russian
        $lang = 'ru';
        $user = null;

        try {
            // Step 2️⃣: Detect user if token present
            if ($request->bearerToken()) {
                $user = Auth::guard('api')->user();

                if ($user) {
                    $userLang = \App\Models\UserLang::where('user_id', $user->id)
                        ->where('device_id', $user->device_id)
                        ->where('device_type', $user->device_type)
                        ->first();

                    if ($userLang && !empty($userLang->language)) {
                        $lang = $userLang->language;
                    }
                }
            }

            // Step 3️⃣: If language param passed, override user language
            if ($request->has('language') && !empty($request->language)) {
                $lang = $request->language;
            }

            // Step 4️⃣: Apply locale
            app()->setLocale($lang);

            // Step 5️⃣: Fetch cities based on language logic
            $cities = \App\Models\City::where(function ($q) use ($lang) {
                if ($lang === 'ru') {
                    // Russian → include both ru + NULL
                    $q->where('language_code', 'ru')
                    ->orWhereNull('language_code');
                } else {
                    // Other → only exact match
                    $q->where('language_code', $lang);
                }
            })->get();

            // Step 6️⃣: Fallback → if no cities found, show Russian (default)
            if ($cities->isEmpty()) {
                $cities = \App\Models\City::where(function ($q) {
                    $q->where('language_code', 'ru')
                    ->orWhereNull('language_code');
                })->get();

                $lang = 'ru'; // reset language to fallback
            }

            // Step 7️⃣: Return localized response
            return response()->json([
                'status'        => true,
                'message'       => __('messages.city.fetched_successfully'),
                'language_used' => $lang,
                'data'          => $cities,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    }



    public function getAllBrands()
    {
        // Assuming your CarModel table has a 'brand' column
        $brands = CarModel::select('brand')->distinct()->get();

        return response()->json([
            'status' => true,
            'message' => 'Brand fetched successfully.',
            'data' => $brands
        ]);
    }

    public function getModelsByBrand(string $brand)
    {
        $models = CarModel::where('brand', $brand)->pluck('model_name');

        if ($models->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No models found for this brand'
            ], 404);
        }

        return response()->json([
            'status' => true,
             'message' => 'models fetched successfully.',
            'brand' => $brand,
            'data' => $models
        ]);
    }

    public function getColorsByModel(string $model)
    {
        // Assuming your CarModel table has a 'color' column
        $colors = CarModel::where('model_name', $model)->pluck('color')->unique();

        if ($colors->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No colors found for this model'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'colors fetched successfully.',
            'model' => $model,
            'data' => $colors
        ]);
    }

    // public function getAllServices()
    // {
    //     // Get all services with id and service_name
    //     $services = Service::select('id', 'service_name','service_image')->get();

    //     return response()->json([
    //         'status' => true,
    //                     'message' => 'Services fetched successfully.',
    //         'data'   => $services, // array of objects [{id:1, service_name:"WiFi"}, ...]
    //     ], 200);
    // }


    // with language code 
   public function getAllServices(Request $request)
    {
        // Step 1️⃣: Default language = Russian
        $lang = 'ru';
        $user = null;

        try {
            // Step 2️⃣: Detect user if token present
            if ($request->bearerToken()) {
                $user = Auth::guard('api')->user();


                

                if ($user) {
                    $userLang = UserLang::where('user_id', $user->id)
                        ->where('device_id', $user->device_id)
                        ->where('device_type', $user->device_type)
                        ->first();

                    // If user language found, use it
                    if ($userLang && !empty($userLang->language)) {
                        $lang = $userLang->language;
                    }
                }
            }

            // Step 3️⃣: If request has explicit language param, override
            if ($request->has('language') && !empty($request->language)) {
                $lang = $request->language;
            }

            // Step 4️⃣: Apply locale for translations
            app()->setLocale($lang);

            // Step 5️⃣: Fetch services by language logic
            $services = Service::where(function ($q) use ($lang) {
                if ($lang === 'ru') {
                    // Russian → include ru + NULL
                    $q->where('language_code', 'ru')
                    ->orWhereNull('language_code');
                } else {
                    // Other language → only exact match
                    $q->where('language_code', $lang);
                }
            })
            ->select('id', 'service_name', 'service_image')
            ->get();

            // Step 6️⃣: If no services found for that lang, fallback to Russian
            if ($services->isEmpty()) {
                $services = Service::where(function ($q) {
                    $q->where('language_code', 'ru')
                    ->orWhereNull('language_code');
                })
                ->select('id', 'service_name', 'service_image')
                ->get();

                $lang = 'ru'; // fallback language
            }

            // Step 7️⃣: Return response
            return response()->json([
                'status'         => true,
                'message'        => __('messages.service.fetched_successfully'),
                'language_used'  => $lang,
                'data'           => $services,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    }



    public function storeEnquiry(Request $request)
    {
            //  Get authenticated user
            $user = Auth::guard('api')->user();
            if (!$user) {
                return response()->json([
                    'status' => false,
                       'message' => __('messages.enquiry.user_not_authenticated'),
                ], 401);
            }

             // 🔹 Detect user's preferred language from UserLang table
            $userLang = UserLang::where('user_id', $user->id)
                ->where('device_id', $user->device_id)
                ->where('device_type', $user->device_type)
                ->first();

            $lang = $userLang->language ?? 'ru'; // fallback to Russian
            app()->setLocale($lang);

            //  Validation
           $validator = Validator::make($request->all(), [
                'title'       => 'required|string',
                'description' => 'required|string',
            ], [
                'title.required'       => __('messages.enquiry.validation.title_required'),
                'description.required' => __('messages.enquiry.validation.description_required'),
           ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => $validator->errors()->first()
                ], 201);
            }

            //  Store enquiry
            $enquiry = Enquiry::create([
                'user_id'     => $user->id,
                'phone'       => $user->phone ?? '', // assuming phone is in users table
                'title'       => $request->title,
                'description' => $request->description,
            ]);

            return response()->json([
                'status'  => true,
               'message' => __('messages.enquiry.success'),
                'data'    => $enquiry
            ],200);
    }

    public function getEnquiryAnswer(Request $request)
    {
        // Get authenticated user
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'status' => false,
                  'message' => __('messages.enquiry.user_not_authenticated'),
            ], 401);
        }

        // 🔹 Detect user's preferred language from UserLang table
        $userLang = UserLang::where('user_id', $user->id)
            ->where('device_id', $user->device_id)
            ->where('device_type', $user->device_type)
            ->first();

        $lang = $userLang->language ?? 'ru'; // fallback to Russian
        app()->setLocale($lang);

        // Fetch all enquiries/answers for this user
        $enquiries = Enquiry::where('user_id', $user->id)
                            ->orderBy('created_at', 'asc')
                            ->get(['id', 'user_id', 'title', 'description', 'answer', 'created_at']);

        return response()->json([
            'status' => true,
            'message' => __('messages.enquiry.fetch_success'),
            'data'   => $enquiries,
        ],200);
    }

    public function storeReport(Request $request)
    {
        // 1️⃣ Authenticate user
        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => __('messages.report.user_not_authenticated'),
            ], 401);
        }

        // 2️⃣ Detect user language
        $userLang = UserLang::where('user_id', $user->id)
            ->where('device_id', $user->device_id)
            ->where('device_type', $user->device_type)
            ->first();

        $lang = $userLang->language ?? 'ru';
        app()->setLocale($lang);

        // 3️⃣ Validation
        $validator = Validator::make($request->all(), [
            'mobile_number' => 'required|string|min:8|max:15',
            'description'   => 'required|string',
        ], [
            'mobile_number.required' => __('messages.report.validation.mobile_required'),
            'description.required'   => __('messages.report.validation.description_required'),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ], 201);
        }

        // 4️⃣ Save report
        $report = Report::create([
            'user_id'       => $user->id,
            'mobile_number' => $request->mobile_number,
            'description'   => $request->description,
        ]);

        // 5️⃣ Return success response
        return response()->json([
            'status'  => true,
            'message' => __('messages.report.success'),
            'data'    => $report,
        ], 200);
    }


    public function blockUser(Request $request)
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => __('messages.block.user_not_authenticated'),
            ], 401);
        }

        // 2️⃣ Detect user language
        $userLang = UserLang::where('user_id', $user->id)
            ->where('device_id', $user->device_id)
            ->where('device_type', $user->device_type)
            ->first();

        $lang = $userLang->language ?? 'ru';
        app()->setLocale($lang);

        $validator = Validator::make($request->all(), [
            'blocked_user_id' => 'required|exists:users,id|not_in:' . $user->id,
        ], [
            'blocked_user_id.required' => __('messages.block.validation.blocked_user_required'),
            'blocked_user_id.exists'   => __('messages.block.validation.blocked_user_exists'),
            'blocked_user_id.not_in'   => __('messages.block.validation.cannot_block_self'),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        // check if already blocked
        $existingBlock = \App\Models\UserBlock::where('user_id', $user->id)
            ->where('blocked_user_id', $request->blocked_user_id)
            ->first();

        if ($existingBlock) {
            return response()->json([
                'status'  => false,
                'message' => __('messages.block.already_blocked'),
            ], 200);
        }

        // store new block
        \App\Models\UserBlock::create([
            'user_id'         => $user->id,
            'blocked_user_id' => $request->blocked_user_id,
        ]);

        return response()->json([
            'status'  => true,
            'message' => __('messages.block.success'),
        ], 200);
    }

    public function unblockUser(Request $request)
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => __('messages.block.user_not_authenticated'),
            ], 401);
        }

        // 2️⃣ Detect user language
        $userLang = UserLang::where('user_id', $user->id)
            ->where('device_id', $user->device_id)
            ->where('device_type', $user->device_type)
            ->first();

        $lang = $userLang->language ?? 'ru';
        app()->setLocale($lang);

        $validator = Validator::make($request->all(), [
            'blocked_user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        \App\Models\UserBlock::where('user_id', $user->id)
            ->where('blocked_user_id', $request->blocked_user_id)
            ->delete();

        return response()->json([
            'status'  => true,
            'message' => __('messages.block.unblocked'),
        ], 200);
    }

    public function getBlockedUsers(Request $request)
    {
        $user = Auth::guard('api')->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => __('messages.block.user_not_authenticated'),
            ], 401);
        }

        // 🔹 Detect user language
        $userLang = UserLang::where('user_id', $user->id)
            ->where('device_id', $user->device_id)
            ->where('device_type', $user->device_type)
            ->first();

        $lang = $userLang->language ?? 'ru';
        app()->setLocale($lang);

        // 🔹 Fetch all blocked users with their details
        $blockedUsers = \App\Models\UserBlock::with('blockedUser:id,name,image')
            ->where('user_id', $user->id)
            ->get()
            ->map(function ($block) {
                return [
                    'blocked_user_id' => $block->blocked_user_id,
                    'name'            => $block->blockedUser->name ?? null,
                    'image'   => $block->blockedUser->image ?? null,
                ];
            });

        return response()->json([
            'status' => true,
            'message' => __('messages.block.list_retrieved'),
            'data' => $blockedUsers,
        ], 200);
    }









    
}
