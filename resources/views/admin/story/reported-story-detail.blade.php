@extends('admin.layouts.app')

@section('content')
<div class="main-box-content main-space-box">
    <section class="project-doorbox">

        <div class="heading-content-box mb-4">
            <h2>Reported Story Details</h2>
            <a href="{{ route('dashboard.admin.reported-stories') }}"
               class="btn btn-secondary">← Back</a>
        </div>

        {{-- Story Info --}}
        <div class="card mb-4">
            <div class="card-header">
                <strong>Story Information</strong>
            </div>
            <div class="card-body row">

                <div class="col-md-4">
                    @if($story->type === 'photo')
                        <img src="{{ asset('assets/story_media/'.$story->media) }}"
                             class="img-fluid rounded">
                    @else
                        <video controls class="w-100">
                            <source src="{{ asset('assets/story_media/'.$story->media) }}">
                        </video>
                    @endif
                </div>

                <div class="col-md-8">
                    <p><strong>Posted By:</strong> {{ $story->user->name ?? 'N/A' }}</p>
                    <p><strong>Type:</strong> {{ ucfirst($story->type) }}</p>
                    <p><strong>City:</strong> {{ $story->city ?? 'N/A' }}</p>
                    <p><strong>Route:</strong> {{ $story->route ?? 'N/A' }}</p>
                    <p><strong>Description:</strong> {{ $story->description ?? '-' }}</p>
                    <p>
                        <strong>Total Reports:</strong>
                        <span class="badge bg-danger">{{ $story->reports_count }}</span>
                    </p>
                </div>
            </div>
        </div>

        {{-- Reports List --}}
        <div class="card">
            <div class="card-header">
                <strong>Reports</strong>
            </div>

            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Reason</th>
                            <th>Reported At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($story->reports as $report)
                            <tr>
                                <td>{{ $report->user->name ?? 'N/A' }}</td>
                                <td>{{ $report->reason ?? '-' }}</td>
                                <td>{{ $report->created_at->format('d M Y, h:i A') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center">
                                    No reports found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>


    </section>
</div>
@endsection
