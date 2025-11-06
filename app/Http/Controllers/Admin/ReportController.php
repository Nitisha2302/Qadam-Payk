<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RideBooking;
use Illuminate\Support\Facades\DB;
use PDF;

class ReportController extends Controller
{
    /**
     * ------------------------------------
     * ✅ GET FILTERED COMPLETED BOOKINGS
     * ------------------------------------
     */
   private function getFilteredBookings($search, $ride_date, $city = null)
{
    $query = RideBooking::with(['user', 'rideDriver', 'requestDriver', 'ride', 'request'])
        ->where('active_status', 2); // ✅ Only completed rides

    // 🔍 SEARCH
    if (!empty($search)) {
        $query->where(function ($q) use ($search) {
            $q->whereHas('user', fn($sub) => $sub->where('name', 'like', "%$search%"))
              ->orWhereHas('rideDriver', fn($sub) => $sub->where('name', 'like', "%$search%"))
              ->orWhereHas('requestDriver', fn($sub) => $sub->where('name', 'like', "%$search%"))
              ->orWhereHas('ride', fn($sub) => $sub->where('pickup_location', 'like', "%$search%"))
              ->orWhereHas('request', fn($sub) => $sub->where('pickup_location', 'like', "%$search%"));
        });
    }

    // 🏙️ CITY FILTER
    if (!empty($city)) {
        $query->where(function ($q) use ($city) {
            $q->whereHas('ride', fn($sub) => $sub->where('pickup_location', 'like', "%$city%"))
              ->orWhereHas('request', fn($sub) => $sub->where('pickup_location', 'like', "%$city%"));
        });
    }

    // 📅 SINGLE DATE FILTER (ride_date)
    if (!empty($ride_date)) {
        $query->whereDate('ride_date', $ride_date);
    }

    return $query->get();
}


    /**
     * ------------------------------------
     * ✅ TRANSFORM + GROUP BY CITY SUMMARY
     * ------------------------------------
     */
    private function transformReportData($bookings)
    {
        return $bookings
            ->map(function ($booking) {

                $isRide = !is_null($booking->ride_id);

                return [
                    'city' => $isRide
                        ? ($booking->ride->pickup_location ?? 'N/A')
                        : ($booking->request->pickup_location ?? 'N/A'),

                    'driver' => $isRide
                        ? ($booking->rideDriver->name ?? 'N/A')
                        : ($booking->user->name ?? 'N/A'),

                    'passenger' => $isRide
                        ? ($booking->user->name ?? 'N/A')
                        : ($booking->request->user->name ?? 'N/A'),

                    'price'   => $booking->price ?? 0,
                ];
            })
            ->groupBy('city')
            ->map(fn($items) => [
                'city'           => $items->first()['city'],
                'total_bookings' => $items->count(),
                'total_revenue'  => $items->sum('price'),
                'drivers'        => $items->pluck('driver')->unique()->implode(', '),
            ])
            ->values();
    }

    /**
     * ------------------------------------
     * ✅ INDEX (UI DISPLAY)
     * ------------------------------------
     */
   public function index(Request $request)
    {
        $bookings = $this->getFilteredBookings(
            $request->search,
            $request->ride_date,
            $request->city
        );

        $reportData = $this->transformReportData($bookings);

        return view('admin.reports.index', compact('reportData'));
    }

    /**
     * ------------------------------------
     * ✅ EXPORT CSV & PDF (MATCHES INDEX)
     * ------------------------------------
     */
    public function export(Request $request, $type)
    {
        $bookings = $this->getFilteredBookings(
            $request->search,
            $request->ride_date,
            $request->city
        );

        $reportData = $this->transformReportData($bookings);

        if ($type === 'csv') {
            $filename = 'city_revenue_report.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=$filename",
            ];

            $callback = function () use ($reportData) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['City', 'Total Bookings', 'Total Revenue', 'Drivers']);
                foreach ($reportData as $row) {
                    fputcsv($file, [
                        $row['city'],
                        $row['total_bookings'],
                        $row['total_revenue'],
                        $row['drivers'],
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        if ($type === 'pdf') {
            $pdf = PDF::loadView('admin.reports.export_pdf', compact('reportData'));
            return $pdf->download('city_revenue_report.pdf');
        }

        return redirect()->back();
    }


}
