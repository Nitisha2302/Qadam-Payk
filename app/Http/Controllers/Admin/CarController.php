<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CarModel; 
use App\Models\Service; 


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
            'seats'     => 'nullable|integer|min:1',
        ], [
            'car_model.required' => 'Please enter the car model.',
            'car_model.string'   => 'Car model must be valid text.',
            'car_model.max'      => 'Car model cannot exceed 255 characters.',

            'brand.required'     => 'Please enter the car brand.',
            'brand.string'       => 'Brand must be valid text.',
            'brand.max'          => 'Brand cannot exceed 255 characters.',

            'seats.integer'      => 'Seats must be a whole number.',
            'seats.min'          => 'Seats must be at least 1.',

        ]);

        // Store the car
        CarModel::create([
            'model_name'     => $request->car_model,
            'brand'    => $request->brand,
            'seats'      => $request->seats,
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
            'seats'     => 'nullable|integer|min:1',
        ], [
            'model_name.required' => 'Please enter the car model.',
            'model_name.string'   => 'Car model must be valid text.',
            'model_name.max'      => 'Car model cannot exceed 255 characters.',
            'brand.string'        => 'Car brand must be valid text.',
            'brand.max'           => 'Car brand cannot exceed 255 characters.',
             'seats.integer'      => 'Seats must be a whole number.',
            'seats.min'          => 'Seats must be at least 1.',

        ]);

        // Update car
        $car->update([
            'model_name' => $request->model_name,
            'brand'      => $request->brand,
            'seats'      => $request->seats,
        ]);

        return redirect()->route('dashboard.admin.all-cars')
                        ->with('success', 'Car updated successfully!');
    }


    public function deleteCar(Request $request)
    {
        $request->validate([
            'car_id' => 'required|exists:car_models,id',
        ]);

        $Car = CarModel::find($request->car_id);

        if ($Car) {
            $Car->delete();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Car not found']);
    }


    public function servicesList() {
        // Fetch cities (latest first, 5 per page)
        $services = Service::orderBy('id', 'desc')->paginate(10);
        // Return Blade view with cities data
        return view('admin.services.servicesListing', compact('services'));
    }

    public function addService(){
      return view('admin.services.addService');
    }

     public function storeService(Request $request)
    {
        // Validation with custom error messages
        $request->validate([
            'service_name' => 'required|string|max:255',
        ], [
            'service_name.required' => 'Please enter the service name.',
            'service_name.string'   => 'Srvice name must be valid text.',
            'service_name.max'      => 'Service name cannot exceed 255 characters.',
        ]);

        // Store the car
        Service::create([
            'service_name'     => $request->service_name,
        ]);

        // Redirect back with success
        return redirect()->route('dashboard.admin.all-services')
                        ->with('success', 'Service added successfully!');
    }


    public function editService($id)
    {
        $service = Service::findOrFail($id);
        return view('admin.services.editService', compact('service'));
    }


    public function updateService(Request $request, $id)
    {
        // Validate input with custom messages
        $request->validate([
            'service_name' => 'required|string|max:255',
        ], [
            'service_name.required' => 'Please enter the service name.',
            'service_name.string'   => 'Service name must be valid text.',
            'service_name.max'      => 'Service name cannot exceed 255 characters.',
        ]);

        // Find the service
        $service = Service::findOrFail($id);

        // Update service
        $service->update([
            'service_name' => $request->service_name,
        ]);

        // Redirect with success message
        return redirect()->route('dashboard.admin.all-services')
                        ->with('success', 'Service updated successfully!');
    }


    public function deleteService(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
        ]);

        $service = Service::find($request->service_id);

        if ($service) {
            $service->delete();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'service not found']);
    }


}
