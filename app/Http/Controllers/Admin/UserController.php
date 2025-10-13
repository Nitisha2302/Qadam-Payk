<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User; 
use App\Models\Ride; // Make sure you have a Ride model
class UserController extends Controller
{


    public function driversList(Request $request)
    {
        $query = User::whereHas('rides'); // only drivers who have rides

        // 🔍 Search by name or phone number
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        // ✅ Filter by verification status
        if ($request->filled('status')) {
            $query->where('id_verified', $request->status);
        }

         // ✅ Filter by block/unblock status
        if ($request->has('blocked')) {
            $blocked = $request->get('blocked'); // string '0' or '1'

            // Only apply if it’s exactly '0' or '1'
            if ($blocked === '0' || $blocked === '1') {
                $query->where('is_blocked', (int)$blocked);
            }
        }

        $users = $query->orderBy('id', 'desc')->paginate(10);

        return view('admin.users.driversListing', compact('users'));
    }


    public function passengersList(Request $request)
    {
        $query = User::whereHas('rideBookings'); // only passengers who have booked rides

        // 🔍 Search by name or phone
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        if ($request->has('blocked')) {
                $blocked = $request->get('blocked'); // string '0' or '1'

                // Only apply if it’s exactly '0' or '1'
                if ($blocked === '0' || $blocked === '1') {
                    $query->where('is_blocked', (int)$blocked);
                }
            }

        $users = $query->orderBy('id', 'desc')->paginate(10);

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


    public function toggleBlockUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::find($request->user_id);

        // Toggle blocked status
        $user->is_blocked = !$user->is_blocked;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => $user->is_blocked ? 'User blocked successfully.' : 'User unblocked successfully.',
            'is_blocked' => $user->is_blocked
        ]);
    }


    public function driverRideHistory($driver_id, Request $request)
    {
        $driver = User::findOrFail($driver_id);

        // Fetch rides for this driver
        $query = Ride::where('user_id', $driver_id);

        // Optional search: by pickup or destination
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('pickup_location', 'like', "%{$search}%")
                ->orWhere('destination', 'like', "%{$search}%");
            });
        }

        $rides = $query->orderBy('created_at', 'desc')
                    ->paginate(10)
                    ->appends($request->only('search'));

        return view('admin.users.driverRideHistory', compact('driver', 'rides'));
    }




}

