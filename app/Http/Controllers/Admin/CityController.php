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

        // 🌐 Optional language filter
        if ($request->filled('language_filter')) {
            $query->where('language_code', $request->language_filter);
        }

        $cities = $query->orderBy('id', 'desc')->paginate(10);

        // Distinct list of countries for dropdown
        $countries = City::select('country')->whereNotNull('country')->distinct()->pluck('country');
         $languages = City::select('language_code')->whereNotNull('language_code')->distinct()->pluck('language_code');

        return view('admin.city.cityListing', compact('cities', 'countries', 'languages'));
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
            'language_code' => 'required|string|max:10',
        ], [
            'city_name.required' => 'Please enter the city name.',
            'city_name.string'   => 'City name must be a valid text.',
            'city_name.max'      => 'City name cannot exceed 255 characters.',

            'country.string'     => 'Country must be a valid text.',
            'country.max'        => 'Country cannot exceed 255 characters.',

             'language_code.required' => 'Please select the city language.',
            'language_code.string'   => 'City language must be a valid text.',
            'language_code.max'      => 'City language cannot exceed 10 characters.',
        ]);

        // Create city
        City::create([
            'city_name' => $request->city_name,
            'country'   => $request->country,
            'language_code' => $request->language_code,
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
             'language_code' => 'required|string|max:10',
        ], [
            'city_name.required' => 'Please enter the city name.',
            'city_name.string'   => 'City name must be a valid text.',
            'city_name.max'      => 'City name cannot exceed 255 characters.',
            'country.string'     => 'Country must be a valid text.',
            'country.max'        => 'Country cannot exceed 255 characters.',
             'language_code.required' => 'Please select the city language.',
            'language_code.string'   => 'City language must be a valid text.',
            'language_code.max'      => 'City language cannot exceed 10 characters.',
        ]);

        $city->update([
            'city_name' => $request->city_name,
            'country'   => $request->country,
             'language_code' => $request->language_code,
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
