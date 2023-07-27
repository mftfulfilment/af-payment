<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class VoiceApiController extends Controller
{
    public $username = "JdfOrg";
    public $apiKey = "98daee4b3ea05d311889507f74885a020ee2bb456d4a575cc7bb1d3386e55d8c";

    public function handleCallback(Request $request)
    {
        $isActive = $request->isActive;
        $direction = $request->direction;
        $dtmfDigits = $request->dtmfDigits;

        if ($isActive == 1) {
            if ($direction == 'Inbound') {
                $welcome_text = "Welcome to JDF Organisation.";
                $prompt_action = "To speak to one of our call representatives, press 1. To register a JDF account, press 2. To contribute to an organization, press 3. To exit, press 0";

                $response = '<?xml version="1.0" encoding="UTF-8"?><Response>';
                $response .= '<Say voice="en-US-Wavenet-F">' . htmlspecialchars($welcome_text) . '</Say>';
                $response .= '<Say voice="en-US-Wavenet-F">' . htmlspecialchars($prompt_action) . '</Say>';
                $response .= '</Response>';

                header('Content-type: application/xml');
                echo $response;

                switch ($dtmfDigits) {
                    case 1:
                        $connect_text = "Please wait while we transfer your call to the next available agent. This call may be recorded for internal training and quality purposes.";
                        $response = '<?xml version="1.0" encoding="UTF-8"?><Response>';
                        $response .= '<Say voice="en-US-Wavenet-F">' . htmlspecialchars($connect_text) . '</Say>';
                        $response .= '<Dial record="true" sequential="true" phoneNumbers="254743895505"/>';
                        $response .= '</Response>';

                        header('Content-type: application/xml');
                        echo $response;
                        break;
                    case 2:
                        $get_number_text = "Please enter your phone number in the international format for us to create an account for you.";
                        $response = '<?xml version="1.0" encoding="UTF-8"?><Response>';
                        $response .= '<Say voice="en-US-Wavenet-F">' . htmlspecialchars($get_number_text) . '</Say>';
                        $response .= '<Record trimSilence="true"></Record>';
                        $response .= '</Response>';

                        header('Content-type: application/xml');
                        echo $response;
                        break;
                    case 3:
                        $get_amount_text = "Please enter the donation amount.";
                        $response = '<?xml version="1.0" encoding="UTF-8"?><Response>';
                        $response .= '<Say voice="en-US-Wavenet-F">' . htmlspecialchars($get_amount_text) . '</Say>';
                        $response .= '<Record trimSilence="true"></Record>';
                        $response .= '</Response>';

                        header('Content-type: application/xml');
                        echo $response;
                        break;
                    default:
                        $goodbye_text = "Thank you for calling JDF organisation. Until next time, goodbye.";
                        $response = '<?xml version="1.0" encoding="UTF-8"?><Response>';
                        $response .= '<Say voice="en-US-Wavenet-F">' . htmlspecialchars($goodbye_text) . '</Say>';
                        $response .= '</Response>';

                        header('Content-type: application/xml');
                        echo $response;
                        break;
                }

                exit();
            } else {
                $response = '<?xml version="1.0" encoding="UTF-8"?><Response>';
                $response .= '<Dial record="true" sequential="true" phoneNumbers="' . htmlspecialchars($request->callerNumber) . '"/>';
                $response .= '</Response>';

                header('Content-type: application/xml');
                echo $response;
                exit();
            }
        } else {
            // ... Rest of the code handling the call history and agents ...
        }
    }

    // Rest of the code for handleEvent method...
}
