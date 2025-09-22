<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OTPVerificationMail extends Mailable
{
    use Queueable, SerializesModels;
    public $name;
    public $otp;
    public $logoPath;

    /**
     * Create a new message instance.
     *
     * @param $otp
     */
    public function __construct($name,$otp,$logoPath)
    {
        $this->name = $name;
        $this->otp = $otp;
        $this->logoPath = $logoPath;
       
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME')) // Get sender's name and email from .env
                    ->subject('Congratulations! Your OTP for email verfiaction')
                    ->view('emails.otp-verification')
                    ->with([
                        'name' => $this->name,
                        'otp' => $this->otp,
                        'logoPath'=>$this->logoPath
                    ]);
    }
}
