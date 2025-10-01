@extends('admin.layouts.app')

@section('content')
<div class="main-box-content main-space-box ">
    <section class="project-doorbox">
       <div class="ai-training-data-wrapper d-flex align-items-baseline justify-content-between">
         <div class="heading-content-box">
            <h2>All Queries</h2>
            <div id="successMessage" class="alert alert-success d-none"></div>
            @if (session('success'))
                <div class="alert alert-success" role="alert" id="success-message">
                    {{ session('success') }}
                </div>
            @endif
            <!-- <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry...</p> -->
        </div>

        <div id="notificationMessage" class="alert d-none" role="alert"></div>

       </div> 
       <div id="notificationMessage" class="alert d-none" role="alert"></div>
            <div class="project-ongoing-box">
           <table class="table table-striped table-bordered table-notification-list">
                <thead>
                    <tr>
                        <th style="width:20%;">Phone Number</th>
                        <th style="width:30%;">Title</th>
                        <th>Query</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($enquiries as $enquiry)
                        <tr>
                            <td >{{ $enquiry->user->phone_number ?? 'N/A' }}</td>
                            <td><div class="scrollable-td">{{$enquiry->title}}</div></td> 
                            <td><div class="scrollable-td">{{$enquiry->description}}</div></td> 
                            <td>                                            
                                <div class="d-flex align-items-center gap-2">
                                    <button  class="dropdown-item delete-btn-design delete-query-btn d-flex justify-content-center" data-query-id="{{ $enquiry->id }}" type="button" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
                                        <i class="fa fa-regular fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                    <tr>
                      <td colspan="12" class="text-center">Geen gegevens gevonden.</td>
                    </tr>
                  @endforelse
                </tbody>
            </table>
        </div>
        @php
            $enquiryList = isset($search_queries) && $search_queries->isNotEmpty() ? $search_queries : $enquiries;
        @endphp
        @if ($enquiryList->lastPage() > 1)
            <nav class="pt-3" aria-label="Page navigation">
                <ul class="pagination" id="pagination-links">
                    {{-- Previous Page --}}
                    @if ($enquiryList->onFirstPage())
                        <li class="page-item disabled"><span class="page-link text-dark">Previous</span></li>
                    @else
                        <li class="page-item"><a class="page-link text-dark" href="{{ $enquiryList->previousPageUrl() }}">Previous</a></li>
                    @endif

                    {{-- Page Numbers --}}
                    @for ($i = 1; $i <= $enquiryList->lastPage(); $i++)
                        <li class="page-item {{ $enquiryList->currentPage() == $i ? 'active' : '' }}">
                            <a class="page-link" href="{{ $enquiryList->url($i) }}">{{ $i }}</a>
                        </li>
                    @endfor

                    {{-- Next Page --}}
                    @if ($enquiryList->hasMorePages())
                        <li class="page-item"><a class="page-link text-dark" href="{{ $enquiryList->nextPageUrl() }}">Next</a></li>
                    @else
                        <li class="page-item disabled"><span class="page-link text-dark">Next</span></li>
                    @endif
                </ul>
            </nav>
        @endif


    </section>  
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- delete-confirmation-popup -->
        <section class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h2 class="delete-confirmation-popup-title delete-user-confirmation-popup-title" id="staticBackdropLabel">Are you sure?</h2>
                        <button type="button" class="btn-close cancel-popup-btnbox" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body delete-confirmation-popup-body ">
                            <p class="delete-confirmation-popup-text delete-user-confirmation-popup-text">Do you really want to delete this Query?</p>
                    </div>
                    <div class="modal-footer border-0 delete-confirmation-popup-footer delete-user-confirmation-popup-footer">
                        <button class="delete-confirmation-popup-btn btn" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                        <button class="delete-confirmation-popup-btn btn delete-confirmation-popup-delete-btn delete-query-confirmation-popup-delete-btn" data-query-id="">Delete</button>
                    </div>
                </div>
            </div>
        </section>
    <!-- delete-confirmation-popup-->
@endsection

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    const csrfToken = "{{ csrf_token() }}";
    const deleteQueryUrl = "{{ route('dashboard.admin.deleteQuery') }}";
</script>

