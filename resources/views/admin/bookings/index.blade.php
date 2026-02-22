@extends('admin.layouts.app')

@section('content')
<div class="main-box-content main-space-box">
    <section class="project-doorbox">
        <div class="ai-training-data-wrapper d-flex align-items-baseline justify-content-between">
            <div class="heading-content-box">
                <h2>All Bookings</h2>
                <div id="successMessage" class="alert alert-success d-none"></div>
            </div>
            <form method="GET" action="{{ route('dashboard.admin.bookings.index') }}" class="d-flex gap-2">
                <input type="text" name="search" class="form-control" placeholder="Search by passenger, driver, location" value="{{ request('search') }}">
                <select name="status" class="form-control">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                     <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
                 <select name="type" class="form-control">
                    <option value="">All Types</option>
                    <option value="0" {{ request('type') === '0' ? 'selected' : '' }}>Ride</option>
                    <option value="1" {{ request('type') === '1' ? 'selected' : '' }}>Parcel</option>
                </select>
                <input type="date" name="ride_date" class="form-control" value="{{ request('ride_date') }}">
    
               
                <button type="submit" class="btn btn-success">Filter</button>
                @if(request()->hasAny(['search', 'status', 'type', 'ride_date']) && collect(request()->only(['search', 'status', 'type', 'ride_date']))->filter()->isNotEmpty())
                    <a href="{{ route('dashboard.admin.bookings.index') }}" class="btn btn-secondary">Reset</a>
                @endif
            </form>
        </div>

        <div class="project-ongoing-box mt-3">
            <table class="table table-striped table-bordered table-notification-list">
                <thead>
                    <tr>
                        <th>Passenger</th>
                        <th>Driver</th>
                        <th>Pickup</th>
                        <th>Destination</th>
                        <th>Service</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $booking)
                        <tr>
                            <td>{{ $booking->passenger?->name ?? 'N/A' }}</td>
                            <td>{{ $booking->driver->name ?? 'N/A' }}</td>
                          <td>{{ $booking->pickup_location ?? $booking->ride?->pickup_location ?? $booking->request?->pickup_location ?? '-' }}</td>
                         <td>{{ $booking->destination ?? $booking->ride?->destination ?? $booking->request?->destination ?? '-' }}</td>
                         <td>
                            @if (!empty($booking->services_details) && count($booking->services_details) > 0)
                                @foreach ($booking->services_details as $service)
                                    <span class="badge bg-info">{{ $service->service_name ?? 'N/A' }}</span>
                                @endforeach
                            @else
                                <span class="">N/A</span>
                            @endif
                         </td>
                            <!-- <td><span class="badge bg-info">{{ ucfirst($booking->status) }}</span></td> -->
                           <td>
                                @if ($booking->type == 0)
                                    <span class="badge bg-success">Ride</span>
                                @elseif ($booking->type == 1)
                                    <span class="badge bg-warning text-dark">Parcel</span>
                                @else
                                    <span class="badge bg-secondary">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($booking->status === 'cancelled')
                                    <span class="badge bg-danger">Cancelled</span>
                                @elseif($booking->status === 'confirmed' && $booking->active_status == 0)
                                    <span class="badge bg-warning text-dark">Confirmed</span>
                                @elseif($booking->active_status == 1)
                                    <span class="badge bg-info">Active</span>
                                @elseif($booking->active_status == 2)
                                    <span class="badge bg-success">Completed</span>
                                @else
                                    <span class="badge bg-secondary">Pending</span>
                                @endif
                            </td>

                           <td>{{ \Carbon\Carbon::parse($booking->ride_date)->format('d-M-Y') }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <a href="javascript:;" 
                                    class="action-btn me-3 view-booking-details"
                                    data-user='@json($booking)'
                                    data-bs-toggle="modal"
                                    data-bs-target="#userModal">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    <button  class="dropdown-item delete-btn-design delete-booking-btn d-flex justify-content-center" data-booking-id="{{ $booking->id }}" type="button" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
                                        <i class="fa fa-regular fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center">No bookings found</td></tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Pagination --}}
            @if ($bookings->lastPage() > 1)
                <nav class="pt-3">
                    <ul class="pagination">
                        {{-- Previous --}}
                        @if ($bookings->onFirstPage())
                            <li class="page-item disabled"><span class="page-link">Previous</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $bookings->previousPageUrl() }}">Previous</a></li>
                        @endif

                        {{-- Pages --}}
                        @for ($i = 1; $i <= $bookings->lastPage(); $i++)
                            <li class="page-item {{ $bookings->currentPage() == $i ? 'active' : '' }}">
                                <a class="page-link" href="{{ $bookings->url($i) }}">{{ $i }}</a>
                            </li>
                        @endfor

                        {{-- Next --}}
                        @if ($bookings->hasMorePages())
                            <li class="page-item"><a class="page-link" href="{{ $bookings->nextPageUrl() }}">Next</a></li>
                        @else
                            <li class="page-item disabled"><span class="page-link">Next</span></li>
                        @endif
                    </ul>
                </nav>
            @endif
        </div>
    </section>
</div>

{{-- View Booking Modal --}}

    <div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="userModalLabel" style="font-weight:800;color:#86c349;font-size:24px;">
            Driver Details
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
            <table class="table table-striped table-bordered table-notification-list">
                <tbody>
                    <!-- <tr><th>Booking ID</th><td id="modal-id">-</td></tr> -->
                        <tr><th>Passenger Name</th><td id="modal-passenger">-</td></tr>
                        <tr><th>Driver Name</th><td id="modal-driver">-</td></tr>
                        <tr><th>Pickup Location</th><td id="modal-pickup">-</td></tr>
                        <tr><th>Destination</th><td id="modal-destination">-</td></tr>
                        <tr><th>Service</th><td id="modal-service">-</td></tr>
                        <tr><th>Type</th><td id="modal-type">-</td></tr>
                        <tr><th>Status</th><td id="modal-status">-</td></tr>
                        <tr><th>Date</th><td id="modal-date">-</td></tr>
                </tbody>
            </table>
        </div>

        <div class="modal-footer">
            <button type="button" class="delete-confirmation-popup-btn btn w-auto px-3" data-bs-dismiss="modal">Close</button>
        </div>
        </div>
    </div>
    </div>

   <!-- delete-confirmation-popup -->
        <section class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h2 class="delete-confirmation-popup-title delete-user-confirmation-popup-title" id="staticBackdropLabel">Are you sure?</h2>
                        <button type="button" class="btn-close cancel-popup-btnbox" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body delete-confirmation-popup-body ">
                            <p class="delete-confirmation-popup-text delete-user-confirmation-popup-text">Do you really want to delete this booking?</p>
                    </div>
                    <div class="modal-footer border-0 delete-confirmation-popup-footer delete-user-confirmation-popup-footer">
                        <button class="delete-confirmation-popup-btn btn" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                        <button class="delete-confirmation-popup-btn btn delete-confirmation-popup-delete-btn delete-booking-confirmation-popup-delete-btn" data-booking-id="">Delete</button>
                    </div>
                </div>
            </div>
        </section>
    <!-- delete-confirmation-popup-->

{{-- AJAX --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
const csrfToken = "{{ csrf_token() }}";
const deleteBookingUrl = "{{ route('dashboard.admin.deleteBooking') }}";

</script>
@endsection
