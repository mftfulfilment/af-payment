<?php

namespace App\Http\Controllers;

use App\Exceptions\AfricasTalkingGatewayException;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AfricasTalkingGateway extends Controller
{
    protected $_username;
    protected $_apiKey;

    protected $_requestBody;
    protected $_requestUrl;

    protected $_responseBody = '';
    protected $_responseInfo;
    protected $_environment;

    //Turn this on if you run into problems. It will print the raw HTTP response from our server
    const Debug             = false;

    const HTTP_CODE_OK      = 200;
    const HTTP_CODE_CREATED = 201;

    public function __construct()
    {
        $this->_apiKey       = env('AFRICAS_TALKING_API_KEY');
        $this->_username       = env('AFRICAS_TALKING_USER_NAME');

        $this->_environment  = env('APP_ENV');

        $this->_requestBody  = null;
        $this->_requestUrl   = null;

        $this->_responseBody = null;
        $this->_responseInfo = null;
    }


    //Messaging methods
    public function sendMessage($to_, $message_, $from_ = null, $bulkSMSMode_ = 1, array $options_ = array())
    {
        if (strlen($to_) == 0 || strlen($message_) == 0) {
            throw new AfricasTalkingGatewayException('Please supply both to and message parameters');
        }

        $params = array(
            'username' => $this->_username,
            'to'       => $to_,
            'message'  => $message_,
        );

        if ($from_ !== null) {
            $params['from']        = $from_;
            $params['bulkSMSMode'] = $bulkSMSMode_;
        }

        //This contains a list of parameters that can be passed in $options_ parameter
        if (count($options_) > 0) {
            $allowedKeys = array(
                'enqueue',
                'keyword',
                'linkId',
                'retryDurationInHours'
            );

            //Check whether data has been passed in options_ parameter
            foreach ($options_ as $key => $value) {
                if (in_array($key, $allowedKeys) && strlen($value) > 0) {
                    $params[$key] = $value;
                } else {
                    throw new AfricasTalkingGatewayException("Invalid key in options array: [$key]");
                }
            }
        }

        $this->_requestUrl  = $this->getSendSmsUrl();
        $this->_requestBody = http_build_query($params, '', '&');

        $this->executePOST();
        $this->_responseInfo['http_code'];
        if ($this->_responseInfo['http_code'] == self::HTTP_CODE_CREATED) {
            $responseObject = json_decode($this->_responseBody);
            if (count($responseObject->SMSMessageData->Recipients) > 0)
                return $responseObject->SMSMessageData->Recipients;

            throw new AfricasTalkingGatewayException($responseObject->SMSMessageData->Message);
        }

        throw new AfricasTalkingGatewayException('Something went wrong');
    }


    public function fetchMessages($lastReceivedId_)
    {
        $username = $this->_username;
        $this->_requestUrl = $this->getSendSmsUrl() . '?username=' . $username . '&lastReceivedId=' . intval($lastReceivedId_);

        $this->executeGet();

        if ($this->_responseInfo['http_code'] == self::HTTP_CODE_OK) {
            $responseObject = json_decode($this->_responseBody);
            return $responseObject->SMSMessageData->Messages;
        }

        throw new AfricasTalkingGatewayException('Something went wrong');
    }


    //Subscription methods
    public function createSubscription($phoneNumber_, $shortCode_, $keyword_)
    {

        if (strlen($phoneNumber_) == 0 || strlen($shortCode_) == 0 || strlen($keyword_) == 0) {
            throw new AfricasTalkingGatewayException('Please supply phoneNumber, shortCode and keyword');
        }

        $params = array(
            'username'    => $this->_username,
            'phoneNumber' => $phoneNumber_,
            'shortCode'   => $shortCode_,
            'keyword'     => $keyword_
        );

        $this->_requestUrl  = $this->getSubscriptionUrl("/create");
        $this->_requestBody = http_build_query($params, '', '&');

        $this->executePOST();

        if ($this->_responseInfo['http_code'] != self::HTTP_CODE_CREATED)
            throw new AfricasTalkingGatewayException('Something went wrong');

        return json_decode($this->_responseBody);
    }

    public function deleteSubscription($phoneNumber_, $shortCode_, $keyword_)
    {
        if (strlen($phoneNumber_) == 0 || strlen($shortCode_) == 0 || strlen($keyword_) == 0) {
            throw new AfricasTalkingGatewayException('Please supply phoneNumber, shortCode and keyword');
        }

        $params = array(
            'username'    => $this->_username,
            'phoneNumber' => $phoneNumber_,
            'shortCode'   => $shortCode_,
            'keyword'     => $keyword_
        );

        $this->_requestUrl  = $this->getSubscriptionUrl("/delete");
        $this->_requestBody = http_build_query($params, '', '&');

        $this->executePOST();

        if ($this->_responseInfo['http_code'] != self::HTTP_CODE_CREATED)
            throw new AfricasTalkingGatewayException('Something went wrong');

        return json_decode($this->_responseBody);
    }

    public function fetchPremiumSubscriptions($shortCode_, $keyword_, $lastReceivedId_ = 0)
    {
        $params  = '?username=' . $this->_username . '&shortCode=' . $shortCode_;
        $params .= '&keyword=' . $keyword_ . '&lastReceivedId=' . intval($lastReceivedId_);
        $this->_requestUrl  = $this->getSubscriptionUrl($params);

        $this->executeGet();

        if ($this->_responseInfo['http_code'] == self::HTTP_CODE_OK) {
            $responseObject = json_decode($this->_responseBody);
            return $responseObject->responses;
        }

        throw new AfricasTalkingGatewayException('Something went wrong');
    }


    //Call methods
    public function call($from_, $to_)
    {
        if (strlen($from_) == 0 || strlen($to_) == 0) {
            throw new AfricasTalkingGatewayException('Please supply both from and to parameters');
        }

        $params = array(
            'username' => $this->_username,
            'from'     => $from_,
            'to'       => $to_
        );

        $this->_requestUrl  = $this->getVoiceUrl() . "/call";
        $this->_requestBody = http_build_query($params, '', '&');

        $this->executePOST();

        if (($responseObject = json_decode($this->_responseBody)) !== null) {
            if (strtoupper(trim($responseObject->errorMessage)) == "NONE") {
                return $responseObject->entries;
            }
            throw new AfricasTalkingGatewayException($responseObject->errorMessage);
        } else
            throw new AfricasTalkingGatewayException('Something went wrong');
    }

    public function getNumQueuedCalls($phoneNumber_, $queueName = null)
    {
        $this->_requestUrl = $this->getVoiceUrl() . "/queueStatus";
        $params = array(
            "username"     => $this->_username,
            "phoneNumbers" => $phoneNumber_
        );
        if ($queueName !== null)
            $params['queueName'] = $queueName;
        $this->_requestBody   = http_build_query($params, '', '&');
        $this->executePOST();

        if (($responseObject = json_decode($this->_responseBody)) !== null) {
            if (strtoupper(trim($responseObject->errorMessage)) == "NONE")
                return $responseObject->entries;
            throw new AfricasTalkingGatewayException($responseObject->ErrorMessage);
        }

        throw new AfricasTalkingGatewayException('Something went wrong');
    }


    public function uploadMediaFile($url_)
    {
        $params = array(
            "username" => $this->_username,
            "url"      => $url_
        );

        $this->_requestBody = http_build_query($params, '', '&');
        $this->_requestUrl  = $this->getVoiceUrl() . "/mediaUpload";

        $this->executePOST();

        if (($responseObject = json_decode($this->_responseBody)) !== null) {
            if (strtoupper(trim($responseObject->errorMessage)) != "NONE")
                throw new AfricasTalkingGatewayException($responseObject->errorMessage);
        } else
            throw new AfricasTalkingGatewayException('Something went wrong');
    }


    //Airtime method
    public function sendAirtime($recipients)
    {
        $params = array(
            "username"    => $this->_username,
            "recipients"  => $recipients
        );
        $this->_requestUrl  = $this->getAirtimeUrl("/send");
        $this->_requestBody = http_build_query($params, '', '&');

        $this->executePOST();

        if ($this->_responseInfo['http_code'] == self::HTTP_CODE_CREATED) {
            $responseObject = json_decode($this->_responseBody);
            if (count($responseObject->responses) > 0)
                return $responseObject->responses;

            throw new AfricasTalkingGatewayException($responseObject->errorMessage);
        }

        throw new AfricasTalkingGatewayException('Something went wrong');
    }

    // Payments
    public function initiateMobilePaymentCheckout(
        $productName_,
        $phoneNumber_,
        $currencyCode_,
        $amount_,
        $metadata_
    ) {
        $this->_requestBody = json_encode(array(
            "username"     => $this->_username,
            "productName"  => $productName_,
            "phoneNumber"  => $phoneNumber_,
            "currencyCode" => $currencyCode_,
            "amount"       => $amount_,
            "metadata"     => $metadata_
        ));
        $this->_requestUrl  = $this->getMobilePaymentCheckoutUrl();

        $this->executeJsonPOST();
        if ($this->_responseInfo['http_code'] == self::HTTP_CODE_CREATED) {
            $response = json_decode($this->_responseBody);
            if ($response->status == "PendingConfirmation") return $response->transactionId;
            else throw new AfricasTalkingGatewayException($response->description);
        }
        throw new AfricasTalkingGatewayException('Something went wrong');
    }

    public function mobilePaymentB2CRequest(
        $productName_,
        $recipients_
    ) {
        $this->_requestBody = json_encode(array(
            "username"     => $this->_username,
            "productName"  => $productName_,
            "recipients"   => $recipients_
        ));
        $this->_requestUrl  = $this->getMobilePaymentB2CUrl();

        $this->executeJsonPOST();
        if ($this->_responseInfo['http_code'] == self::HTTP_CODE_CREATED) {
            $response = json_decode($this->_responseBody);
            $entries  = $response->entries;
            if (count($entries) > 0) return  $entries;
            else throw new AfricasTalkingGatewayException($response->errorMessage);
        }
        throw new AfricasTalkingGatewayException('Something went wrong');
    }

    public function mobilePaymentB2BRequest($productName_, $providerData_, $currencyCode_, $amount_, $metadata_)
    {
        if (!isset($providerData_['provider']) || strlen($providerData_['provider']) == 0)
            throw new AfricasTalkingGatewayException("Missing field provider");

        if (!isset($providerData_['destinationChannel']) || strlen($providerData_['destinationChannel']) == 0)
            throw new AfricasTalkingGatewayException("Missing field destinationChannel");

        if (!isset($providerData_['destinationAccount']) || strlen($providerData_['destinationAccount']) == 0)
            throw new AfricasTalkingGatewayException("Missing field destinationAccount");

        if (!isset($providerData_['transferType']) || strlen($providerData_['transferType']) == 0)
            throw new AfricasTalkingGatewayException("Missing field transferType");

        $params = array(
            "username" => $this->_username,
            "productName"  => $productName_,
            "currencyCode" => $currencyCode_,
            "amount" => $amount_,
            'provider' => $providerData_['provider'],
            'destinationChannel' => $providerData_['destinationChannel'],
            'destinationAccount' => $providerData_['destinationAccount'],
            'transferType' => $providerData_['transferType'],
            'metadata' => $metadata_
        );

        $this->_requestBody = json_encode($params);
        $this->_requestUrl  = $this->getMobilePaymentB2BUrl();

        $this->executeJsonPOST();
        if ($this->_responseInfo['http_code'] == self::HTTP_CODE_CREATED) {
            $response = json_decode($this->_responseBody);
            return $response;
        }
        throw new AfricasTalkingGatewayException('Something went wrong');
    }

    //User info method
    public function getUserData()
    {
        $username = $this->_username;
        $this->_requestUrl = $this->getUserDataUrl('?username=' . $username);
        $this->executeGet();

        if ($this->_responseInfo['http_code'] == self::HTTP_CODE_OK) {
            $responseObject = json_decode($this->_responseBody);
            return $responseObject->UserData;
        }

        throw new AfricasTalkingGatewayException('Something went wrong');
    }

    private function executeGet()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: application/json',
            'apikey: ' . $this->_apiKey
        ));
        $this->doExecute($ch);
    }

    private function executePost()
    {


        // $response = Http::post($this->_requestUrl, [
        //     'name' => 'Steve',
        //     'role' => 'Network Administrator',
        // ]);
        // dd($this->_requestBody, $this->_apiKey);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->_requestBody);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: application/json',
            'apikey: ' . $this->_apiKey
        ));

        $this->doExecute($ch);
    }

    private function executeJsonPost()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->_requestBody);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($this->_requestBody),
            'apikey: ' . $this->_apiKey
        ));
        $this->doExecute($ch);
    }

    private function doExecute(&$curlHandle_)
    {
        try {

            $this->setCurlOpts($curlHandle_);
            $responseBody = curl_exec($curlHandle_);

            if (self::Debug) {
                echo "Full response: " . print_r($responseBody, true) . "\n";
            }

            $this->_responseInfo = curl_getinfo($curlHandle_);

            $this->_responseBody = $responseBody;
            curl_close($curlHandle_);
        } catch (Exception $e) {
            curl_close($curlHandle_);
            throw $e;
        }
    }

    public function event(Request $request)
    {
        Log::debug('**Event**');

        Log::debug($request->all());
        Log::debug('**Event**');
    }

    public function ussd(Request $request)
    {
        Log::debug('**Ussd**');
        Log::debug($request->all());
        Log::debug('**Ussd**');
        // Read the variables sent via POST from our API
        $sessionId   = $request->sessionId;
        $serviceCode = $request->serviceCode;
        $phoneNumber = $request->phoneNumber;
        $text        = $request->text;

        // Check if user exists
        $user = User::where('phone', $phoneNumber)->first();

        // Initialize the response
        $response = '';

        // $text_1 = str_replace("'", '');

        $text_1 = explode('*', $text);

        Log::debug('text_1');
        Log::debug($text_1);
        Log::debug('text_1');

        if (!$user) {
            // User is not registered, prompt for registration
            if ($text == "") {
                // This is the first request. Note how we start the response with CON
                $response = "CON Register for an account \n";
                $response .= "0. Register";
            } else if ($text == "0") {
                // Perform user registration here and store their details in the database
                // For example:
                // $response = "END Your account number is ".$accountNumber;

                User::create(['phone' => $phoneNumber]);

                $response = "END Your has been created";

                // Registration successful, show donation options
                // $response = "CON Registration successful! Choose an organization to donate to: \n";
                // $response .= "1. Organization A \n";
                // $response .= "2. Organization B \n";
                // Add more organizations as needed
            }
        } else {
            // User is registered, show donation options
            if ($text == "") {
                // This is the first request. Note how we start the response with CON
                $response = "CON Choose an organization to donate to: \n";
                $response .= "1. Organization A \n";
                $response .= "2. Organization B \n";
                // Add more organizations as needed
            } else if ($text == "1") {
                // User selected an organization, prompt for donation amount
                $organization = ($text == '1') ? 'Organization A' : 'Organization B';
                // Add more organizations as needed

                $response = "CON Enter the donation amount for {$organization}:";
            } else if ($text == "2") {
                // User selected an organization, prompt for donation amount
                $organization = 'Organization A';
                // Add more organizations as needed

                $response = "CON Enter the donation amount for {$organization}:";
            }else if ($text == "3") {
                // User selected an organization, prompt for donation amount
                $organization = 'Organization C';
                // Add more organizations as needed

                $response = "CON Enter the donation amount for {$organization}:";
            }else if ('1*' . $text_1[1]){
                $donationAmount = $text_1[1];
                $response = "END Thank you for donating {$donationAmount}! You will receive an M-pesa STK push shortly";
                $message = 'Thank you for donating ' . $donationAmount . '! You will receive an M-pesa STK push shortly';
                $this->sendMessage($phoneNumber, $message);
            }

        }

        // Echo the response back to the API
        return response($response)->header('Content-type', 'text/plain');
    }


    private function setCurlOpts(&$curlHandle_)
    {
        curl_setopt($curlHandle_, CURLOPT_TIMEOUT, 60);
        curl_setopt($curlHandle_, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curlHandle_, CURLOPT_URL, $this->_requestUrl);
        curl_setopt($curlHandle_, CURLOPT_RETURNTRANSFER, true);
    }


    private function getApiHost()
    {
        return ($this->_environment == 'local') ? 'https://api.sandbox.africastalking.com' : 'https://api.africastalking.com';
    }

    private function getPaymentHost()
    {
        return ($this->_environment == 'local') ? 'https://payments.sandbox.africastalking.com' : 'https://payments.africastalking.com';
    }

    private function getVoiceHost()
    {
        return ($this->_environment == 'local') ? 'https://voice.sandbox.africastalking.com' : 'https://voice.africastalking.com';
    }

    private function getSendSmsUrl($extension_ = "")
    {
        return $this->getApiHost() . '/version1/messaging' . $extension_;
    }

    private function getVoiceUrl()
    {
        return $this->getVoiceHost();
    }

    private function getUserDataUrl($extension_)
    {
        return $this->getApiHost() . '/version1/user' . $extension_;
    }

    private function getSubscriptionUrl($extension_)
    {
        return $this->getApiHost() . '/version1/subscription' . $extension_;
    }

    private function getAirtimeUrl($extension_)
    {
        return $this->getApiHost() . '/version1/airtime' . $extension_;
    }

    private function getMobilePaymentCheckoutUrl()
    {
        return $this->getPaymentHost() . '/mobile/checkout/request';
    }

    private function getMobilePaymentB2CUrl()
    {
        return $this->getPaymentHost() . '/mobile/b2c/request';
    }

    private function getMobilePaymentB2BUrl()
    {
        return $this->getPaymentHost() . '/mobile/b2b/request';
    }
}
