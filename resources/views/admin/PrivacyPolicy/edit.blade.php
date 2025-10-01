@extends('admin.layouts.app')

@section('content')
<div class="main-box-content main-space-box ">
    <section class="project-doorbox">
        <div class="heading-content-box heading-text-center-main">
            <h2 class="text-center">Update Privacy Policy Content</h2>
            <div id="successMessage" class="alert alert-success d-none"></div>

            @if (session('success'))
                <div class="alert alert-success" role="alert" id="success-message">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger" role="alert" id="error-message">
                    {{ session('error') }}
                </div>
            @endif

            <!-- <p style="text-align:center;">Lorem Ipsum is simply dummy text of the printing and typesetting industry...</p> -->
        </div>

        <div class="project-ongoing-box">
            <form class="employe-form" action="" method="POST"  enctype="multipart/form-data">
                  @csrf
                  <div class="form-container">
                    <div class="row">

                        <div class="col-md-12">
                            <div class="form-group mb-4">
                                <label for="title">Privacy Policy Title</label>
                                <textarea id="title-editor" name="title" class="form-control user-input" placeholder="Enter Privacy Policy Title"></textarea>
                                @error('title')
                                    <div class="text-danger error-message">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group mb-4">
                                <label for="description">Privacy Policy Content</label>
                                <textarea id="content-editor" name="content" class="form-control user-input" placeholder="Enter Privacy Policy Content"></textarea>
                                @error('content')
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
                  </div>
            </form>
        </div>
    </section>  
</div>


@endsection
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<script>
    $(document).ready(function () {
        CKEDITOR.replace('title-editor', {
            height: 100,   // smaller height for title
            allowedContent: true
        });
        CKEDITOR.replace('content-editor', {
            height: 300,   // bigger height for content
            allowedContent: true
        });
    });
</script>

