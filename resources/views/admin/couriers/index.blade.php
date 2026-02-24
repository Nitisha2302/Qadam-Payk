@extends('admin.layouts.app')

@section('content')
<div class="main-box-content main-space-box ">
    <section class="project-doorbox">

        <div class="ai-training-data-wrapper d-flex align-items-baseline justify-content-between">
            <div class="heading-content-box">
                <h2>All Courier Requests</h2>

                <form method="GET" action="{{ route('dashboard.admin.couriers.index') }}" class="d-flex gap-2 mb-3">

                    <input type="text"
                           name="search"
                           class="form-control"
                           placeholder="Search by pickup / drop / user name"
                           value="{{ request('search') }}">

                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status')=='pending'?'selected':'' }}>Pending</option>
                        <option value="accepted" {{ request('status')=='accepted'?'selected':'' }}>Accepted</option>
                        <option value="in_transit" {{ request('status')=='in_transit'?'selected':'' }}>In Transit</option>
                        <option value="completed" {{ request('status')=='completed'?'selected':'' }}>Completed</option>
                    </select>

                    <button type="submit" class="btn btn-success">Filter</button>

                    @if(request()->hasAny(['search','status']))
                        <a href="{{ route('dashboard.admin.couriers.index') }}" class="btn btn-secondary">Reset</a>
                    @endif

                </form>
            </div>
        </div>

        <div class="project-ongoing-box">

            <table class="table table-striped table-bordered table-notification-list">
                <thead>
                    <tr>
                        
                        <th>Created By</th>
                        <th>Driver</th>
                        <th>Pickup</th>
                        <th>Drop</th>
                        <th>Suggested Price</th>
                        <th>Final Price</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($couriers as $courier)

                    @php
                        $acceptedInterest = $courier->interests
                            ->where('driver_id', $courier->accepted_driver_id)
                            ->first();
                    @endphp

                    <tr>
                        

                        <td>
                            {{ $courier->sender->name ?? '-' }} <br>
                            <small>{{ $courier->sender->phone_number ?? '-' }}</small>
                        </td>

                        <td>
                            @if($courier->acceptedDriver)
                                {{ $courier->acceptedDriver->name }} <br>
                                <small>{{ $courier->acceptedDriver->phone_number }}</small>
                            @else
                                <span class="text-danger">Not Assigned</span>
                            @endif
                        </td>

                        <td>{{ $courier->pickup_location }}</td>
                        <td>{{ $courier->drop_location }}</td>

                        <td>{{ $courier->suggested_price ?? '-' }}</td>

                        <td>
                            {{ $acceptedInterest->driver_price ?? '-' }}
                        </td>

                        <td>
                            <span class="badge bg-info">
                                {{ ucfirst(str_replace('_',' ',$courier->status)) }}
                            </span>
                        </td>

                        <td>{{ $courier->created_at->format('d M Y') }}</td>

                        <td>
                            <a href="{{ route('dashboard.admin.couriers.show',$courier->id) }}"
                               class="action-btn me-2">
                                <i class="fa fa-eye"></i>
                            </a>
                        </td>
                    </tr>

                    @empty
                        <tr>
                            <td colspan="10" class="text-center">NO Data Found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Pagination --}}
            @if ($couriers->lastPage() > 1)
                <nav class="pt-3">
                    <ul class="pagination">

                        @if ($couriers->onFirstPage())
                            <li class="page-item disabled"><span class="page-link">Previous</span></li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $couriers->previousPageUrl() }}">Previous</a>
                            </li>
                        @endif

                        @for ($i = 1; $i <= $couriers->lastPage(); $i++)
                            <li class="page-item {{ $couriers->currentPage() == $i ? 'active' : '' }}">
                                <a class="page-link" href="{{ $couriers->url($i) }}">{{ $i }}</a>
                            </li>
                        @endfor

                        @if ($couriers->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $couriers->nextPageUrl() }}">Next</a>
                            </li>
                        @else
                            <li class="page-item disabled"><span class="page-link">Next</span></li>
                        @endif

                    </ul>
                </nav>
            @endif

        </div>

    </section>
</div>
@endsection
