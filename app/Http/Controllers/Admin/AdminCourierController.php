<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourierRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminCourierController extends Controller
{
    public function index(Request $request)
    {
        $query = CourierRequest::with([
            'sender',
            'acceptedDriver',
            'interests.driver'
        ])->latest();

        // ✅ Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // ✅ Search Filter
        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {

                $q->where('pickup_location', 'like', "%{$search}%")
                ->orWhere('drop_location', 'like', "%{$search}%")

                // Search by sender name
                ->orWhereHas('sender', function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%");
                })

                // Search by driver name
                ->orWhereHas('acceptedDriver', function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%");
                });
            });
        }

        $couriers = $query->paginate(20)->withQueryString();

        return view('admin.couriers.index', compact('couriers'));
    }


    public function show($id)
    {
        $courier = CourierRequest::with([
            'sender',
            'acceptedDriver',
            'interests.driver'
        ])->findOrFail($id);

        return view('admin.couriers.show', compact('courier'));
    }


     public function indexReport(Request $request)
    {
        $reportData = $this->getReportData($request);

        return view('admin.courier_reports.index', compact('reportData'));
    }

    private function getReportData($request)
    {
        $query = CourierRequest::where('status', 'completed');

        // Filter by city
        if ($request->filled('city')) {
            $query->where('pickup_location', 'like', "%{$request->city}%");
        }

        // Filter by date
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        return $query
            ->select(
                'pickup_location as city',
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(suggested_price) as total_revenue')
            )
            ->groupBy('pickup_location')
            ->get()
            ->toArray();
    }

    public function export(Request $request)
    {
        $type = $request->type;
        $reportData = $this->getReportData($request);

        if ($type == 'csv') {

            $filename = "courier_report.csv";

            $headers = [
                "Content-type" => "text/csv",
                "Content-Disposition" => "attachment; filename=$filename",
            ];

            $callback = function () use ($reportData) {
                $file = fopen('php://output', 'w');

                fputcsv($file, ['City', 'Total Orders', 'Total Revenue']);

                foreach ($reportData as $row) {
                    fputcsv($file, [
                        $row['city'],
                        $row['total_orders'],
                        number_format($row['total_revenue'], 2)
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        if ($type == 'pdf') {

            $pdf = Pdf::loadView('admin.courier_reports.pdf', compact('reportData'));

            return $pdf->download('courier_report.pdf');
        }
    }
}
