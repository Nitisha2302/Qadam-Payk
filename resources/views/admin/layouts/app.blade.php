<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>QADAMPAYK</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="icon" href="{{ asset('favicon-qadampayk.png') }}" type="image/x-icon">
  <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="{{ asset('assets/admin/bootstrap/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/style.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/style-new.css') }}">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  
  @include('admin.css.custom')
</head>
<body>

<div class="overlay-box"></div>
<!-- header -->
@include('admin.layouts.nav-top')
<!-- header -->


<!-- sidenavbar -->
@include('admin.layouts.side-nav')
<!-- sidenavbar -->



<!-- main-content -->
 
<div class="main">
  @yield('content')
<div>
<!-- main-content -->

<script src="{{ asset('assets/admin/js/custom-js/jquery.min.js') }}"></script>
<script src="{{ asset('assets/admin/js/custom-js/custom.js') }}"></script>
<script src="{{ asset('assets/admin/js/admin-js/admin.js') }}"></script>
<script src="{{ asset('assets/admin/js/ckeditor/ckeditor.js') }}"></script>
<script src="{{ asset('assets/admin/js/bootstrap-js/bootstrap.bundle.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

</body>
</html>