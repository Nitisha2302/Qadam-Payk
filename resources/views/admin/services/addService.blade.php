

@extends('admin.layouts.app')

@section('content')

    <div class="main-box-content main-space-box ">

        <section class="project-doorbox">
            <div class="heading-content-box">
                <h2>Add Service</h2>

                <div class="alert alert-success" role="alert" id="success-message" style="display: none;">
                    {{ session('success') }}
                </div>
                <div id="assigned-success-message" class="alert alert-success" style="display: none;"></div>

                @if (session('success'))
                    <div class="alert alert-success" role="alert" id="success-message">
                        {{ session('success') }}
                    </div>
                @endif
                <!-- <p>Add a new city with optional state and country information.</p> -->
            </div>
            <div id="notificationMessage" class="alert d-none" role="alert"></div>
             <div class="project-ongoing-box">
                <form class="employe-form" action="{{ route('dashboard.admin.store-service') }}" method="POST"  enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 step-field">
                            <div class="form-group mb-4">
                                <label for="service_name">Service Name</label>
                                <input type="text" id="service_name" name="service_name" class="form-control" placeholder="Enter service name" value="{{ old('service_name') }}">
                                @error('service_name')
                                    <div class="text-danger error-message">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-12">
                            <button type="submit" class="btn-box btn-submt-user py-block justify-content-center ms-0 mt-3">
                                Add
                            </button>
                        </div>
                    </div>
                </form>
           </div>

        </section> 

    </div>


@endsection




