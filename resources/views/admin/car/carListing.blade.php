@extends('admin.layouts.app')

@section('content')
<div class="main-box-content main-space-box ">
    <section class="project-doorbox">
       <div class="ai-training-data-wrapper d-flex align-items-baseline justify-content-between">
         <div class="heading-content-box">
            <h2>Car Listing</h2>
            <div id="successMessage" class="alert alert-success d-none"></div>
            @if (session('success'))
                <div class="alert alert-success" role="alert" id="success-message">
                    {{ session('success') }}
                </div>
            @endif
            <!-- <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry...</p> -->
        </div>

        <div id="notificationMessage" class="alert d-none" role="alert"></div>
           
        <a href="{{ route('dashboard.admin.car') }}" class="btn btn-green">Add Car</a></br></br>

       </div> 
       <div id="notificationMessage" class="alert d-none" role="alert"></div>
        <div class="project-ongoing-box">
           <table class="table table-striped table-bordered table-notification-list">
                <thead>
                    <tr>
                        <th>Car Model</th>
                        <th>Brand</th>
                         <th>Seats</th>
                         <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cars as $car)
                        <tr>
                            <td> {{ $car->model_name ?? 'N/A' }}</td>
                            <td>{{ $car->brand ?? 'N/A' }}</td>
                            <td>{{ $car->seats ?? 'N/A' }}</td> 
                            <td>                                            
                                <div class="d-flex align-items-center gap-2">
                                    <a href="{{ route('dashboard.admin.edit-car', ['id' => $car->id]) }}" class="action-btn">
                                    <i class="fas fa-edit"></i> <span class="edit-span"></span>
                                    </a>
                                    <button  class="dropdown-item delete-btn-design delete-car-btn d-flex justify-content-center" data-car-id="{{ $car->id }}" type="button" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
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
            $carList = isset($search_cars) && $search_cars->isNotEmpty() ? $search_cars : $cars;
        @endphp
        @if ($carList->lastPage() > 1)
            <nav class="pt-3" aria-label="Page navigation">
                <ul class="pagination" id="pagination-links">
                    {{-- Previous Page --}}
                    @if ($carList->onFirstPage())
                        <li class="page-item disabled"><span class="page-link text-dark">Previous</span></li>
                    @else
                        <li class="page-item"><a class="page-link text-dark" href="{{ $carList->previousPageUrl() }}">Previous</a></li>
                    @endif

                    {{-- Page Numbers --}}
                    @for ($i = 1; $i <= $carList->lastPage(); $i++)
                        <li class="page-item {{ $carList->currentPage() == $i ? 'active' : '' }}">
                            <a class="page-link" href="{{ $carList->url($i) }}">{{ $i }}</a>
                        </li>
                    @endfor

                    {{-- Next Page --}}
                    @if ($carList->hasMorePages())
                        <li class="page-item"><a class="page-link text-dark" href="{{ $carList->nextPageUrl() }}">Next</a></li>
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
                            <p class="delete-confirmation-popup-text delete-user-confirmation-popup-text">Do you really want to delete this car?</p>
                    </div>
                    <div class="modal-footer border-0 delete-confirmation-popup-footer delete-user-confirmation-popup-footer">
                        <button class="delete-confirmation-popup-btn btn" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                        <button class="delete-confirmation-popup-btn btn delete-confirmation-popup-delete-btn delete-car-confirmation-popup-delete-btn" data-car-id="">Delete</button>
                    </div>
                </div>
            </div>
        </section>
    <!-- delete-confirmation-popup-->
@endsection

<script>
    const csrfToken = "{{ csrf_token() }}";
     const deleteCarUrl = "{{ url('dashboard/admin/delete-car') }}"; 
</script>
