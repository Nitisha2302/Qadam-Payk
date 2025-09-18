<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $generatedPassword;
    public $logoPath;

    public function __construct(User $user, $generatedPassword,$logoPath)
    {
        
        $this->user = $user;
        $this->generatedPassword = $generatedPassword;
        $this->logoPath = $logoPath;
    }

    public function build()
    {
        // dd($this->user, $this->generatedPassword);
        return $this->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME')) // Get sender's name and email from .env
        ->subject('Gefeliciteerd! Uw accountgegevens')
        ->view('emails.admin_password')
        
        ->with([
            'user' => $this->user,
            'generatedPassword' => $this->generatedPassword, // Pass data to the viewm
            'logoPath'=>$this->logoPath
        ]);
    }
}
