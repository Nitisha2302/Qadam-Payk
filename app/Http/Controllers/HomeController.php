<?php

namespace App\Http\Controllers;
use App\Models\City; 
use App\Models\CarModel; 
use App\Models\Service;
use App\Models\Enquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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
                    'message' => 'User not authenticated'
                ], 401);
            }

            //  Validation
           $validator = Validator::make($request->all(), [
                'title'       => 'required|string',
                'description' => 'required|string',
            ], [
                'title.required'      => 'Title is required.',
                'description.required'      => 'Description ID is required.',
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
                'message' => 'Enquiry submitted successfully',
                'data'    => $enquiry
            ],200);
    }

    
}
