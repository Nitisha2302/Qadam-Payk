@extends('admin.layouts.app')

@section('content')
<div class="main-box-content main-space-box">
    <section class="project-doorbox">
        <div class="ai-training-data-wrapper d-flex align-items-baseline justify-content-between">
            <div class="heading-content-box">
                <h2>City-wise Ride & Revenue Report</h2>
                <div id="successMessage" class="alert alert-success d-none"></div>
            </div>

            {{-- Filter Form --}}
            <form method="GET" action="{{ route('dashboard.admin.reports.index') }}" class="d-flex gap-2">
                <!-- <input type="text" name="search" class="form-control" placeholder="Search city or driver" value="{{ request('search') }}"> -->
                <input type="text" name="city" class="form-control" placeholder="Filter by city" value="{{ request('city') }}">
                <input type="date" name="ride_date" class="form-control" value="{{ request('ride_date') }}">
                <button type="submit" class="btn btn-success">Filter</button>

                @if(request()->hasAny(['search','city','ride_date']) && collect(request()->only(['search','city','ride_date']))->filter()->isNotEmpty())
                    <a href="{{ route('dashboard.admin.reports.index') }}" class="btn btn-secondary">Reset</a>
                @endif
            </form>

        </div>

        {{-- Report Table --}}
        <div class="project-ongoing-box mt-3">
            <table class="table table-striped table-bordered table-notification-list">
                <thead>
                    <tr>
                        <th>City</th>
                        <th>Total Bookings</th>
                        <th>Total Revenue</th>
                        <th>Drivers</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reportData as $row)
                        <tr>
                            <td>{{ $row['city'] }}</td>
                            <td>{{ $row['total_bookings'] }}</td>
                            <td>{{ number_format($row['total_revenue'], 2) }}</td>
                            <td>{{ $row['drivers'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Export Buttons --}}
            <div class="d-flex justify-content-end gap-2 mt-3">
                <a href="{{ route('dashboard.admin.reports.export', ['type' => 'csv']) }}" class="btn btn-success btn-sm">Export CSV</a>
                <a href="{{ route('dashboard.admin.reports.export', ['type' => 'pdf']) }}" class="btn btn-danger btn-sm">Export PDF</a>

                <!-- <a href="{{ url('admin/reports/export/csv') }}" class="btn btn-success btn-sm">Export CSV</a>
                <a href="{{ url('admin/reports/export/pdf') }}" class="btn btn-danger btn-sm">Export PDF</a> -->
            </div>
        </div>
    </section>
</div>

{{-- Modal for Viewing Report --}}
<div class="modal fade" id="reportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-weight:800;color:#86c349;font-size:22px;">City Report Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-striped table-bordered table-notification-list">
                    <tbody>
                        <tr><th>City</th><td id="modal-city">-</td></tr>
                        <tr><th>Driver</th><td id="modal-driver">-</td></tr>
                        <tr><th>Total Bookings</th><td id="modal-bookings">-</td></tr>
                        <tr><th>Total Revenue</th><td id="modal-revenue">-</td></tr>
                    </tbody>
                </table>
                
            </div>
            <div class="modal-footer">
                <button type="button" class="delete-confirmation-popup-btn btn w-auto px-3" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- JS --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).on('click', '.view-report-details', function() {
    const report = $(this).data('report');
    $('#modal-city').text(report.city || 'N/A');
    $('#modal-driver').text(report.user_name || 'N/A');
    $('#modal-bookings').text(report.total_bookings || '0');
    $('#modal-revenue').text('₹' + (report.total_revenue ? parseFloat(report.total_revenue).toFixed(2) : '0.00'));
});
</script>
@endsection
