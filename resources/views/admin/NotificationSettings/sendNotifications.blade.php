@extends('admin.layouts.app')

@section('content')
<div class="main-box-content main-space-box ">
    <section class="project-doorbox">
        <div class="heading-content-box heading-text-center-main">
            <h2 class="text-center">Send Notifications</h2>
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
            <form class="employe-form" action="{{ route('dashboard.admin.notifications.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-container">
                    <div class="row">

                        <div class="col-md-12">
                            <div class="form-group mb-4">
                                <label for="title">Notification Title</label>
                                <textarea id="title" name="title" class="form-control user-input" placeholder="Notification title">{{ old('title') }}</textarea>
                                @error('title')
                                    <div class="text-danger error-message">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <!-- Description -->
                        <div class="col-md-12">
                            <div class="form-group mb-4">
                                <label for="description">Notification Description</label>
                                <textarea id="description" name="description" class="form-control user-input" placeholder="Notification Description">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="text-danger error-message">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group mb-4">
                            <label>Select Users</label></div>
                            <div class="user-checkbox-list" style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px;">
                                <div>
                                    <input type="checkbox" name="users[]" value="all" id="user_all">
                                    <label for="user_all">All Users</label>
                                </div>
                                @foreach($users as $user)
                                <div >
                                    <input type="checkbox" name="users[]" value="{{ $user->id }}" id="user_{{ $user->id }}">
                                    <label for="user_{{ $user->id }}" class="scrollable-td">{{ $user->name }} ({{ $user->phone_number }})</label>
                                </div>
                                @endforeach
                            </div>
                            @error('users') 
                                <span class="text-danger">{{ $message }}</span> 
                            @enderror
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

