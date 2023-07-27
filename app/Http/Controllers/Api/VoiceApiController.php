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
        $direction = $request->direction;
        $callerNumber = $request->callerNumber;
        $destinationNumber = $request->destinationNumber;
        $dialedNumber = '25490662265';
        $dtmfDigits = $request->dtmfDigits;

        if ($isActive == 1) {
            if ($direction == 'Inbound') {

                $welcome_text = "Welcome to JDF Organisation.";
                $promp_action = "To speak to one of our call representative  press 1. To register a JDF account press 2.To contribute to an organisation press 3To exit press 0";

                $response = '<?xml version="1.0" encoding="UTF-8"?>';
                $response .= '<Response>';
                $response .= '<Say voice="en-US-Wavenet-F">' . $welcome_text . '</Say>';
                $response .= '<Say voice="en-US-Wavenet-F">' . $promp_action . '</Say>';
                $response .= '<Dial record="true" sequential="true" phoneNumbers="' . $dialedNumber . '"/>';

                $response .= '<Record trimSilence="true"></Record>';
                $response .= '</Response>';
                header('Content-type: application/xml');
                echo $response;
                exit();

                if ($dtmfDigits == 1) {
                    $connect_text = "Please wait while we transfer your call to the next available agent.This call may be recorded for internal training and quality purposes.";
                    $response = '<?xml version="1.0" encoding="UTF-8"?>';
                    $response .= '<Response>';
                    $response .= '<Say voice="en-US-Wavenet-F">' . $connect_text . '</Say>';
                    $response .= '<Dial record="true" sequential="true" phoneNumbers="' . $dialedNumber . '"/>';
                    $response .= '</Response>';
                    header('Content-type: application/xml');
                    echo $response;
                    exit();

                } else if ($dtmfDigits == 2) {
                    $get_number_text = "Please enter your phone number in the international format for us to create an account for you.";

                    $response = '<?xml version="1.0" encoding="UTF-8"?>';
                    $response .= '<Response>';
                    $response .= '<Say voice="en-US-Wavenet-F">' . $get_number_text . '</Say>';
                    $response .= '<Record trimSilence="true"></Record>';
                    $response .= '</Response>';
                    header('Content-type: application/xml');
                    echo $response;
                    exit();

                } else {
                    $goodbye_text = "Thank you for calling JDF organisation.Until next time it is a Goodbye";

                    $response = '<?xml version="1.0" encoding="UTF-8"?>';
                    $response .= '<Response>';
                    $response .= '<Say voice="en-US-Wavenet-F">' . $goodbye_text . '</Say>';
                    $response .= '</Response>';
                    header('Content-type: application/xml');
                    echo $response;
                    exit();
                }

            } else {

                $response = '<?xml version="1.0" encoding="UTF-8"?>';
                $response .= '<Response>';
                $response .= '<Dial record="true" sequential="true" phoneNumbers="' . $callerNumber . '"/>';
                $response .= '</Response>';

                header('Content-type: text/plain');
                echo $response;
                exit();

            }

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
