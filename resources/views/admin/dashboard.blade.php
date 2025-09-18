

@extends('admin.layouts.app')

@section('content')
<div class="main-box-content main-space-box ">

      <section class="project-doorbox">
        <div class="heading-content-box">
            <h2>Dashboard</h2>

            <!-- <div class="alert alert-success" role="alert" id="success-message" style="display: none;">
            {{ session('success') }}
            </div> -->
            <div id="assigned-success-message" class="alert alert-success" style="display: none;"></div>

            @if (session('success'))
            <div class="alert alert-success" role="alert" id="success-message">
                {{ session('success') }}
            </div>
            @endif
            <!-- <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.</p> -->
        </div>
        <div class="project-ongoing-box">
          <div class="row mt-30">
         		<div class="col-xl-3 col-sm-6 info-card mb-20">
                    <div class="card d-flex shadow">
                        <div class="card-body card-icon__left  d-flex align-items-center gap-3">
                            <div class="card-icon">
                                <i class="fas fa-city icon-font-size "></i>
                            </div>
                            <div class="card-title m-0">
                                   {{ $cityCount}}
                                <div class="card-subtitle">
                                    Total Cities
                                </div>
                            </div>
                        </div>
                    </div>
		        </div>
	        </div>
        </div>
           

      </section>  
    </div>
@endsection

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>



