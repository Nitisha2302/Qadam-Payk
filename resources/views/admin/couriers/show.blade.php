@extends('admin.layouts.app')

@section('content')
<div class="main-box-content main-space-box ">
    <section class="project-doorbox">

        <div class="ai-training-data-wrapper d-flex align-items-baseline justify-content-between">
            <div class="heading-content-box">
                <h2>Courier Detail </h2>
                <a href="{{ route('dashboard.admin.couriers.index') }}" class="btn btn-secondary mb-3">Back</a>
            </div>
        </div>

        <div class="project-ongoing-box">

            <table class="table table-striped table-bordered table-notification-list">
                <tbody>

                    <tr>
                        <th>Pickup Location</th>
                        <td>{{ $courier->pickup_location }}</td>
                    </tr>

                    <tr>
                        <th>Drop Location</th>
                        <td>{{ $courier->drop_location }}</td>
                    </tr>

                    <tr>
                        <th>Distance</th>
                        <td>{{ $courier->distance }}</td>
                    </tr>

                    <tr>
                        <th>Trip Type</th>
                        <td>{{ ucfirst($courier->trip_type) }}</td>
                    </tr>

                    <tr>
                        <th>Package Size</th>
                        <td>{{ ucfirst($courier->package_size) }}</td>
                    </tr>

                    <tr>
                        <th>Status</th>
                        <td>
                            <span class="badge bg-info">
                                {{ ucfirst(str_replace('_',' ',$courier->status)) }}
                            </span>
                        </td>
                    </tr>

                    <tr>
                        <th>Suggested Price</th>
                        <td>{{ $courier->suggested_price }}</td>
                    </tr>

                    <tr>
                        <th>Payment Method</th>
                        <td>{{ ucfirst($courier->payment_method) }}</td>
                    </tr>

                    <tr>
                        <th>Paid By</th>
                        <td>{{ ucfirst($courier->paid_by) }}</td>
                    </tr>

                </tbody>
            </table>

            <br>

            <h5>Created By</h5>
            <table class="table table-bordered">
                <tr>
                    <th>Name</th>
                    <td>{{ $courier->sender->name ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Phone</th>
                    <td>{{ $courier->sender->phone_number ?? '-' }}</td>
                </tr>
            </table>

            <br>

            <h5>Accepted Driver</h5>
            @if($courier->acceptedDriver)
                <table class="table table-bordered">
                    <tr>
                        <th>Name</th>
                        <td>{{ $courier->acceptedDriver->name }}</td>
                    </tr>
                    <tr>
                        <th>Phone</th>
                        <td>{{ $courier->acceptedDriver->phone_number }}</td>
                    </tr>
                    <tr>
                        <th>Driver Price</th>
                        <td>
                            {{ optional($courier->interests
                                ->where('driver_id',$courier->accepted_driver_id)
                                ->first())->driver_price }}
                        </td>
                    </tr>
                </table>
            @else
                <p class="text-danger">No driver assigned yet.</p>
            @endif

        </div>

    </section>
</div>
@endsection
