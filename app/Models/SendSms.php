<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SendSms extends Model
{
    use HasFactory;

    
    function sendSMS($recipients, $message) {
        $account_sid = getenv("TWILIO_SID");
        $auth_token = getenv("TWILIO_AUTH_TOKEN");
        $twilio_number = getenv("TWILIO_SMS_NUMBER");

        $client = new Client($account_sid, $auth_token);

        $client->messages->create($recipients, 
            ['from' => $twilio_number, 'body' => $message] );
    }
}
