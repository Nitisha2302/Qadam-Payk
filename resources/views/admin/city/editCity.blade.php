

@extends('admin.layouts.app')

@section('content')

    <div class="main-box-content main-space-box ">

        <section class="project-doorbox">
            <div class="heading-content-box">
                <h2>Update City</h2>

                <div class="alert alert-success" role="alert" id="success-message" style="display: none;">
                    {{ session('success') }}
                </div>
                <div id="assigned-success-message" class="alert alert-success" style="display: none;"></div>

                @if (session('success'))
                    <div class="alert alert-success" role="alert" id="success-message">
                        {{ session('success') }}
                    </div>
                @endif
                
            </div>
            <div id="notificationMessage" class="alert d-none" role="alert"></div>
             <div class="project-ongoing-box">
                <form class="employe-form" action="{{ route('dashboard.admin.update-city', $city->id) }}" method="POST"  enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6 step-field">
                            <div class="form-group mb-4">
                                <label for="city_name">City Name</label>
                                <input type="text" id="city_name" name="city_name" class="form-control" placeholder="Enter city name" value="{{ old('city_name', $city->city_name) }}">
                                @error('city_name')
                                    <div class="text-danger error-message">{{ $message }}</div>
                                @enderror
                        </div>
                        </div>

                        <!-- <div class="col-md-6 step-field">
                            <div class="form-group mb-4">
                                <label for="state">State</label>
                                <input type="text" id="state" name="state" class="form-control" placeholder="Enter state" value="{{ old('state', $city->state) }}">
                                @error('state')
                                    <div class="text-danger error-message">{{ $message }}</div>
                                @enderror
                        </div>
                        </div> -->

                        <div class="col-md-6 step-field">
                           <div class="form-group mb-4">
                                <label for="country">Country</label>
                                <input type="text" id="country" name="country" class="form-control" placeholder="Enter country" value="{{ old('country', $city->country) }}">
                                @error('country')
                                    <div class="text-danger error-message">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-12">
                            <button type="submit" class="btn-box btn-submt-user py-block justify-content-center ms-0 mt-3">
                                Update
                            </button>
                        </div>
                    </div>
                </form>
           </div>

        </section> 

    </div>


@endsection




