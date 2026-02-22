@extends('admin.layouts.app')

@section('content')
<div class="main-box-content main-space-box ">
    <section class="project-doorbox">

        <div class="ai-training-data-wrapper d-flex align-items-baseline justify-content-between">
            <div class="heading-content-box">
                <h2>All Announcements</h2>

                {{-- FILTER FORM --}}
                <!-- <form method="GET" action="{{ route('dashboard.admin.announcement-listing') }}" class="d-flex gap-2 mb-3">
                    <input type="text" name="search" class="form-control"
                        placeholder="Search by title or description" value="{{ request('search') }}">

                    <button type="submit" class="btn btn-success">Filter</button>

                    @if(request()->has('search'))
                        <a href="{{ route('dashboard.admin.announcement-listing') }}" class="btn btn-secondary">Reset</a>
                    @endif
                </form> -->

                @if (session('success'))
                    <div class="alert alert-success" id="success-message">
                        {{ session('success') }}
                    </div>
                @endif
            </div>

            <a href="{{ route('dashboard.admin.notifications.send') }}" class="btn btn-green">
                Add Announcement
            </a>
        </div>

        {{-- TABLE LISTING --}}
        <div class="project-ongoing-box">
            <table class="table table-striped table-bordered table-notification-list">
                <thead>
                    <tr>
                        <th width="15%">Image</th>
                        <th width="15%">Type</th>
                        <th width="20%">Title</th>
                        <th>Description</th>
                        <th width="15%">Date</th>
                        <!-- <th width="10%">Action</th> -->
                    </tr>
                </thead>

                <tbody>
                    @forelse ($announcements as $a)
                        <tr>
                            <td>
                                <img src="{{ $a->image ? asset('assets/banner/'.$a->image) : asset('assets/default/no-image.png') }}"
                                    class="listing-img" width="80">
                            </td>
                            <td>
                                <div class="scrollable-td">
                                    @if ($a->type == 1)
                                        <span class="badge bg-primary">Announcement</span>
                                    @elseif ($a->type == 2)
                                        <span class="badge bg-info">News</span>
                                    @else
                                        <span class="badge bg-secondary">Unknown</span>
                                    @endif
                                </div>
                            </td>

                            <td><div class="scrollable-td">{{ $a->title }}</div></td>

                            <td><div class="scrollable-td">{{ $a->description }}</div></td>

                            <td>{{ $a->created_at->format('d M Y') }}</td>

                            <!-- <td>
                                <div class="d-flex align-items-center gap-2">

                                    <button class="dropdown-item delete-btn-design delete-announcement-btn"
                                        data-id="{{ $a->id }}" type="button" data-bs-toggle="modal"
                                        data-bs-target="#deleteAnnouncementModal">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            </td> -->
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center">No announcements found.</td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

        {{-- PAGINATION --}}
        @if ($announcements->lastPage() > 1)
            <nav class="pt-3" aria-label="Page navigation">
                <ul class="pagination">
                    @if ($announcements->onFirstPage())
                        <li class="page-item disabled"><span class="page-link text-dark">Previous</span></li>
                    @else
                        <li class="page-item"><a class="page-link text-dark" href="{{ $announcements->previousPageUrl() }}">Previous</a></li>
                    @endif

                    @for ($i = 1; $i <= $announcements->lastPage(); $i++)
                        <li class="page-item {{ $announcements->currentPage() == $i ? 'active' : '' }}">
                            <a class="page-link" href="{{ $announcements->url($i) }}">{{ $i }}</a>
                        </li>
                    @endfor

                    @if ($announcements->hasMorePages())
                        <li class="page-item"><a class="page-link text-dark" href="{{ $announcements->nextPageUrl() }}">Next</a></li>
                    @else
                        <li class="page-item disabled"><span class="page-link text-dark">Next</span></li>
                    @endif
                </ul>
            </nav>
        @endif

    </section>  
</div>

{{-- DELETE MODAL --}}
<div class="modal fade" id="deleteAnnouncementModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h2 class="modal-title">Are you sure?</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <p>Do you really want to delete this Announcement?</p>
            </div>

            <div class="modal-footer border-0">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-danger delete-confirm-btn" data-id="">Delete</button>
            </div>
        </div>
    </div>
</div>

@endsection

<script>
$(document).ready(function () {
    // Pass ID to delete modal
    $('.delete-announcement-btn').click(function () {
        let id = $(this).data('id');
        $('.delete-confirm-btn').attr('data-id', id);
    });

    // AJAX Delete
    $('.delete-confirm-btn').click(function () {
        let id = $(this).data('id');

        $.ajax({
            url: "{{ url('dashboard/admin/delete-announcement') }}/" + id,
            type: "DELETE",
            data: { _token: "{{ csrf_token() }}" },
            success: function (res) {
                location.reload();
            }
        });
    });
});
</script>
