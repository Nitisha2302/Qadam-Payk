@extends('admin.layouts.app')

@section('content')
<div class="main-box-content main-space-box ">
    <section class="project-doorbox">
       <div class="ai-training-data-wrapper d-flex align-items-baseline justify-content-between">
           <div class="heading-content-box">
                <h2>All Users</h2>
                <form method="GET" action="{{ route('dashboard.admin.allUsers') }}" class="d-flex gap-2 mb-3">
                    <input type="text" name="search" class="form-control" placeholder="Search by name or phone number" value="{{ request('search') }}">

                    <button type="submit" class="btn btn-success">Filter</button>
                      @if(request()->hasAny(['search','status']))
                        <a href="{{ route('dashboard.admin.all-drivers') }}" class="btn btn-secondary">Reset</a>
                    @endif
                    
                </form>


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
                    <th>Gov.Id</th>
                    <th>Gov.Id(for walking)</th>
                    <th>Passport</th>
                    <th>Lisence</th>
                    <th>Selfie</th>

                    <!-- <th>Id verification status</th> -->
                    <th>Courier status</th>
                    <th>Profile Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                 @php
                        $passports = json_decode($user->passport_images, true) ?? [];
                        $licenses = json_decode($user->license_images, true) ?? [];
                         $walking_gov_id = json_decode($user->walking_gov_id, true) ?? [];

                        $hasPassport = !empty($passports);
                        $hasLicense  = !empty($licenses);
                        $haswalking_gov_id = !empty($walking_gov_id);
                        $hasSelfie   = !empty($user->courier_selfie);

                        $documentsUploaded = $hasPassport && $hasLicense && $hasSelfie;
                    @endphp
                    <tr>
                        <td>
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
                            @if($haswalking_gov_id)
                                @foreach($walking_gov_id as $img)
                                    <a href="{{ asset('assets/courier/walking_gov/'.$img) }}" target="_blank">
                                        <img src="{{ asset('assets/courier/walking_gov/'.$img) }}" width="60">
                                    </a>
                                @endforeach
                            @else
                                <span class="text-danger">Not Uploaded</span>
                            @endif
                        </td>
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

                        <!-- <td>
                            <div class="d-flex align-items-center gap-2">

                                {{-- BLOCKED --}}
                                @if($user->is_blocked)

                                    <button class="btn btn-warning btn-sm toggle-block-btn"
                                        data-user-id="{{ $user->id }}">
                                        Unblock
                                    </button>

                                {{-- NOT BLOCKED --}}
                                @else

                                    {{-- PENDING --}}
                                    @if($user->id_verified == 0)

                                        <button class="btn btn-success btn-sm verify-user-btn"
                                            data-user-id="{{ $user->id }}">
                                            Verify
                                        </button>

                                        <button class="btn btn-danger btn-sm reject-user-btn"
                                            data-user-id="{{ $user->id }}">
                                            Reject
                                        </button>

                                    {{-- VERIFIED --}}
                                    @elseif($user->id_verified == 1)

                                        <button class="btn btn-danger btn-sm reject-user-btn"
                                            data-user-id="{{ $user->id }}">
                                            Reject
                                        </button>

                                    {{-- REJECTED --}}
                                    @elseif($user->id_verified == 2)

                                        <button class="btn btn-success btn-sm verify-user-btn"
                                            data-user-id="{{ $user->id }}">
                                            Verify
                                        </button>

                                    @endif

                                    {{-- BLOCK BUTTON ALWAYS IF NOT BLOCKED --}}
                                    <button class="btn btn-danger btn-sm toggle-block-btn"
                                        data-user-id="{{ $user->id }}">
                                        Block
                                    </button>

                                @endif

                            </div>
                        </td> -->
                         
                        <td>
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
                            @endif
                        </td>
                        <td>
                            @if($user->is_blocked)
                                <span class="badge bg-dark">Blocked</span>

                            @elseif($user->id_verified == 0)
                                <span class="badge bg-warning text-dark">Pending</span>

                            @elseif($user->id_verified == 1)
                                <span class="badge bg-success">Verified</span>

                            @elseif($user->id_verified == 2)
                                <span class="badge bg-danger">Rejected</span>

                            @else
                                <span class="badge bg-secondary">Unknown</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                 <div class="d-flex align-items-center gap-2">

                                {{-- BLOCKED --}}
                                @if($user->is_blocked)

                                    <button class="btn btn-warning btn-sm toggle-block-btn"
                                        data-user-id="{{ $user->id }}">
                                        Unblock
                                    </button>

                                {{-- NOT BLOCKED --}}
                                @else

                                    {{-- PENDING --}}
                                    @if($user->id_verified == 0)

                                        <button class="btn btn-success btn-sm verify-user-btn"
                                            data-user-id="{{ $user->id }}">
                                            Verify
                                        </button>

                                        <button class="btn btn-danger btn-sm reject-user-btn"
                                            data-user-id="{{ $user->id }}">
                                            Reject
                                        </button>

                                    {{-- VERIFIED --}}
                                    @elseif($user->id_verified == 1)

                                        <button class="btn btn-danger btn-sm reject-user-btn"
                                            data-user-id="{{ $user->id }}">
                                            Reject
                                        </button>

                                    {{-- REJECTED --}}
                                    @elseif($user->id_verified == 2)

                                        <button class="btn btn-success btn-sm verify-user-btn"
                                            data-user-id="{{ $user->id }}">
                                            Verify
                                        </button>

                                    @endif

                                    {{-- BLOCK BUTTON ALWAYS IF NOT BLOCKED --}}
                                    <button class="btn btn-danger btn-sm toggle-block-btn"
                                        data-user-id="{{ $user->id }}">
                                        Block
                                    </button>

                                @endif

                            </div>
                                <a href="javascript:;" 
                                    class="action-btn me-3 view-user-details"
                                    data-user='@json($user)'
                                    data-bs-toggle="modal"
                                    data-bs-target="#userModal">
                                        <i class="fa fa-eye"></i>
                                </a>
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

                <tr><th>Profile Image</th><td><img id="modal-image" width="120"></td></tr>

                <tr><th>Name</th><td id="modal-name">-</td></tr>
                <tr><th>Email</th><td id="modal-email">-</td></tr>
                <tr><th>Phone</th><td id="modal-phone_number">-</td></tr>
                <tr><th>DOB</th><td id="modal-dob">-</td></tr>
                <tr><th>Gender</th><td id="modal-gender">-</td></tr>

                <tr><th>ID Status</th><td id="modal-id-status">-</td></tr>
                <tr><th>Courier Status</th><td id="modal-courier-status">-</td></tr>
                <tr><th>Online Status</th><td id="modal-online">-</td></tr>

                <tr><th>Government ID</th>
                <td id="modal-gov"></td></tr>

                <tr><th>Government ID(for walking courier)</th>
                <td id="modal-walking"></td></tr>

                <tr><th>Passport Images</th>
                <td id="modal-passport"></td></tr>

                <tr><th>License Images</th>
                <td id="modal-license"></td></tr>

                <tr><th>Selfie</th>
                <td><img id="modal-selfie" width="120"></td></tr>

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

    <!-- Block/Unblock Confirmation Modal -->
    <section class="modal fade" id="blockUnblockModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="blockUnblockLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h2 class="modal-title" id="blockUnblockLabel">Are you sure?</h2>
                    <button type="button" class="btn-close cancel-popup-btnbox" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="block-unblock-text">Do you really want to block/unblock this user?</p>
                </div>
                <div class="modal-footer border-0">
                    <button class="btn btn-secondary delete-confirmation-popup-btn btn" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary delete-confirmation-popup-btn btn delete-confirmation-popup-delete-btn confirm-block-unblock-btn" data-user-id="">Yes</button>
                </div>
            </div>
        </div>
    </section>
    


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    const csrfToken = "{{ csrf_token() }}";
    const deleteUserUrl = "{{ route('dashboard.admin.deleteUser') }}";
    const verifyUserUrl = "{{ url('dashboard/admin/verify-user') }}";
    const rejectUserUrl = "{{ url('dashboard/admin/reject-user') }}";
     const toggleBlockUserUrl = "{{ route('dashboard.admin.toggleBlockUser') }}";

</script>



@endsection
