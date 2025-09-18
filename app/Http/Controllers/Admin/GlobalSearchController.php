<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\City;
use App\Models\CarModel; 
use App\Models\Report;
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
        if (str_contains($currentRoute, 'all-doctors')) {
            $users = User::where('name', 'like', "%{$searchTerm}%")
                ->paginate(10)
                ->appends($request->only('search'));

            return view('admin.doctors.userList', [
                'users' => $users,
                'search_users' => $users,
            ]);
        }

        // Search Cities
        if (str_contains($currentRoute, 'cities')) {
            $cities = City::where('city_name', 'like', "%{$searchTerm}%")
                ->orWhere('state', 'like', "%{$searchTerm}%")
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
                ->orWhere('color', 'like', "%{$searchTerm}%")
                ->orderBy('id', 'desc')
                ->paginate(10)
                ->appends($request->only('search'));

            return view('admin.car.carListing', [
                'cars' => $cars,
                'search_cars' => $cars, // optional
            ]);
        }

        // Default fallback (optional)
        return redirect()->back()->with('error', 'Search not available on this page.');
    }
}
