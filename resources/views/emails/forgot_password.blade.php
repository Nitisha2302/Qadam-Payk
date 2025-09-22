
@include('emails.email-header')
  <div class="email-container">
        
    <div class="header">
      <img src="{{ $logoPath }}" alt="logo" width="130">
    </div>
    <div class="content">
      <h3>Uw nieuwe wachtwoord</h3>
      <p>Hoi, </p>
      <p>We hebben een verzoek ontvangen om je wachtwoord voor je Hewie-account opnieuw in te stellen. Als je dit verzoek niet hebt gedaan, kun je deze e-mail deze e-mail negeren.</p>
      <div class="otp">{{ $password }}</div>
      <p>Blijf gezond,<br>Het Hewie-team</p>
    </div>
    @include('emails.email-footer')
