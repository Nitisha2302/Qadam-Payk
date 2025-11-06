

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
                <form class="employe-form"  id="serviceForm"  enctype="multipart/form-data">
                  
                    <div class="row">

                                          <div class="col-md-12">
                            <div class="upload-img-btn d-flex align-items-center mb-3">
                                <h4>Icon image:</h4>
                                <div class="d-flex align-items-center">
                                    <label class="upload-btn" style="cursor:pointer;">
                                    Upload
                                        <input type="file" id="fileInput1" class="d-none" name="image" accept="image/*" onchange="previewImage()">
                                    </label>
                                    @error('image')
                                        <div class="text-danger error-message">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="grid-main-imgbox mb-3" id="imagePreviewGrid">
                            <div class="d-block">
                                <div class="d-flex flex-wrap" id="imagePreview1"></div>
                            </div>
                        </div>
                        <div class="col-md-6 step-field">
                            <div class="form-group mb-4">
                                <label for="service_name">Service Name</label>
                                <input type="text" id="service_name" name="service_name" class="form-control" placeholder="Enter service name" value="{{ old('service_name') }}">
                                @error('service_name')
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    const serviceStoreData = "{{ route('dashboard.admin.store-service') }}";
    const csrfToken = "{{ csrf_token() }}";
</script>




