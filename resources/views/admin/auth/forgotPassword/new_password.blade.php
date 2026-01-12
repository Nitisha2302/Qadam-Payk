@include('admin.auth.login_header')
  
<!-- Landing-Page-Banner-Start-->
<section class="landing-herobanner">

  <div class="herobanner" style="background-image: url('{{ asset('assets/admin/images/bg-cover.png') }}');">
    <div class="container-xl">
      <div class="d-flex flex-column gap-5">
        <div class="col-md-12">
          <div class="d-block text-center banner-contentbox login-contentbox">
            <div class="logo-clr">
              <img src="{{ asset('assets/admin/images/logo_QADAM.webp') }}" width="200" alt="logo">
            </div>
            <div class="login-box mx-auto">                          
              <div class="login-formbox">
                <h3 class="mb-4 text-black">New Password</h3>
                <form method="POST" action="{{ route('update-password') }}">
                  @csrf
                  <input type="hidden" name="token" value="{{ $token ?? '' }}">
                  <input type="hidden" name="email" value="{{ $email }}">
                  <div class="d-block mb-2">
                    <div class="icon-box password-icon @error('password') is-error @enderror">
                      <input type="password" name="password" id="pass" class="pass" placeholder="Create new password"  />
                      <span toggle="#pass" class="fa fa-fw fa-eye-slash toggle-password text-black" style="cursor: pointer;"></span>
                    </div>
                    @error('password')
                      <div class="text-start error-msg-field error-message">{{ $message }}</div>
                    @enderror
                  </div>
                  <div class="d-block mb-4">
                    <div class="icon-box password-icon mb-4 @error('password_confirmation') is-error @enderror">
                      <input type="password" name="password_confirmation" id="confirm_pass" class="pass" placeholder="Confirm password"  />
                      <span toggle="#confirm_pass" class="fa fa-fw fa-eye-slash toggle-password text-black" style="cursor: pointer;"></span>
                    </div>
                    @error('password_confirmation')
                      <div class="text-start error-msg-field error-message">{{ $message }}</div>
                    @enderror
                  </div>
                  
                  <button class="login-btn-box btn-hoverbox mb-2">Change</button>
                </form>
              </div>
            </div>
          </div>
        </div>
       
      </div>
    </div>
  </div>
  @include('admin.auth.login_footer')


