<?php

namespace App\Http\Controllers;
use App\Models\City; 
use App\Models\CarModel; 
use Illuminate\Http\Request;

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
            'success' => true,
            'data' => $brands
        ]);
    }

    public function getModelsByBrand(string $brand)
    {
        $models = CarModel::where('brand', $brand)->pluck('model_name');

        if ($models->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No models found for this brand'
            ], 404);
        }

        return response()->json([
            'success' => true,
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
                'success' => false,
                'message' => 'No colors found for this model'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'model' => $model,
            'data' => $colors
        ]);
    }

    
}
