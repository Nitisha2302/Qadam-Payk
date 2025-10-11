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
        $query = RideBooking::with(['user', 'driver', 'service'])->orderBy('id', 'desc');


        // Optional filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', fn($sub) => $sub->where('name', 'like', "%$search%"))
                    ->orWhereHas('driver', fn($sub) => $sub->where('name', 'like', "%$search%"))
                    ->orWhere('pickup_location', 'like', "%$search%")
                    ->orWhere('destination', 'like', "%$search%");
            });
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween(DB::raw('DATE(created_at)'), [$request->from_date, $request->to_date]);
        }

        $bookings = $query->paginate(10);

        return view('admin.bookings.index', compact('bookings'));
    }

    /**
     * Show details of a single booking
     */
    public function show($id)
    {
        $booking = RideBooking::with(['user', 'driver', 'service'])->findOrFail($id);
        return response()->json($booking);
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
    public function destroy($id)
    {
        $booking = RideBooking::findOrFail($id);
        $booking->delete();

        return response()->json(['success' => true, 'message' => 'Booking deleted successfully.']);
    }
}
