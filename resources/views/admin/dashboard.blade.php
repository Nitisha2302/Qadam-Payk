

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
                              <i class="fa fa-users icon-font-size"></i>
                            </div>
                            <div class="card-title m-0">
                                   {{ $userCount}}
                                <div class="card-subtitle">
                                    Total Users
                                </div>
                            </div>
                        </div>
                    </div>
		        </div>


                <div class="col-xl-3 col-sm-6 info-card mb-20">
                    <div class="card d-flex shadow">
                        <div class="card-body card-icon__left  d-flex align-items-center gap-3">
                            <div class="card-icon">
                              <i class="fa fa-users icon-font-size"></i>
                            </div>
                            <div class="card-title m-0">
                                   {{ $driversCount}}
                                <div class="card-subtitle">
                                    Total Drivers
                                </div>
                            </div>
                        </div>
                    </div>
		        </div>


                <div class="col-xl-3 col-sm-6 info-card mb-20">
                    <div class="card d-flex shadow">
                        <div class="card-body card-icon__left  d-flex align-items-center gap-3">
                            <div class="card-icon">
                              <i class="fa fa-users icon-font-size"></i>
                            </div>
                            <div class="card-title m-0">
                                   0
                                <div class="card-subtitle">
                                    Total Passengers
                                </div>
                            </div>
                        </div>
                    </div>
		        </div>


                <div class="row mt-4">
                 <!-- Pending Users -->
                    <div class="col-xl-3 col-sm-6 info-card mb-20">
                        <div class="card d-flex shadow">
                            <div class="card-body card-icon__left d-flex align-items-center gap-3">
                                <div class="card-icon">
                                    <i class="fas fa-hourglass-half icon-font-size text-warning"></i>
                                </div>
                                <div class="card-title m-0"> 
                                    {{ $pendingDrivers }}
                                    <div class="card-subtitle">Pending Verification</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Verified Users -->
                    <div class="col-xl-3 col-sm-6 info-card mb-20">
                        <div class="card d-flex shadow">
                            <div class="card-body card-icon__left d-flex align-items-center gap-3">
                                <div class="card-icon">
                                    <i class="fas fa-check-circle icon-font-size text-success"></i>
                                </div>
                                <div class="card-title m-0">
                                    {{ $verifiedDrivers }}
                                    <div class="card-subtitle">Verified Drivers</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rejected Users -->
                    <div class="col-xl-3 col-sm-6 info-card mb-20">
                        <div class="card d-flex shadow">
                            <div class="card-body card-icon__left d-flex align-items-center gap-3">
                                <div class="card-icon">
                                    <i class="fas fa-times-circle icon-font-size text-danger"></i>
                                </div>
                                <div class="card-title m-0">
                                    {{ $rejectedDrivers }}
                                    <div class="card-subtitle">Rejected Drivers</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Second Row: Cities -->
                <div class="row mt-4">
                    <div class="col-xl-3 col-sm-6 info-card mb-20">
                        <div class="card d-flex shadow">
                            <div class="card-body card-icon__left d-flex align-items-center gap-3">
                                <div class="card-icon">
                                    <i class="fas fa-city icon-font-size"></i>
                                </div>
                                <div class="card-title m-0">
                                    {{ $cityCount }}
                                    <div class="card-subtitle">
                                        Total Cities
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card shadow p-3">
                            <h4>Total Users, Drivers & Passengers</h4>
                            <canvas id="totalsChart" height="150"></canvas>
                        </div>
                    </div>
                </div>

                 <div class="row mt-4">
                    <div class="col-12">
                        <div class="card shadow p-3">
                            <h4>Drivers Verification Status</h4>
                            <canvas id="userVerificationChart" height="150"></canvas>
                        </div>
                    </div>
                </div>

	        </div>
        </div>
           

      </section>  
    </div>
@endsection

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {

    // Existing chart
    const ctx1 = document.getElementById('userVerificationChart').getContext('2d');
    new Chart(ctx1, {
        type: 'bar',
        data: {
            labels: ['Pending', 'Verified', 'Rejected'],
            datasets: [{
                label: 'Drivers',
                data: {!! json_encode([$pendingDrivers, $verifiedDrivers, $rejectedDrivers]) !!},
                backgroundColor: [
                    'rgba(255, 193, 7, 0.7)',   // warning yellow
                    'rgba(40, 167, 69, 0.7)',   // success green
                    'rgba(220, 53, 69, 0.7)'    // danger red
                ],
                borderColor: [
                    'rgba(255, 193, 7, 1)',
                    'rgba(40, 167, 69, 1)',
                    'rgba(220, 53, 69, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: { enabled: true }
            },
            scales: {
                y: { beginAtZero: true, precision: 0 }
            }
        }
    });

    // New chart for Total Users & Passengers
   const ctx = document.getElementById('totalsChart').getContext('2d');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Users', 'Drivers', 'Passengers'],
            datasets: [{
                label: 'Count',
                data: {!! json_encode([$userCount, $driversCount, 0]) !!}, // Replace 0 with $passengersCount if available
                backgroundColor: [
                    'rgba(54, 162, 235, 0.7)',   // blue for Users
                    'rgba(40, 167, 69, 0.7)',    // green for Drivers
                    'rgba(255, 99, 132, 0.7)'    // red/pink for Passengers
                ],
                borderColor: [
                    'rgba(54, 162, 235, 1)',
                    'rgba(40, 167, 69, 1)',
                    'rgba(255, 99, 132, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: { enabled: true }
            },
            scales: {
                y: { beginAtZero: true, precision: 0 }
            }
        }
    });

});
</script>





