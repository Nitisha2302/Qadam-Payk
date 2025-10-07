<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User; 

class UserController extends Controller
{


   public function driversList()
    {
        // Only drivers (users who have rides)
        $users = User::whereHas('rides')
                    ->orderBy('id', 'desc')
                    ->paginate(10);
        return view('admin.users.driversListing', compact('users'));
    }

    public function passengersList()
    {
        $users = User::whereHas('rideBookings')
                    ->orderBy('id', 'desc')
                    ->paginate(10);

        return view('admin.users.passengersListing', compact('users'));
    }



    public function deleteUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::find($request->user_id);

        if ($user) {
            $user->delete();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'User not found']);
    }

    public function verifyUser(Request $request)
    {
        $user = User::findOrFail($request->user_id);
        $user->id_verified = 1; // Verified
        $user->save();

        return response()->json(['success' => true, 'message' => 'User verified successfully.']);
    }

    public function rejectUser(Request $request)
    {
        $user = User::findOrFail($request->user_id);
        $user->id_verified = 2; // Rejected
        $user->save();

        return response()->json(['success' => true, 'message' => 'User rejected successfully.']);
    }

}

