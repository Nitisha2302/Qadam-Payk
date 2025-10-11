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
                <input type="text" name="search" class="form-control" placeholder="Search by user, driver, location" value="{{ request('search') }}">
                <select name="status" class="form-control">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                <button type="submit" class="btn btn-success">Filter</button>
            </form>
        </div>

        <div class="project-ongoing-box mt-3">
            <table class="table table-striped table-bordered table-notification-list">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Passenger</th>
                        <th>Driver</th>
                        <th>Pickup</th>
                        <th>Destination</th>
                        <th>Service</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                  
                    @forelse($bookings as $booking)
                        <tr>
                            <td>{{ $booking->id }}</td>
                            <td>{{ $booking->user->name ?? 'N/A' }}</td>
                            <td>{{ $booking->driver->name ?? 'N/A' }}</td>
                            <td>{{ $booking->pickup_location ?? $booking->ride->pickup_location ?? '-' }}</td>
                             <td>{{ $booking->destination ?? $booking->ride->destination ?? '-' }}</td>
                           <td>
                                @foreach ($booking->services_details as $service)
                                    <span class="badge bg-info">{{ $service->service_name }}</span>
                                @endforeach
                            </td>
                            <!-- <td><span class="badge bg-info">{{ ucfirst($booking->status) }}</span></td> -->

                            <td>
                                @if ($booking->status == 'pending')
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @elseif ($booking->status == 'confirmed')
                                    <span class="badge bg-success">Confirmed</span>
                                @elseif ($booking->status == 'cancelled')
                                    <span class="badge bg-danger">Cancelled</span>
                                @elseif ($booking->status == 'completed')
                                    <span class="badge bg-primary">Completed</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($booking->status ?? 'Unknown') }}</span>
                                @endif
                            </td>
                            <td>{{ $booking->created_at->format('d M Y') }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <!-- <button class="btn btn-sm btn-primary view-booking" data-id="{{ $booking->id }}">
                                        <i class="fa fa-eye"></i>
                                    </button> -->
                                    <button class="btn btn-sm btn-danger delete-booking" data-id="{{ $booking->id }}">
                                        <i class="fa fa-trash"></i>
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
<div class="modal fade" id="bookingModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" style="font-weight:800;color:#86c349;font-size:24px;">Booking Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered">
          <tbody>
            <tr><th>User</th><td id="b-user">-</td></tr>
            <tr><th>Driver</th><td id="b-driver">-</td></tr>
            <tr><th>Pickup</th><td id="b-pickup">-</td></tr>
            <tr><th>Destination</th><td id="b-destination">-</td></tr>
            <tr><th>Service</th><td id="b-service">-</td></tr>
            <tr><th>Status</th><td id="b-status">-</td></tr>
            <tr><th>Created At</th><td id="b-created">-</td></tr>
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

{{-- AJAX --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
const csrfToken = "{{ csrf_token() }}";
const showUrl = "{{ route('dashboard.admin.bookings.show', ':id') }}";
const deleteUrl = "{{ route('dashboard.admin.bookings.destroy', ':id') }}";

$(document).on('click', '.view-booking', function() {
    const id = $(this).data('id');
    $.get(showUrl.replace(':id', id), function(res) {
        $('#b-user').text(res.user?.name ?? 'N/A');
        $('#b-driver').text(res.driver?.name ?? 'N/A');
        $('#b-pickup').text(res.pickup_location);
        $('#b-destination').text(res.destination);
        $('#b-service').text(res.service?.name ?? 'N/A');
        $('#b-status').text(res.status);
        $('#b-created').text(res.created_at);
        $('#bookingModal').modal('show');
    });
});

$(document).on('click', '.delete-booking', function() {
    if (!confirm('Are you sure you want to delete this booking?')) return;
    const id = $(this).data('id');
    $.ajax({
        url: deleteUrl.replace(':id', id),
        type: 'DELETE',
        data: {_token: csrfToken},
        success: function(res) {
            if (res.success) location.reload();
        }
    });
});
</script>
@endsection
