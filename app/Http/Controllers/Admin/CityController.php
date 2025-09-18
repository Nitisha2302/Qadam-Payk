<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\City; 

class CityController extends Controller
{
    public function cityList() {
        // Fetch cities (latest first, 5 per page)
        $cities = City::orderBy('id', 'desc')->paginate(10);
        // Return Blade view with cities data
        return view('admin.city.cityListing', compact('cities'));
    }

    public function addCity(){
      return view('admin.city.addCity');
    }

    public function storeCity(Request $request)
    {
        // Validation with custom error messages
        $request->validate([
            'city_name' => 'required|string|max:255',
            'state'     => 'nullable|string|max:255',
            'country'   => 'nullable|string|max:255',
        ], [
            'city_name.required' => 'Please enter the city name.',
            'city_name.string'   => 'City name must be a valid text.',
            'city_name.max'      => 'City name cannot exceed 255 characters.',

            'state.string'       => 'State must be a valid text.',
            'state.max'          => 'State cannot exceed 255 characters.',

            'country.string'     => 'Country must be a valid text.',
            'country.max'        => 'Country cannot exceed 255 characters.',
        ]);

        // Create city
        City::create([
            'city_name' => $request->city_name,
            'state'     => $request->state,
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
            'state'     => 'nullable|string|max:255',
            'country'   => 'nullable|string|max:255',
        ], [
            'city_name.required' => 'Please enter the city name.',
            'city_name.string'   => 'City name must be a valid text.',
            'city_name.max'      => 'City name cannot exceed 255 characters.',
            'state.string'       => 'State must be a valid text.',
            'state.max'          => 'State cannot exceed 255 characters.',
            'country.string'     => 'Country must be a valid text.',
            'country.max'        => 'Country cannot exceed 255 characters.',
        ]);

        $city->update([
            'city_name' => $request->city_name,
            'state'     => $request->state,
            'country'   => $request->country,
        ]);

        return redirect()->route('dashboard.admin.all-cities')
                        ->with('success', 'City updated successfully!');
    }




    


}
