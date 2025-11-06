@extends('admin.layouts.app')

@section('content')

    <div class="main-box-content main-space-box ">

        <section class="project-doorbox">
            <div class="heading-content-box">
                <h2>Add Car</h2>

                @if (session('success'))
                    <div class="alert alert-success" role="alert">
                        {{ session('success') }}
                    </div>
                @endif

                <!-- <p>Add a new car with model, brand, color, and other features.</p> -->
            </div>

            <div class="project-ongoing-box">
                <form class="employe-form" 
                      action="{{ route('dashboard.admin.store-car') }}" 
                      method="POST"  
                      enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        {{-- Brand --}}
                        <div class="col-md-6 step-field">
                            <div class="form-group mb-4">
                                <label for="brand">Brand</label>
                                <input type="text" id="brand" name="brand" class="form-control" 
                                       placeholder="Enter brand" value="{{ old('brand') }}">
                                @error('brand')
                                    <div class="text-danger error-message">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Car Model --}}
                        <div class="col-md-6 step-field">
                            <div class="form-group mb-4">
                                <label for="car_model">Car Model</label>
                                <input type="text" id="car_model" name="car_model" class="form-control" 
                                       placeholder="Enter car model" value="{{ old('car_model') }}">
                                @error('car_model')
                                    <div class="text-danger error-message">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Color --}}

                        {{-- Seats --}}
                        <div class="col-md-6 step-field">
                            <div class="form-group mb-4">
                                <label for="seats">Total Seats</label>
                                <input type="number" id="seats" name="seats" class="form-control" 
                                    placeholder="Enter number of seats" value="{{ old('seats') }}" min="1" step="1">
                                @error('seats')
                                    <div class="text-danger error-message">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                         <!-- Language Code -->
                        <div class="col-md-6 step-field">
                            <div class="form-group mb-4">
                                <label for="language_code">Language Code</label>
                                <select id="language_code" name="language_code" class="form-control">
                                    <option value="">Select Language</option>
                                    <option value="en" {{ old('language_code', $city->language_code ?? '') == 'en' ? 'selected' : '' }}>English (en)</option>
                                    <option value="ru" {{ old('language_code', $city->language_code ?? '') == 'ru' ? 'selected' : '' }}>Russian (ru)</option>
                                    <option value="tj" {{ old('language_code', $city->language_code ?? '') == 'tj' ? 'selected' : '' }}>Tajik (tj)</option>
                                </select>
                                @error('language_code')
                                    <div class="text-danger error-message">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Submit --}}
                        <div class="col-md-12">
                            <button type="submit" class="btn-box btn-submt-user py-block justify-content-center ms-0 mt-3">
                                Add Car
                            </button>
                        </div>

                    </div>
                </form>
           </div>

        </section> 

    </div>

@endsection
