<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VoiceApiController extends Controller
{
    public $username = "JdfOrg";
    public $apiKey = "98daee4b3ea05d311889507f74885a020ee2bb456d4a575cc7bb1d3386e55d8c";

    public function handleCallback(Request $request)
    {


        $isActive = $request->isActive;
        $sessionId = $request->sessionId;
        $direction = $request->direction;
        $callerNumber = $request->callerNumber;
        $destinationNumber = $request->destinationNumber;

        if ($isActive == 1) {

            $say_welcome_text = "Welcome to JDF Organisation Limited.Please wait while we transfer your call to the next available agent.This call may be recorded for internal training and quality purposes.";

            $response = '<?xml version="1.0" encoding="UTF-8"?>';
            $response .= '<Response>';
            $response .= '<Say voice="en-US-Wavenet-F">' . $say_welcome_text . '</Say>';
            $response .= '<Dial record="true" sequential="true" phoneNumbers="' . +254110666140 . '" />';
            $response .= '<Record trimSilence="true"></Record>';
            $response .= '</Response>';

            // Print the response onto the page so that our gateway can read it
            header('Content-type: application/xml');
            echo $response;
            exit();

        } else {

        }

    }

    public function handleEvent()
    {
        //
    }

    public function transferCall(Request $request)
    {
        //
    }

    public function dequeueCall(string $id)
    {
        //
    }

    public function generateToken(string $id)
    {
        //
    }

}
