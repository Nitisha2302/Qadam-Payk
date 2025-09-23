@extends('admin.layouts.app')

@section('content')

    <div class="main-box-content main-space-box ">

        <section class="project-doorbox">
            <div class="heading-content-box">
                <h2>Update Service</h2>

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
               <form class="employe-form" id="serviceEditForm" action="{{ route('dashboard.admin.update-service', $service->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row">
                       <div class="col-md-12">
                            <div class="upload-img-btn d-flex align-items-center mb-3">
                                <h4>Icon Image:</h4>
                                <label class="upload-btn" style="cursor:pointer;">
                                    Upload
                                    <input type="file" id="fileInputEdit" class="d-none" name="image" accept=".jpeg,.jpg,.png" onchange="previewEditImage()">
                                </label>
                                @error('image')
                                    <div class="text-danger error-message">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="grid-main-imgbox mb-3">
                                <div class="d-block">
                                    <div class="d-flex flex-wrap" id="imagePreviewEdit">
                                        @if($service->service_image)
                                            <div style="position:relative;margin:5px;">
                                                <img src="{{ asset('assets/services_images/'.$service->service_image) }}" style="max-width:80px;height:80px;border:1px solid #ddd;border-radius:5px;object-fit:cover;">
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="col-md-6 step-field">
                            <div class="form-group mb-4">
                                <label for="service_name">Service Name</label>
                                <input type="text" id="service_name" name="service_name" class="form-control" placeholder="Enter service name" value="{{ old('service_name', $service->service_name) }}">
                                @error('service_name')
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
