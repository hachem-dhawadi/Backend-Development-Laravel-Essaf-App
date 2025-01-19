<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Nexmo\Laravel\Facade\Nexmo;
use Illuminate\Notifications\Messages\NexmoMessage;
use Illuminate\Notifications\Notification;

use Illuminate\Http\Request;


class NotificationController extends Controller
{
    public function smsnotification()
    {
       Nexmo::message()->send([
        'to'=>'+21626212515',
        'from'=>'E-Saff APP',
        'text'=>'Dear [Client Name],
        this is a AI E-Saff reminder , your turn for [service] at [name] is coming up soon. Please make sure to arrive on time and have any necessary documents or information with you. Thank you!.'
       ]);
       //echo "Message sent!";
      
    }
    
}
