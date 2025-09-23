<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\City;

class DashboardController extends Controller
{
    public function Dashboard() {
        // Base query to exclude admin
        $baseQuery = User::where(function ($query) {
            $query->where('role', '!=', 1)
                ->orWhereNull('role');
        });

        $userCount    = $baseQuery->count();
        $cityCount    = City::count();


        $driversQuery = User::where(function ($query) {
            $query->where('role', '!=', 1)
                ->orWhereNull('role');
        })
        ->whereHas('rides'); // Only drivers who have rides

        $driversCount = $driversQuery->count();

        // Drivers by ID verification status
        $pendingDrivers  = (clone $driversQuery)->where('id_verified', 0)->count();
        $verifiedDrivers = (clone $driversQuery)->where('id_verified', 1)->count();
        $rejectedDrivers = (clone $driversQuery)->where('id_verified', 2)->count();

    

        return view('admin.dashboard', compact(
            'userCount', 'cityCount', 'pendingDrivers', 'verifiedDrivers', 'rejectedDrivers','driversCount'
        ));
    }


    

    
}
