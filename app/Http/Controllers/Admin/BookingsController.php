<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RideBooking;
use App\Models\User;
use App\Models\Service;
use Illuminate\Support\Facades\DB;

class BookingsController extends Controller
{
    /**
     * Display a listing of all bookings
     */
    public function index(Request $request)
    {
        $query = RideBooking::with(['user', 'rideDriver', 'requestDriver', 'ride', 'request'])
            ->orderBy('id', 'desc');

        // Status filter
        if ($request->filled('status')) {
            switch ($request->status) {
                case 'pending':
                    // Active status 0 and not cancelled
                    $query->where('active_status', 0)
                        ->where('status', '!=', 'cancelled');
                    break;
                case 'confirmed':
                    $query->where('status', 'confirmed')
                        ->where('active_status', 0);
                    break;
                case 'active':
                    $query->where('active_status', 1);
                    break;
                case 'completed':
                    $query->where('active_status', 2);
                    break;
                case 'cancelled':
                    $query->where('status', 'cancelled');
                    break;
            }
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                // Passenger via ride
                $q->whereHas('user', fn($sub) => $sub->where('name', 'like', "%$search%"))
                // Passenger via request
                ->orWhereHas('request.user', fn($sub) => $sub->where('name', 'like', "%$search%"))
                // Driver via ride
                ->orWhereHas('rideDriver', fn($sub) => $sub->where('name', 'like', "%$search%"))
                // Driver via request
                ->orWhereHas('requestDriver', fn($sub) => $sub->where('name', 'like', "%$search%"))
                // Pickup/destination via ride
                ->orWhereHas('ride', fn($sub) => $sub->where('pickup_location', 'like', "%$search%")
                                                    ->orWhere('destination', 'like', "%$search%"))
                // Pickup/destination via request
                ->orWhereHas('request', fn($sub) => $sub->where('pickup_location', 'like', "%$search%")
                                                        ->orWhere('destination', 'like', "%$search%"));
            });
        }

        // Ride date filter
        if ($request->filled('ride_date')) {
            $query->whereDate('ride_date', $request->ride_date);
        }

        // Type filter
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $bookings = $query->paginate(10);

        return view('admin.bookings.index', compact('bookings'));
    }


    /**
     * Update booking status
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled',
        ]);

        $booking = RideBooking::findOrFail($id);
        $booking->status = $request->status;
        $booking->save();

        return response()->json(['success' => true, 'message' => 'Booking status updated successfully.']);
    }

    /**
     * Delete a booking
     */

    public function deleteBooking(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:ride_bookings,id',
        ]);

        $rideBooking = RideBooking::find($request->booking_id);

        if ($rideBooking) {
            $rideBooking->delete();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'booking not found']);
    }
}
