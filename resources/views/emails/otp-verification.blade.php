
@include('emails.email-header')
  <div class="email-container">
        
    <div class="header">
      
      <img src="{{ $logoPath }}" alt="logo" width="130">

    </div>
    <div class="content">
      <!-- <h1></h1> -->
      <h3>Uw login OTP</h3>
      <p>Hoi,</p>
      <p>Uw eenmalige wachtwoord (OTP) om in te loggen op uw Hewie-account is:</p>
      <div class="otp">{{$otp}}</div>
      <p>Deze OTP is de komende 5 minuten geldig.</p>
      <p>Als je hier niet om hebt gevraagd, negeer deze e-mail dan of neem onmiddellijk contact op met ons ondersteuningsteam.</p>
      <p>Blijf gezond,<br>Het Hewie-team</p>
    </div>
    @include('emails.email-footer')

