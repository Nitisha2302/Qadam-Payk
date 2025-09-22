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

        // Users by ID verification status (exclude admin)
        $pendingCount  = (clone $baseQuery)->where('id_verified', 0)->count();
        $verifiedCount = (clone $baseQuery)->where('id_verified', 1)->count();
        $rejectedCount = (clone $baseQuery)->where('id_verified', 2)->count();

        return view('admin.dashboard', compact(
            'userCount', 'cityCount', 'pendingCount', 'verifiedCount', 'rejectedCount'
        ));
    }


    

    
}
