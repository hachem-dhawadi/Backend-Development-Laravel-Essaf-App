<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class SendEmail extends Mailable
{
    use Queueable, SerializesModels;
    
    public $subject;

    public function __construct($subject)
    {
        $this->subject = $subject;
    }

    public function build()
    {
        return $this->subject('E-Saff')
                    ->view('emails.send'); // Create a corresponding blade view file
    }
}
