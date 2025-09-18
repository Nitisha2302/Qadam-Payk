@extends('admin.layouts.app')

@section('content')

    <div class="main-box-content main-space-box ">

        <section class="project-doorbox">
            <div class="heading-content-box">
                <h2>Upadte Car Details</h2>

                @if (session('success'))
                    <div class="alert alert-success" role="alert">
                        {{ session('success') }}
                    </div>
                @endif

                <!-- <p>Add a new car with model, brand, color, and other features.</p> -->
            </div>

            <div class="project-ongoing-box">
                <form class="employe-form" 
                      action="{{ route('dashboard.admin.update-car', $car->id) }}" 
                      method="POST"  
                      enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="row">

 

                        {{-- Brand --}}
                        <div class="col-md-6 step-field">
                            <div class="form-group mb-4">
                                <label for="brand">Brand</label>
                                <input type="text" id="brand" name="brand" class="form-control" 
                                       placeholder="Enter brand" value="{{ old('brand', $car->brand) }}">
                                @error('brand')
                                    <div class="text-danger error-message">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                                               {{-- Car Model --}}
                        <div class="col-md-6 step-field">
                            <div class="form-group mb-4">
                                <label for="model_name">Car Model</label>
                                <input type="text" id="model_name" name="model_name" class="form-control" 
                                       placeholder="Enter car model" value="{{ old('model_name', $car->model_name) }}">
                                @error('model_name')
                                    <div class="text-danger error-message">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Color --}}
                        <div class="col-md-6 step-field">
                            <div class="form-group mb-4">
                                <label for="color">Color</label>
                                <input type="text" id="color" name="color" class="form-control" 
                                       placeholder="Enter car color" value="{{ old('color', $car->color) }}">
                                @error('color')
                                    <div class="text-danger error-message">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>


                        {{-- Features (array checkboxes) --}}
                        <!-- <div class="col-md-12 step-field">
                            <div class="form-group mb-4">
                                <label>Features</label><br>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="features[]" value="AC">
                                    <label class="form-check-label">AC</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="features[]" value="WiFi">
                                    <label class="form-check-label">WiFi</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="features[]" value="Music System">
                                    <label class="form-check-label">Music System</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="features[]" value="GPS">
                                    <label class="form-check-label">GPS</label>
                                </div>
                                @error('features')
                                    <div class="text-danger error-message">{{ $message }}</div>
                                @enderror
                            </div>
                        </div> -->

                        {{-- Submit --}}
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
