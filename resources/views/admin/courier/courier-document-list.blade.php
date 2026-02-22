@extends('admin.layouts.app')

@section('content')
<div class="main-box-content main-space-box">
    <section class="project-doorbox">

        <div class="ai-training-data-wrapper d-flex align-items-baseline justify-content-between">
            <div class="heading-content-box">
                <h2>Courier Document Requests</h2>

                <form method="GET" action="{{ route('dashboard.admin.courierDocuments') }}" class="d-flex gap-2 mb-3">

                    <input type="text" name="search" class="form-control"
                        placeholder="Search by name or phone"
                        value="{{ request('search') }}">

                    <!-- <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status')=='pending'?'selected':'' }}>Pending</option>
                        <option value="approved" {{ request('status')=='approved'?'selected':'' }}>Approved</option>
                        <option value="rejected" {{ request('status')=='rejected'?'selected':'' }}>Rejected</option>
                        <option value="not_submitted" {{ request('status')=='not_submitted'?'selected':'' }}>Not Submitted</option>
                    </select> -->

                    <button type="submit" class="btn btn-success">Filter</button>

                    @if(request()->hasAny(['search','status']))
                        <a href="{{ route('dashboard.admin.courierDocuments') }}" class="btn btn-secondary">Reset</a>
                    @endif
                </form>

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
            </div>
        </div>

        <div class="project-ongoing-box">

            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Passport</th>
                        <th>License</th>
                        <th>Selfie</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                @forelse($users as $user)

                    @php
                        $passports = json_decode($user->passport_images, true) ?? [];
                        $licenses = json_decode($user->license_images, true) ?? [];

                        $hasPassport = !empty($passports);
                        $hasLicense  = !empty($licenses);
                        $hasSelfie   = !empty($user->courier_selfie);

                        $documentsUploaded = $hasPassport && $hasLicense && $hasSelfie;
                    @endphp

                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->phone_number ?? '-' }}</td>

                        {{-- Passport --}}
                        <td>
                            @if($hasPassport)
                                @foreach($passports as $img)
                                    <a href="{{ asset('assets/courier/passport/'.$img) }}" target="_blank">
                                        <img src="{{ asset('assets/courier/passport/'.$img) }}" width="60">
                                    </a>
                                @endforeach
                            @else
                                <span class="text-danger">Not Uploaded</span>
                            @endif
                        </td>

                        {{-- License --}}
                        <td>
                            @if($hasLicense)
                                @foreach($licenses as $img)
                                    <a href="{{ asset('assets/courier/license/'.$img) }}" target="_blank">
                                        <img src="{{ asset('assets/courier/license/'.$img) }}" width="60">
                                    </a>
                                @endforeach
                            @else
                                <span class="text-danger">Not Uploaded</span>
                            @endif
                        </td>

                        {{-- Selfie --}}
                        <td>
                            @if($hasSelfie)
                                <a href="{{ asset('assets/courier/selfie/'.$user->courier_selfie) }}" target="_blank">
                                    <img src="{{ asset('assets/courier/selfie/'.$user->courier_selfie) }}" width="60">
                                </a>
                            @else
                                <span class="text-danger">Not Uploaded</span>
                            @endif
                        </td>

                        {{-- Status --}}
                        <td>
                            @if($user->courier_doc_status == 'pending')
                                <span class="badge bg-warning">Pending</span>
                            @elseif($user->courier_doc_status == 'approved')
                                <span class="badge bg-success">Approved</span>
                            @elseif($user->courier_doc_status == 'rejected')
                                <span class="badge bg-danger">Rejected</span>
                            @elseif($user->courier_doc_status == 'not_submitted')
                                <span class="badge bg-secondary">Not Submitted</span>
                            @else
                                <span class="badge bg-dark">Unknown</span>
                            @endif
                        </td>

                        {{-- Action --}}
                        <td>

                            @if(!$documentsUploaded)
                                <span class="text-muted">Documents Not Complete</span>

                            @else

                                @if($user->courier_doc_status == 'approved')
                                    <form method="POST"
                                        action="{{ route('dashboard.admin.rejectCourier',$user->id) }}"
                                        style="display:inline;">
                                        @csrf
                                        <button class="btn btn-danger btn-sm">Reject</button>
                                    </form>

                                @elseif($user->courier_doc_status == 'rejected')
                                    <form method="POST"
                                        action="{{ route('dashboard.admin.approveCourier',$user->id) }}"
                                        style="display:inline;">
                                        @csrf
                                        <button class="btn btn-success btn-sm">Verify</button>
                                    </form>

                                @else
                                    <form method="POST"
                                        action="{{ route('dashboard.admin.approveCourier',$user->id) }}"
                                        style="display:inline;">
                                        @csrf
                                        <button class="btn btn-success btn-sm">Verify</button>
                                    </form>

                                    <form method="POST"
                                        action="{{ route('dashboard.admin.rejectCourier',$user->id) }}"
                                        style="display:inline;">
                                        @csrf
                                        <button class="btn btn-danger btn-sm">Reject</button>
                                    </form>
                                @endif

                            @endif

                        </td>

                    </tr>

                @empty
                    <tr>
                        <td colspan="7" class="text-center">No Data Found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>

            {{ $users->links() }}

        </div>
    </section>
</div>
@endsection
