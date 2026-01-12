@include('admin.auth.login_header')
  
<!-- Landing-Page-Banner-Start-->
<section class="landing-herobanner">
  <div class="herobanner" style="background-image: url('{{ asset('assets/admin/images/bg-cover.png') }}');">
    <div class="container-xl ">
      <div class="d-flex flex-column gap-5">
      <div class="col-md-12">
        <div class="d-block text-center banner-contentbox login-contentbox">
          <div class="logo-clr">
            <img src="{{ asset('assets/admin/images/logo_QADAM.webp') }}" width="200" alt="logo">
          </div>
          <div class="login-box mx-auto">                          


            
           <!-- Forgot Password Form -->
            <form id="forgot-password-form" class="login-formbox" method="POST" action="{{ route('forgot-password-link') }}">
              @csrf
              @if (session('success'))
                <div class="alert alert-success" role="alert" id="success-message">
                  {{ session('success') }}
                </div>
              @endif
         
              
              <h3 class="mb-4 text-black">Forgot password? </h3>
              <div class="d-block mb-4">
                <div class="icon-box email-icon @error('email') is-error @enderror">
                  <input type="text" name="email" id="email" placeholder="Email " value="{{ old('email') }}" >
                </div>
                @error('email')
                  <div class="text-start error-msg-field error-message">{{ $message }}</div>
                  @enderror
              </div>             
              <button type="submit" class="login-btn-box btn-hoverbox mb-2">Next</button>
              <a href="{{ route('login') }}" class="login-btn-box btn-hoverbox btn-bck-forward" id="back-to-login">Back</a>
            </form>

           
          </div>
        </div>
      </div>
  
     </div>
    </div>
  </div>
  @include('admin.auth.login_footer')




