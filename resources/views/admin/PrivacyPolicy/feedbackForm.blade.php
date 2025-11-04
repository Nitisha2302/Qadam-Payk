@include('admin.PrivacyPolicy.privacyHeader')

<div class="qadam-privacy-wrapper p-0">
    <div class="container qadam-policy-container">
        <div class="text-center qadam-policy-head">
            <div class="container-xl">
                <div class="qadam-logo">
                    <img src="{{ asset('assets/admin/images/qadampayk-dash.png') }}" alt="QadamPayk Logo">
                </div>
            </div>
            <h1 style="text-align:center; font-size:36px; font-weight:700; color:#008955; margin-bottom:30px;">
                Feedback Form
            </h1>
        </div>

        <div class="qadam-policy-content" style="max-width: 600px; margin: 0 auto;">
            @if (session('success'))
                <div class="alert alert-success text-center" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            <form action="" method="POST" style="text-align:left;">
                @csrf

                <div class="form-group mb-4">
                    <label for="email" style="font-weight:600;">Email</label>
                    <input type="email" name="email" id="email" class="form-control" 
                           placeholder="Enter your email" required>
                    @error('email')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group mb-4">
                    <label for="description" style="font-weight:600;">Description</label>
                    <textarea name="description" id="description" rows="5" class="form-control" 
                              placeholder="Write your feedback here..." required></textarea>
                    @error('description')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-success" 
                            style="background-color:#008955; border:none; padding:10px 30px; font-weight:600;">
                        Submit Feedback
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>