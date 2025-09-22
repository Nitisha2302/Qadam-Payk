<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ForgotPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $randomPassword;
    public $name;
    public $logoPath;

    public function __construct($name, $randomPassword,$logoPath)
    {
        $this->name = $name;
        $this->randomPassword = $randomPassword;
        $this->logoPath = $logoPath;
    }

    public function build()
    {
        return $this->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME')) // Get sender's name and email from .env
                   ->subject('Your New Password')
                    ->view('emails.forgot_password')
                    ->with([
                        'name' => $this->name,
                        'password' => $this->randomPassword,
                        'logoPath'=>$this->logoPath
                    ]);

    }
}

