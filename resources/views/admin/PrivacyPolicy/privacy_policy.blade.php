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
                {!! $policy->title ?? 'Privacy Policy' !!}
            </h1>
        </div>

        <div class="qadam-policy-content">
            {!! $policy->content ?? '<p>No privacy policy found.</p>' !!}
        </div>
    </div>
</div>