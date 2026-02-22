@extends('admin.layouts.app')
 
@section('content')
<div class="main-box-content main-space-box ">
    <section class="project-doorbox">
        <div class="heading-content-box heading-text-center-main">
            <h2 class="text-center">Make Announcement/News</h2>
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
 
            <p style="text-align: center !important;">Send festival greetings, important announcements, or personal notifications directly to your users with just a few clicks.</p>
        </div>
 
        <div class="project-ongoing-box">
            <form class="employe-form" id="notificationForm" action="{{ route('dashboard.admin.notifications.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-container">
                    <div class="row">

                      <div class="col-md-12">
                            <div class="form-group mb-4">
                                <label for="type">Type</label>

                                <select name="type" id="type" class="form-control user-input">
                                    <option value="">-- Select Type --</option>
                                    <option value="1" {{ old('type', 1) == 1 ? 'selected' : '' }}>
                                        Announcement
                                    </option>
                                    <option value="2" {{ old('type') == 2 ? 'selected' : '' }}>
                                        News
                                    </option>
                                </select>

                                @error('type')
                                    <span class="text-danger error-message">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
 
                        <div class="col-md-12">
                            <div class="form-group mb-4">
                                <label for="title">Title</label>
                                <textarea id="title" name="title" class="form-control user-input" placeholder="Title">{{ old('title') }}</textarea>
                                @error('title')
                                    <div class="text-danger error-message">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <!-- Description -->
                        <div class="col-md-12">
                            <div class="form-group mb-4">
                                <label for="description">Description</label>
                                <textarea id="description" name="description" class="form-control user-input" placeholder="Description">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="text-danger error-message">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
 
                        <div class="col-md-12">
                            <div class="form-group mb-4">
                                <label for="announcement_date">Announcement Date</label>
                                <input type="date" name="announcement_date" id="announcement_date" class="form-control user-input"
                                    value="{{ old('announcement_date') }}">
                                @error('announcement_date')
                                    <div class="text-danger error-message">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                       
 
                       <div class="col-md-12">
                            <div class="form-group mb-4">
                                <label>Select Users</label>
                            </div>

                            <div class="user-checkbox-list"
                                style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px;">

                                <div>
                                    <input type="radio" name="user_group" value="all" id="all_users">
                                    <label for="all_users">All Users</label>
                                </div>

                                <div>
                                    <input type="radio" name="user_group" value="drivers" id="drivers">
                                    <label for="drivers">Drivers</label>
                                </div>

                                <div>
                                    <input type="radio" name="user_group" value="passengers" id="passengers">
                                    <label for="passengers">Passengers</label>
                                </div>

                            </div>

                            @error('user_group')
                                <span class="text-danger error-message">{{ $message }}</span>
                            @enderror
                        </div>

 
                        <div class="col-md-12">
                            <div class="upload-img-btn d-flex align-items-center mb-3">
                                <h4>Banner/Image:</h4>
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
 
 
                        <div class="col-md-12">
                            <button type="submit" class="btn-box btn-submt-user py-block justify-content-center ms-0 mt-3">
                                Send
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>  
</div>
<script>
    window.notificationStoreURL = "{{ route('dashboard.admin.notifications.store') }}";
</script>
 
@endsection
 
 
 
<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        CKEDITOR.replace('description', {
            allowedContent: true,
            height: 150,
            on: {
                instanceReady: function (evt) {
                    evt.editor.focus();
                    const editorBody = evt.editor.document.getBody();
                    editorBody.setStyle('padding', '20px');
                    editorBody.setStyle('background-color', '#ffffff');
                }
            }
        });
 
         CKEDITOR.replace('title', {
            allowedContent: true,
            height: 150,
            on: {
                instanceReady: function (evt) {
                    evt.editor.focus();
                    const editorBody = evt.editor.document.getBody();
                    editorBody.setStyle('padding', '20px');
                    editorBody.setStyle('background-color', '#ffffff');
                }
            }
        });
    });
</script> -->