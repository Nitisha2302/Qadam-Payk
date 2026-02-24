@extends('admin.layouts.app')

@section('content')
<div class="main-box-content main-space-box">
    <section class="project-doorbox">

        <div class="ai-training-data-wrapper d-flex align-items-baseline justify-content-between">
            <div class="heading-content-box">
                <h2>City-wise Courier Revenue Report</h2>
            </div>

            
        </div>

        <form method="GET" action="{{ route('dashboard.admin.courier.reports.index') }}" class="d-flex gap-2">

                <input type="text" name="city"
                       class="form-control"
                       placeholder="Filter by city"
                       value="{{ request('city') }}">

                <input type="date" name="date"
                       class="form-control"
                       value="{{ request('date') }}">

                <button type="submit" class="btn btn-success">Filter</button>

                @if(request()->filled('city') || request()->filled('date'))
                    <a href="{{ route('dashboard.admin.courier.reports.index') }}"
                       class="btn btn-secondary">Reset</a>
                @endif

            </form>

        <div class="project-ongoing-box mt-3">
            <table class="table table-striped table-bordered table-notification-list">
                <thead>
                    <tr>
                        <th>City</th>
                        <th>Total Orders</th>
                        <th>Total Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reportData as $row)
                        <tr>
                            <td>{{ $row['city'] }}</td>
                            <td>{{ $row['total_orders'] }}</td>
                            <td>₹{{ number_format($row['total_revenue'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center">No Data Found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="d-flex justify-content-end gap-2 mt-3">
                <a href="{{ route('dashboard.admin.courier.reports.export', ['type' => 'csv'] + request()->all()) }}"
                   class="btn btn-success btn-sm">
                   Export CSV
                </a>

                <a href="{{ route('dashboard.admin.courier.reports.export', ['type' => 'pdf'] + request()->all()) }}"
                   class="btn btn-danger btn-sm">
                   Export PDF
                </a>
            </div>
        </div>

    </section>
</div>
@endsection
