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

class HomeController extends Controller
{
    public function getCity()
    {
        // Fetch all height_key records
        $cities = City::all();

        // Return a structured JSON response
        return response()->json([
            'status' => true,
            'message' => 'Cities fetched successfully.',
            'data' => $cities
        ],200);
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

    public function getAllServices()
    {
        // Get all services with id and service_name
        $services = Service::select('id', 'service_name','service_image')->get();

        return response()->json([
            'status' => true,
                        'message' => 'Services fetched successfully.',
            'data'   => $services, // array of objects [{id:1, service_name:"WiFi"}, ...]
        ], 200);
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





    
}
