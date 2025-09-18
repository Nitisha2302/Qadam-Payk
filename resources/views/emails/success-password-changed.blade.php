@include('emails.email-header')
  <div class="email-container">
        
    <div class="header">
      <img src="{{ $logoPath }}" alt="logo" width="130">
    </div>
    <div class="content">
      <h3>Gefeliciteerd</h3>
      <p>Hoi,{{ $user->name }}, </p>
      <p>Uw wachtwoord is succesvol gewijzigd.</p>
      <p>Als u deze wijziging niet hebt aangevraagd, neem dan onmiddellijk contact op met ons ondersteuningsteam.</p>
      <p>Blijf gezond,<br>Het Hewie-team</p>
    </div>
@include('emails.email-footer')

