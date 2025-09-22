
@include('admin.auth.login_header')
<!-- Landing-Page-Banner-Start-->
<section class="landing-herobanner">
<div class="herobanner" style="background-image: url('{{ asset('assets/admin/images/bg-cover.png') }}');">
    <div class="container-xl ">
      <div class="d-flex flex-column gap-5">
      <div class="col-md-12">
        <div class="d-block text-center banner-contentbox login-contentbox">
          <div class="logo-clr">
            <img src="{{ asset('assets/admin/images/qadampayk-dash.png') }}" width="200" alt="logo">
          </div>
          <div class="login-box mx-auto">  
                               
            <!-- Login Form -->
            <form id="login-form" action="{{ route('login.submit') }}" class="login-formbox" method="POST">
              @csrf
              @if (session('success'))
                <div class="alert alert-success" role="alert" id="success-message">
                    {{ session('success') }}
                  </div>
              @endif 
              <div class="user-iconbox d-flex justify-content-center">
                <img src="{{ asset('assets/admin/images/user-icon.png') }}" alt="user-icon">
              </div>
              <div class="d-block mb-2">
                <div class="icon-box @error('email') is-error @enderror">
                  <input type="text"  name="email" id="username" placeholder="Email" value="{{ old('email', Cookie::get('email')) }}"> 
                </div>
                @error('email')
                <div class="text-start error-msg-field error-message" >{{ $message }}</div>
                @enderror
              </div>
              <div class="d-block mb-2">
                <div class="icon-box password-icon @error('password') is-error @enderror">
                  <input type="password" name="password" id="pass" placeholder="Password" value="{{ Cookie::get('password') }}" >
                  <span toggle="#pass" class="fa fa-fw fa-eye-slash toggle-password text-black" style="cursor: pointer;"></span>

                </div>
                @error('password')
                  <div class="text-start error-msg-field error-message" >{{ $message }}</div>
                  @enderror
                </div>
              
              <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="checkbox-group">
                  <input class="rounded-checkbox" name="remember" value="1" id="check1" type="checkbox" {{ Cookie::get('email') ? 'checked' : '' }}>
                  <label for="check1">Remember me</label>
                </div>
                <!-- <a class="forgot-pass" href="{{ route('forgot-password') }}" id="forgot-password-link">Forgot Password?</a> -->
              </div>
              <button type="submit" id="submit" value="LOGIN" class="login-btn-box">Login</button>
            </form>
          </div>
        </div>
      </div>
   
     </div>
    </div>
  </div>
  @include('admin.auth.login_footer')


    
  
