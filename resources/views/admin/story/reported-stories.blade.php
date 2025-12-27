@extends('admin.layouts.app')

@section('content')
<div class="main-box-content main-space-box">
    <section class="project-doorbox">
        <div class="ai-training-data-wrapper d-flex align-items-baseline justify-content-between">
            <div class="heading-content-box">
                <h2>Reported Stories</h2>

                {{-- Search --}}
                <form method="GET" action="{{ route('dashboard.admin.reported-stories') }}" class="d-flex gap-2 mb-3">
                    <input type="text" name="search" class="form-control" placeholder="Search by user / city" value="{{ request('search') }}">
                    <button type="submit" class="btn btn-success">Filter</button>
                    @if(request()->has('search'))
                        <a href="{{ route('dashboard.admin.reported-stories') }}" class="btn btn-secondary">Reset</a>
                    @endif
                </form>

                <div id="notificationMessage" class="alert d-none"></div>

            </div>
        </div>

        <div class="project-ongoing-box">
            <table class="table table-striped table-bordered table-notification-list">
                <thead>
                    <tr>
                        <th>Media</th>
                        <th>User</th>
                        <th>Type</th>
                        <th>City</th>
                        <th>Reports</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stories as $story)
                        <tr id="story-row-{{ $story->id }}">
                            <td>
                                @if($story->type === 'photo')
                                    <img class="listing-img" src="{{ asset('assets/story_media/'.$story->media) }}" width="80">
                                @else
                                    <video width="80" controls>
                                        <source src="{{ asset('assets/story_media/'.$story->media) }}">
                                    </video>
                                @endif
                            </td>
                            <td>{{ $story->user->name ?? 'N/A' }}</td>
                            <td>{{ ucfirst($story->type) }}</td>
                            <td>{{ $story->city ?? 'N/A' }}</td>
                            <td><span class="badge bg-danger">{{ $story->reports_count }}</span></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <a href="{{ route('dashboard.admin.reported-story-detail', $story->id) }}" class="action-btn">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                   <button class="btn btn-danger delete-story-btn" data-story-id="{{ $story->id }}" data-bs-toggle="modal" data-bs-target="#deleteStoryModal">
                                        <i class="fa fa-trash"></i>
                                    </button>

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No reported stories found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($stories->lastPage() > 1)
            <nav class="pt-3">
                <ul class="pagination">
                    @if ($stories->onFirstPage())
                        <li class="page-item disabled"><span class="page-link">Previous</span></li>
                    @else
                        <li class="page-item"><a class="page-link" href="{{ $stories->previousPageUrl() }}">Previous</a></li>
                    @endif

                    @for ($i = 1; $i <= $stories->lastPage(); $i++)
                        <li class="page-item {{ $stories->currentPage() == $i ? 'active' : '' }}">
                            <a class="page-link" href="{{ $stories->url($i) }}">{{ $i }}</a>
                        </li>
                    @endfor

                    @if ($stories->hasMorePages())
                        <li class="page-item"><a class="page-link" href="{{ $stories->nextPageUrl() }}">Next</a></li>
                    @else
                        <li class="page-item disabled"><span class="page-link">Next</span></li>
                    @endif
                </ul>
            </nav>
        @endif
    </section>
</div>

{{-- DELETE MODAL --}}
<section class="modal fade" id="deleteStoryModal" tabindex="-1" aria-labelledby="deleteStoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h2 class="modal-title">Are you sure?</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Do you really want to delete this story?</p>
            </div>
            <div class="modal-footer border-0">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-danger" id="confirmDeleteStory" data-story-id="">Delete</button>
            </div>
        </div>
    </div>
</section>
<script>
    const csrfToken = "{{ csrf_token() }}";
    const deleteStoryUrl = "{{ url('dashboard/admin/stories') }}";
    console.log(deleteStoryUrl);
    $(document).ready(function () {

        // Set story id to modal button
        $(document).on('click', '.delete-story-btn', function () {
            let storyId = $(this).data('story-id');
            $('#confirmDeleteStory').data('story-id', storyId);
        });

        // Confirm delete
        $(document).on('click', '#confirmDeleteStory', function () {
            let storyId = $(this).data('story-id');
            if (!storyId) return;

            $.ajax({
                url: deleteStoryUrl + '/' + storyId,
                type: 'DELETE',
                data: {_token: csrfToken},
                success: function(response) {
                    $('#deleteStoryModal').modal('hide');
                    if(response.status) {
                        $('#story-row-' + storyId).fadeOut(500, function(){ $(this).remove(); });
                    } else {
                        $('#notificationMessage').removeClass('d-none alert-success').addClass('alert-danger').text(response.message || 'Could not delete story.');
                        setTimeout(()=> { $('#notificationMessage').addClass('d-none').text(''); }, 4000);
                    }
                },
                error: function() {
                    $('#notificationMessage').removeClass('d-none alert-success').addClass('alert-danger').text('Error deleting story.');
                    setTimeout(()=> { $('#notificationMessage').addClass('d-none').text(''); }, 4000);
                }
            });
        });

    });
</script>
@endsection
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
