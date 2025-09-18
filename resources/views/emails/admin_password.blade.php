
@include('emails.email-header')
  <div class="email-container">
    <div class="header">
     
      <img src="{{ $logoPath }}" alt="logo" />
    </div>
    <div class="content">
      <h2>Welkom bij HEWIE</h2>
      <p>Beste {{ $user->first_name }} {{ $user->last_name }},</p>
      <p>Je bent succesvol geregistreerd als medewerker bij HEWIE. Hieronder staan je accountgegevens:</p>
      <p><strong>E-mail:</strong> {{ $user->email }}</p>
      <p><strong>Wachtwoord:</strong> {{ $generatedPassword }}</p> <!-- This will display the password -->
      <p>Log in en verander je wachtwoord zo snel mogelijk om veiligheidsredenen.</p>
    
      <h3>Contact  us</h3>
      <p>Als je vragen hebt, neem dan contact met ons op:</p>
      <p>
        <strong>E-mail:</strong> <a href="#">{{ env('CONTACT_EMAIL') }}</a><br><br>
        <strong>Telefoon:</strong>{{ env('CONTACT_PHONE') }}<br><br>
        <strong>Adres:</strong> {{ env('CONTACT_ADDRESS') }}
      </p>
      
    </div>

@include('emails.email-footer')