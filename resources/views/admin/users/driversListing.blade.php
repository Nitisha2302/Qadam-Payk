@extends('admin.layouts.app')

@section('content')
<div class="main-box-content main-space-box ">
    <section class="project-doorbox">
       <div class="ai-training-data-wrapper d-flex align-items-baseline justify-content-between">
           <div class="heading-content-box">
                <h2>All Drivers</h2>
                <div id="successMessage" class="alert alert-success d-none"></div>
                @if (session('success'))
                    <div class="alert alert-success" role="alert" id="success-message">
                        {{ session('success') }}
                    </div>
                @endif
                
            </div>
 
        </div> 
    
        <div class="project-ongoing-box">
        {{-- Users Listing --}}
        <table class="table table-striped table-bordered table-notification-list">
            <thead>
                <tr>
                    <th>Profile Image</th>
                    <th>Name</th>
                    <th>Phone Number</th>
                    <th>Goverment Id</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <!-- @php
                            $imagePath = 'assets/profile_image/' . $user->image;
                            $imageUrl = file_exists(public_path($imagePath)) 
                                ? asset($imagePath) 
                                : asset('assets/admin/images/default_user_profile.jpg');
                        @endphp -->
                        <td>
                            <!-- <a href="{{ $imageUrl }}" target="_blank">
                                        <img class="listing-img" 
                                            src="{{ $imageUrl }}" 
                                            alt="gov-id" width="80">
                            </a> -->
                            <!-- <a href="{{ asset('assets/profile_image/' . $user->image) }}" target="_blank">
                                        <img class="listing-img" 
                                            src="{{ file_exists(public_path('assets/profile_image/' . $user->image)) ? asset('assets/profile_image/' . $user->image) : asset('assets/admin/images/default_user_profile.jpg') }}" 
                                            alt="gov-id" width="80">
                            </a> -->
                            <a href="{{ asset('assets/profile_image/' . $user->image) }}" target="_blank"></a>
                                <img class="listing-img" 
                                    src="{{ $user->image ? asset('assets/profile_image/' . $user->image) : asset('assets/admin/images/default_user_profile.jpg') }}" 
                                    alt="user-img" width="80">
                            </a>
                        </td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->phone_number ?? '-' }}</td>
                        <td>
                           @php
                                $govIds = [];
                                if ($user->government_id) {
                                    // Remove extra quotes if present
                                    $cleanJson = trim($user->government_id, '"'); 
                                    $govIds = json_decode($cleanJson, true);
                                    // Fallback if still null
                                    if (!is_array($govIds)) {
                                        $govIds = [];
                                    }
                                }
                            @endphp

                            @if(!empty($govIds))
                                @foreach($govIds as $idImage)
                                    <a href="{{ asset('assets/identity/' . $idImage) }}" target="_blank">
                                        <img class="listing-img" 
                                            src="{{ file_exists(public_path('assets/identity/' . $idImage)) ? asset('assets/identity/' . $idImage) : asset('assets/admin/images/default_user_profile.jpg') }}" 
                                            alt="gov-id" width="80">
                                    </a>
                                @endforeach
                            @else
                                <img class="listing-img" src="{{ asset('assets/admin/images/default_user_profile.jpg') }}" alt="" width="80">
                            @endif
                        </td>
                        <td>
                            @if($user->id_verified == 0)
                                <span class="badge bg-warning">Pending</span>
                            @elseif($user->id_verified == 1)
                                <span class="badge bg-success">Verified</span>
                            @elseif($user->id_verified == 2)
                                <span class="badge bg-danger">Rejected</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <a href="javascript:;" 
                                class="action-btn me-3 view-user-details"
                                data-user='@json($user)'
                                data-bs-toggle="modal"
                                data-bs-target="#userModal">
                                    <i class="fa fa-eye"></i>
                                </a>
                                    @if($user->id_verified == 0)
                                        <button class="btn btn-success btn-sm verify-user-btn" data-user-id="{{ $user->id }}">Verify</button>
                                        <button class="btn btn-danger btn-sm reject-user-btn" data-user-id="{{ $user->id }}">Reject</button>
                                    @endif
                                <button  class="dropdown-item delete-btn-design delete-user-btn d-flex justify-content-center" data-user-id="{{ $user->id }}" type="button" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
                                    <i class="fa fa-regular fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">NO Data Found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination --}}
        @if ($users->lastPage() > 1)
            <nav class="pt-3">
                <ul class="pagination">
                    {{-- Previous --}}
                    @if ($users->onFirstPage())
                        <li class="page-item disabled"><span class="page-link">Previous</span></li>
                    @else
                        <li class="page-item"><a class="page-link" href="{{ $users->previousPageUrl() }}">Previous</a></li>
                    @endif

                    {{-- Pages --}}
                    @for ($i = 1; $i <= $users->lastPage(); $i++)
                        <li class="page-item {{ $users->currentPage() == $i ? 'active' : '' }}">
                            <a class="page-link" href="{{ $users->url($i) }}">{{ $i }}</a>
                        </li>
                    @endfor

                    {{-- Next --}}
                    @if ($users->hasMorePages())
                        <li class="page-item"><a class="page-link" href="{{ $users->nextPageUrl() }}">Next</a></li>
                    @else
                        <li class="page-item disabled"><span class="page-link">Next</span></li>
                    @endif
                </ul>
            </nav>
        @endif

    </section>  
</div>

<!-- user Detail Modal -->
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
            <tr><th>Name</th><td id="modal-name">-</td></tr>
            <tr><th>Phone NUmber</th><td id="modal-phone_number">-</td></tr>
          </tbody>
        </table>
      </div>

      <div class="modal-footer">
        <button type="button" class="delete-confirmation-popup-btn btn w-auto px-3" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

    <!-- Modal -->
        <section class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h2 class="delete-confirmation-popup-title delete-user-confirmation-popup-title" id="staticBackdropLabel">Are you sure?</h2>
                        <button type="button" class="btn-close cancel-popup-btnbox" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body delete-confirmation-popup-body ">
                            <p class="delete-confirmation-popup-text delete-user-confirmation-popup-text">Do you really want to delete this driver?</p>
                    </div>
                    <div class="modal-footer border-0 delete-confirmation-popup-footer delete-user-confirmation-popup-footer">
                        <button class="delete-confirmation-popup-btn btn" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                        <button class="delete-confirmation-popup-btn btn delete-confirmation-popup-delete-btn delete-user-confirmation-popup-delete-btn" data-user-id="">Delete</button>
                    </div>
                </div>
            </div>
        </section>
    <!-- delete-confirmation-popup-->


<!-- delete-confirmation-popup-->

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    const csrfToken = "{{ csrf_token() }}";
    const deleteUserUrl = "{{ route('dashboard.admin.deleteUser') }}";
    const verifyUserUrl = "{{ url('dashboard/admin/verify-user') }}";
    const rejectUserUrl = "{{ url('dashboard/admin/reject-user') }}";

</script>



@endsection
