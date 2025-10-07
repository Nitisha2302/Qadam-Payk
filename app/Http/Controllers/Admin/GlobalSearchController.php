<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\City;
use App\Models\CarModel; 
use App\Models\Service;
use App\Models\rideBookings;
use Illuminate\Support\Facades\Auth;

class GlobalSearchController extends Controller
{
    public function search(Request $request)
    {
        $searchTerm = $request->query('search');
        $currentRoute = url()->previous(); // Get the referring URL

        // Fallback if search is empty
        if (!$searchTerm) {
            return redirect()->back();
        }

        // Determine which page you're on based on the route    
        // if (str_contains($currentRoute, 'all-drivers')) {
        //     $users = User::where('name', 'like', "%{$searchTerm}%")
        //      ->orWhere('email', 'like', "%{$searchTerm}%")
        //         ->orWhere('phone_number', 'like', "%{$searchTerm}%")
        //         ->paginate(10)
        //         ->appends($request->only('search'));

        //     return view('admin.users.driversListing', [
        //         'users' => $users,
        //         'search_users' => $users,
        //     ]);
        // }


        if (str_contains($currentRoute, 'all-drivers')) {
            $users = User::whereHas('rides') // ✅ only drivers
                ->where(function ($query) use ($searchTerm) {
                    $query->where('name', 'like', "%{$searchTerm}%")
                        ->orWhere('email', 'like', "%{$searchTerm}%")
                        ->orWhere('phone_number', 'like', "%{$searchTerm}%");
                })
                ->paginate(10)
                ->appends($request->only('search'));

            return view('admin.users.driversListing', [
                'users' => $users,
                'search_users' => $users,
            ]);
        }
        if (str_contains($currentRoute, 'all-passengers')) {
            $users = User::whereHas('rideBookings') // ✅ only passengers
                ->where(function ($query) use ($searchTerm) {
                    $query->where('name', 'like', "%{$searchTerm}%")
                        ->orWhere('email', 'like', "%{$searchTerm}%")
                        ->orWhere('phone_number', 'like', "%{$searchTerm}%");
                })
                ->paginate(10)
                ->appends($request->only('search'));

            return view('admin.users.passengersListing', [
                'users' => $users,
                'search_users' => $users,
            ]);
        }


        // Search Cities
        if (str_contains($currentRoute, 'cities')) {
            $cities = City::where('city_name', 'like', "%{$searchTerm}%")
                ->orWhere('country', 'like', "%{$searchTerm}%")
                ->orderBy('id', 'desc')
                ->paginate(10)
                ->appends($request->only('search'));

            return view('admin.city.cityListing', [
                'cities' => $cities,
                'search_cities' => $cities, // optional
            ]);
        }


        if (str_contains($currentRoute, 'cars')) {
            $cars = CarModel::where('model_name', 'like', "%{$searchTerm}%")
                ->orWhere('brand', 'like', "%{$searchTerm}%")
                ->orderBy('id', 'desc')
                ->paginate(10)
                ->appends($request->only('search'));

            return view('admin.car.carListing', [
                'cars' => $cars,
                'search_cars' => $cars, // optional
            ]);
        }


        if (str_contains($currentRoute, 'services')) {
            $services = Service::where('service_name', 'like', "%{$searchTerm}%")
                ->orderBy('id', 'desc')
                ->paginate(10)
                ->appends($request->only('search'));

            return view('admin.services.servicesListing', [
                'services' => $services,
                'search_services' => $services, // optional
            ]);
        }

        if (str_contains($currentRoute, 'all-query')) {
            $queries = User::whereHas('rides') // ✅ only drivers
                ->where(function ($query) use ($searchTerm) {
                    $query->where('title', 'like', "%{$searchTerm}%")
                        ->orWhere('description', 'like', "%{$searchTerm}%")
                        ->orWhere('phone_number', 'like', "%{$searchTerm}%");
                })
                ->paginate(10)
                ->appends($request->only('search'));

            return view('admin.enquiry.allQuery', [
                'queries' => $queries,
                'search_queries' => $queries,
            ]);
        }



        

        // Default fallback (optional)
        return redirect()->back()->with('error', 'Search not available on this page.');
    }
}
