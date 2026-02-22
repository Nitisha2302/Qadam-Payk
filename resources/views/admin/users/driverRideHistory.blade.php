@extends('admin.layouts.app')

@section('content')
<div class="main-box-content main-space-box">
    <section class="project-doorbox">
        <div class="ai-training-data-wrapper d-flex align-items-baseline justify-content-between">
            <div class="heading-content-box">
                <h2>Ride History of {{ $driver->name }}</h2>

                <form method="GET" action="{{ route('dashboard.admin.driverRideHistory', ['driver_id' => $driver->id]) }}" class="d-flex gap-2 mb-3">
                    <input type="text" name="search" class="form-control" placeholder="Search by pickup or destination" value="{{ request('search') }}">
                    <button type="submit" class="btn btn-success">Search</button>
                    @if(request()->has('search'))
                        <a href="{{ route('dashboard.admin.driverRideHistory', ['driver_id' => $driver->id]) }}" class="btn btn-secondary">Reset</a>
                    @endif
                </form>

                @if (session('success'))
                    <div class="alert alert-success" role="alert" id="success-message">
                        {{ session('success') }}
                    </div>
                @endif
            </div>
        </div>

        <div class="project-ongoing-box">
            <table class="table table-striped table-bordered table-notification-list">
                <thead>
                    <tr>
                        <th>Pickup</th>
                        <th>Destination</th>
                        <th>Seats</th>
                        <th>Budget</th>
                        <th>Type</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rides as $ride)
                        <tr>
                            <td>{{ $ride->pickup_location }}</td>
                            <td>{{ $ride->destination }}</td>
                            <td>{{ $ride->number_of_seats }}</td>
                            <td>{{ $ride->price }}</td>
                            <td>{{ $ride->accept_parcel == 0 ? 'Ride' : 'Parcel' }}</td>
                            <td>{{ $ride->ride_date }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No rides found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @php
            $rideList = $rides;
        @endphp

        {{-- Pagination --}}
        @if ($rideList->lastPage() > 1)
            <nav class="pt-3" aria-label="Page navigation">
                <ul class="pagination" id="pagination-links">
                    {{-- Previous Page --}}
                    @if ($rideList->onFirstPage())
                        <li class="page-item disabled"><span class="page-link text-dark">Previous</span></li>
                    @else
                        <li class="page-item"><a class="page-link text-dark" href="{{ $rideList->previousPageUrl() }}">Previous</a></li>
                    @endif

                    {{-- Page Numbers --}}
                    @for ($i = 1; $i <= $rideList->lastPage(); $i++)
                        <li class="page-item {{ $rideList->currentPage() == $i ? 'active' : '' }}">
                            <a class="page-link text-dark" href="{{ $rideList->url($i) }}">{{ $i }}</a>
                        </li>
                    @endfor

                    {{-- Next Page --}}
                    @if ($rideList->hasMorePages())
                        <li class="page-item"><a class="page-link text-dark" href="{{ $rideList->nextPageUrl() }}">Next</a></li>
                    @else
                        <li class="page-item disabled"><span class="page-link text-dark">Next</span></li>
                    @endif
                </ul>
            </nav>
        @endif

    </section>
</div>
@endsection
