<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\City;

class DashboardController extends Controller
{
    public function Dashboard(){
        $userCount = User::where(function ($query) {
        $query->where('role', '!=', 1)
              ->orWhereNull('role');
       })->count();

        $cityCount = City::count();

       return view('admin.dashboard', compact('userCount', 'cityCount'));
    }

    

    
}
