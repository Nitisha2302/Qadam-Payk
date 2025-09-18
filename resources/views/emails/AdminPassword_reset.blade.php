@include('emails.email-header')
    <div class="email-container">
        <div class="header">
            <img src="" alt="HEWIE Logo" style="width: 200px;"/>
        </div>
        <h2>Wachtwoord opnieuw instellen</h2>
        <p>Hallo,</p>
        <p>We hebben een verzoek ontvangen om het wachtwoord voor je HEWIE account te resetten.</p>
        <p><a href="{{ $actionUrl }}" class="button">Wachtwoord opnieuw instellen</a></p>
        <p>Als je geen nieuw wachtwoord hebt aangevraagd, negeer dan deze e-mail.</p>
        <p>Bedankt,<br>HEWIE</p>
        @include('emails.email-footer')
    </div>

