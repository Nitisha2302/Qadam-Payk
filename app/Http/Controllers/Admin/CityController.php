<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\City; 

class CityController extends Controller
{
    public function cityList(Request $request)
    {
        $query = City::query();

        // 🔍 Search by city name or country
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('city_name', 'like', "%{$search}%")
                  ->orWhere('country', 'like', "%{$search}%");
            });
        }

        // 🌍 Optional country filter
        if ($request->filled('country_filter')) {
            $query->where('country', $request->country_filter);
        }

        $cities = $query->orderBy('id', 'desc')->paginate(10);

        // Distinct list of countries for dropdown
        $countries = City::select('country')->whereNotNull('country')->distinct()->pluck('country');

        return view('admin.city.cityListing', compact('cities', 'countries'));
    }

    public function addCity(){
      return view('admin.city.addCity');
    }

    public function storeCity(Request $request)
    {
        // Validation with custom error messages
        $request->validate([
            'city_name' => 'required|string|max:255',
            'country'   => 'nullable|string|max:255',
        ], [
            'city_name.required' => 'Please enter the city name.',
            'city_name.string'   => 'City name must be a valid text.',
            'city_name.max'      => 'City name cannot exceed 255 characters.',

            'country.string'     => 'Country must be a valid text.',
            'country.max'        => 'Country cannot exceed 255 characters.',
        ]);

        // Create city
        City::create([
            'city_name' => $request->city_name,
            'country'   => $request->country,
        ]);

        // Redirect back with success
        return redirect()->route('dashboard.admin.all-cities')
                        ->with('success', 'City added successfully!');
    }

    public function editCity($id)
    {
        $city = City::findOrFail($id);
        return view('admin.city.editCity', compact('city'));
    }

    public function updateCity(Request $request, $id)
    {
        $city = City::findOrFail($id);

        $request->validate([
            'city_name' => 'required|string|max:255',
            'country'   => 'nullable|string|max:255',
        ], [
            'city_name.required' => 'Please enter the city name.',
            'city_name.string'   => 'City name must be a valid text.',
            'city_name.max'      => 'City name cannot exceed 255 characters.',
            'country.string'     => 'Country must be a valid text.',
            'country.max'        => 'Country cannot exceed 255 characters.',
        ]);

        $city->update([
            'city_name' => $request->city_name,
            'country'   => $request->country,
        ]);

        return redirect()->route('dashboard.admin.all-cities')
                        ->with('success', 'City updated successfully!');
    }


    public function deleteCity(Request $request)
    {
        $request->validate([
            'city_id' => 'required|exists:cities,id',
        ]);

        $City = City::find($request->city_id);

        if ($City) {
            $City->delete();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'City not found']);
    }




    


}
