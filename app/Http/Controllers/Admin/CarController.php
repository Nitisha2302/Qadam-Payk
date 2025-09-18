<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CarModel; 


class CarController extends Controller
{
    public function carList() {
        // Fetch cities (latest first, 5 per page)
        $cars = CarModel::orderBy('id', 'desc')->paginate(10);
        // Return Blade view with cities data
        return view('admin.car.carListing', compact('cars'));
    }

    public function addCar(){
      return view('admin.car.addCar');
    }

    public function storeCar(Request $request)
    {
        // Validation with custom error messages
        $request->validate([
            'car_model' => 'required|string|max:255',
            'brand'     => 'required|string|max:255',
            'color'     => 'nullable|string|max:100',
            'features'  => 'nullable|array',
            'features.*'=> 'string|max:100',
        ], [
            'car_model.required' => 'Please enter the car model.',
            'car_model.string'   => 'Car model must be valid text.',
            'car_model.max'      => 'Car model cannot exceed 255 characters.',

            'brand.required'     => 'Please enter the car brand.',
            'brand.string'       => 'Brand must be valid text.',
            'brand.max'          => 'Brand cannot exceed 255 characters.',

            'color.string'       => 'Color must be valid text.',
            'color.max'          => 'Color cannot exceed 100 characters.',

            'features.array'     => 'Features must be an array.',
            'features.*.string'  => 'Each feature must be valid text.',
            'features.*.max'     => 'Each feature cannot exceed 100 characters.',
        ]);

        // Store the car
        CarModel::create([
            'model_name'     => $request->car_model,
            'brand'    => $request->brand,
            'color'    => $request->color,
            'features' => $request->features ? json_encode($request->features) : "N/A",
        ]);

        // Redirect back with success
        return redirect()->route('dashboard.admin.all-cars')
                        ->with('success', 'Car added successfully!');
    }

    public function editCar($id)
    {
        $car = CarModel::findOrFail($id);
        return view('admin.car.editCar', compact('car'));
    }

    public function updateCar(Request $request, $id)
    {
        $car = CarModel::findOrFail($id);

        // Validation
        $request->validate([
            'model_name' => 'required|string|max:255',
            'brand'      => 'nullable|string|max:255',
            'color'      => 'nullable|string|max:255',
            // 'features'   => 'nullable|array', // features stored as array (checkboxes)
            // 'features.*' => 'string|max:255',
        ], [
            'model_name.required' => 'Please enter the car model.',
            'model_name.string'   => 'Car model must be valid text.',
            'model_name.max'      => 'Car model cannot exceed 255 characters.',
            'brand.string'        => 'Car brand must be valid text.',
            'brand.max'           => 'Car brand cannot exceed 255 characters.',
            'color.string'        => 'Car color must be valid text.',
            'color.max'           => 'Car color cannot exceed 255 characters.',
            // 'features.array'      => 'Features must be an array.',
            // 'features.*.string'   => 'Each feature must be valid text.',
            // 'features.*.max'      => 'Each feature cannot exceed 255 characters.',
        ]);

        // Update car
        $car->update([
            'model_name' => $request->model_name,
            'brand'      => $request->brand,
            'color'      => $request->color,
            // 'features'   => $request->features ? json_encode($request->features) : null,
        ]);

        return redirect()->route('dashboard.admin.all-cars')
                        ->with('success', 'Car updated successfully!');
    }


}
