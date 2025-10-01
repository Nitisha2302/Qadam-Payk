@extends('admin.layouts.app')

@section('content')
<div class="main-box-content main-space-box ">
    <section class="project-doorbox">
       <div class="ai-training-data-wrapper d-flex align-items-baseline justify-content-between">
         <div class="heading-content-box">
            <h2>All Services</h2>
            <div id="successMessage" class="alert alert-success d-none"></div>
            @if (session('success'))
                <div class="alert alert-success" role="alert" id="success-message">
                    {{ session('success') }}
                </div>
            @endif
            <!-- <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry...</p> -->
        </div>

        <div id="notificationMessage" class="alert d-none" role="alert"></div>
           
         <a href="{{ route('dashboard.admin.service') }}" class="btn btn-green">Add Service</a>

       </div> 
       <div id="notificationMessage" class="alert d-none" role="alert"></div>
        <div class="project-ongoing-box">
           <table class="table table-striped table-bordered table-notification-list">
                <thead>
                    <tr>
                        <th>Icon</th>
                        <th>Services</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($services as $service)
                        <tr>
                            <td>
                            <img class="listing-img" 
                                src="{{ $service->service_image ? asset('assets/services_images/' . $service->service_image) : asset('assets/profile_image/default_user_profile.jpg') }}" 
                                alt="user-img" width="80">
                           </td>
                            <td> {{$service->service_name}}</td> 
                            <td>                                            
                                <div class="d-flex align-items-center gap-2">
                                    <a href="{{ route('dashboard.admin.edit-service', ['id' => $service->id]) }}" class="action-btn">
                                    <i class="fas fa-edit"></i> <span class="edit-span"></span>
                                    </a>
                                    <button  class="dropdown-item delete-btn-design delete-service-btn d-flex justify-content-center" data-service-id="{{ $service->id }}" type="button" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
                                        <i class="fa fa-regular fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                    <tr>
                      <td colspan="12" class="text-center">No data found.</td>
                    </tr>
                  @endforelse                 
                </tbody>
            </table>
        </div>
        @php
            $serviceList = isset($search_services) && $search_services->isNotEmpty() ? $search_services : $services;
        @endphp
        @if ($serviceList->lastPage() > 1)
            <nav class="pt-3" aria-label="Page navigation">
                <ul class="pagination" id="pagination-links">
                    {{-- Previous Page --}}
                    @if ($serviceList->onFirstPage())
                        <li class="page-item disabled"><span class="page-link text-dark">Previous</span></li>
                    @else
                        <li class="page-item"><a class="page-link text-dark" href="{{ $serviceList->previousPageUrl() }}">Previous</a></li>
                    @endif

                    {{-- Page Numbers --}}
                    @for ($i = 1; $i <= $serviceList->lastPage(); $i++)
                        <li class="page-item {{ $serviceList->currentPage() == $i ? 'active' : '' }}">
                            <a class="page-link" href="{{ $serviceList->url($i) }}">{{ $i }}</a>
                        </li>
                    @endfor

                    {{-- Next Page --}}
                    @if ($serviceList->hasMorePages())
                        <li class="page-item"><a class="page-link text-dark" href="{{ $serviceList->nextPageUrl() }}">Next</a></li>
                    @else
                        <li class="page-item disabled"><span class="page-link text-dark">Next</span></li>
                    @endif
                </ul>
            </nav>
        @endif


    </section>  
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Modal -->
        <section class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h2 class="delete-confirmation-popup-title delete-user-confirmation-popup-title" id="staticBackdropLabel">Are you sure?</h2>
                        <button type="button" class="btn-close cancel-popup-btnbox" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body delete-confirmation-popup-body ">
                            <p class="delete-confirmation-popup-text delete-user-confirmation-popup-text">Do you really want to delete this service?</p>
                    </div>
                    <div class="modal-footer border-0 delete-confirmation-popup-footer delete-user-confirmation-popup-footer">
                        <button class="delete-confirmation-popup-btn btn" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                        <button class="delete-confirmation-popup-btn btn delete-confirmation-popup-delete-btn delete-service-confirmation-popup-delete-btn" data-service-id="">Delete</button>
                    </div>
                </div>
            </div>
        </section>
    <!-- delete-confirmation-popup-->
@endsection

<script>
    const csrfToken = "{{ csrf_token() }}";
     const deleteServiceUrl = "{{ url('dashboard/admin/delete-service') }}"; 
</script>
