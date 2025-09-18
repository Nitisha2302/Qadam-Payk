<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SuccessPasswordChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $logoPath;

    public function __construct($user, $logoPath)
    {
        $this->user = $user;
        $this->logoPath = $logoPath;
    }

    public function build()
    {
        return $this->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
                    ->subject('Uw wachtwoord is gewijzigd')
                    ->view('emails.success-password-changed')
                    ->with([
                        'user' => $this->user,
                        'logoPath' => $this->logoPath
                    ]);
    }
}
