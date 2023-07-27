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
        $callerNumber = $request->callerNumber;
        $sessionId = $request->sessionId;
        $destinationNumber = $request->destinationNumber;
        $dialedNumber = '254743895505';
        $dtmfDigits = $request->dtmfDigits;

        if ($isActive == 1) {
            if ($direction == 'Inbound') {

                $welcome_text = "Welcome to JDF Organisation.";
                $prompt_action = "To speak to one of our call representative  press 1. To register a JDF account press 2.To contribute to an organisation press 3.To exit press 0";

                $response = '<?xml version="1.0" encoding="UTF-8"?>';
                $response .= '<Dial record="true" sequential="true" phoneNumbers="' . $dialedNumber . '"/>';
                $response .= '<Response>';
                $response .= '<Say voice="en-US-Wavenet-F">' . $welcome_text . '</Say>';
                $response .= '<Say voice="en-US-Wavenet-F">' . $prompt_action . '</Say>';
                $response .= '</Response>';
                header('Content-type: application/xml');
                echo $response;


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

                }else if ($dtmfDigits == 3) {

                    $get_amount_text = "Please enter the dontation amount.";

                    $response = '<?xml version="1.0" encoding="UTF-8"?>';
                    $response .= '<Response>';
                    $response .= '<Say voice="en-US-Wavenet-F">' . $get_amount_text . '</Say>';
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

        }else {

            $recordingUrl = $request->recordingUrl;
            $durationInSeconds = $request->durationInSeconds;
            $currencyCode = $request->currencyCode;
            $amount = $request->amount;
            $hangupCause = $request->hangupCause;

            $call_history = DB::table('call_histories')
                ->where('sessionId', $sessionId)
                ->first();

            if($call_history){

                $update_call_history = DB::table('call_histories')
                    ->where('sessionId', $sessionId)
                    ->update([
                        'isActive' => $isActive,
                        'recordingUrl' => $recordingUrl,
                        'durationInSeconds' => $durationInSeconds,
                        'currencyCode' => $currencyCode,
                        'amount' => $amount,
                        'hangupCause' => $hangupCause,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);


                    DB::table('call_agents')
                    ->where('sessionId', $sessionId)
                    ->update([
                        'status' => 'available',
                        'sessionId' => null,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);

            }


        }

    }

    public function handleEvent(Request $request){


        $callSessionState = $request->callSessionState;
        if($callSessionState == 'Transferred' || $callSessionState == 'TransferCompleted'){

            $callTransferredToNumber = $request->callTransferredToNumber;
            $sessionId = $request->sessionId;

            $call_history = DB::table('call_histories')
                ->where('isActive', 1)
                ->where('sessionId', $sessionId)
                ->where('deleted_at', null)
                ->first();

            if($call_history){

                // Update current agent
                $update_call_agent = DB::table('call_agents')
                    ->where('admin_id', $call_history->adminId)
                    ->update([
                        'status' => 'busy',
                        'sessionId' => $sessionId,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);

                $call_agent = DB::table('call_agents')
                    ->where('client_name', substr($callTransferredToNumber, strpos($callTransferredToNumber, ".") + 1))
                    ->where('deleted_at', null)
                    ->first();

                if($call_agent){

                    // Update next agent
                    $update_call_agent = DB::table('call_agents')
                        ->where('id', $call_agent->id)
                        ->update([
                            'status' => 'busy',
                            'sessionId' => $sessionId,
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                }
            }

        }elseif ($callSessionState == 'Active'){

            if($request->has('callTransferState')){

                $callTransferState = $request->callTransferState;
                if($callTransferState == 'CallerHangup'){

                    $sessionId = $request->sessionId;
                    $call_history = DB::table('call_histories')
                        ->where('isActive', 1)
                        ->where('sessionId', $sessionId)
                        ->where('deleted_at', null)
                        ->first();

                    if($call_history){

                        // Update current agent
                        $update_call_agent = DB::table('call_agents')
                            ->where('admin_id', $call_history->adminId)
                            ->update([
                                'status' => 'available',
                                'sessionId' => $sessionId,
                                'updated_at' => date('Y-m-d H:i:s'),
                            ]);

                        if($update_call_agent){

                            $call_agent = DB::table('call_agents')
                                ->where('sessionId', $sessionId)
                                ->where('deleted_at', null)
                                ->first();

                            if($call_agent){

                                $update_call_history = DB::table('call_histories')
                                    ->where('id', $call_history->id)
                                    ->update([
                                        'adminId' => $call_agent->admin_id,
                                        'agentId' => $call_agent->client_name,
                                        'nextCallStep' => 'in_progress',
                                        'updated_at' => date('Y-m-d H:i:s')
                                    ]);

                            }
                        }

                    }

                }elseif ($callTransferState == 'CalleeHangup'){

                    $sessionId = $request->sessionId;
                    $call_history = DB::table('call_histories')
                        ->where('isActive', 1)
                        ->where('sessionId', $sessionId)
                        ->where('deleted_at', null)
                        ->first();

                    if($call_history){

                        $call_agent = DB::table('call_agents')
                            ->where('sessionId', $sessionId)
                            ->where('deleted_at', null)
                            ->first();

                        if($call_agent){

                            if($call_history->agentId != $call_agent->client_name){

                                $update_call_agent = DB::table('call_agents')
                                    ->where('id', $call_agent->id)
                                    ->update([
                                        'status' => 'available',
                                        'sessionId' => '',
                                        'updated_at' => date('Y-m-d H:i:s'),
                                    ]);
                            }

                        }

                    }

                }
            }
        }

    }





}
