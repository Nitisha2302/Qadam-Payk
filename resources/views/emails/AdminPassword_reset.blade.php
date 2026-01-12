@include('emails.email-header')
    <div class="email-container">
        <div class="header">
            <img src="" alt="QADAM Logo" style="width: 200px;"/>
        </div>
        <h2>Wachtwoord opnieuw instellen</h2>
        <p>Hallo,</p>
        <p>We hebben een verzoek ontvangen om het wachtwoord voor je QADAM account te resetten.</p>
        <p><a href="{{ $actionUrl }}" class="button">Wachtwoord opnieuw instellen</a></p>
        <p>Als je geen nieuw wachtwoord hebt aangevraagd, negeer dan deze e-mail.</p>
        <p>Bedankt,<br>QADAM</p>
        @include('emails.email-footer')
    </div>

