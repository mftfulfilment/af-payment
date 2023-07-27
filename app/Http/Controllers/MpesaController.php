<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MpesaController extends Controller
{
    public function stk_push($phone)
    {

        $amount = 1;
        $api_url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
        $passkey = 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919';
        $shortcode = 174379;
        $timestamp = date('YmdHis', strtotime('now'));
        $password = $this->password($shortcode . $passkey . $timestamp);
        $app_url = 'https://5e79-102-140-203-238.ngrok-free.app/api/callback';

        $token = $this->token();

        $response = Http::withHeaders([
            "Authorization" => "Bearer " . $token,
            "Content-Type" => "application/json",
        ])->post($api_url, [
            "BusinessShortCode" => $shortcode,
            "Password" => $password,
            "Timestamp" => $timestamp,
            "TransactionType" => "CustomerPayBillOnline",
            "Amount" => $amount,
            "PartyA" => $phone,
            "PartyB" => $shortcode,
            "PhoneNumber" => $phone,
            "CallBackURL" => $app_url,
            "AccountReference" => "CompanyXLTD",
            "TransactionDesc" => "Order payment"
        ]);

        echo $response->body();
    }

    public function password($string)
    {
        $encodedString = base64_encode($string);
        return $encodedString;
    }


    public function base_64()
    {
        $key = env('MPESA_KEY');
        $secret = env('MPESA_SECRET');
        $base64 = base64_encode($key . ':' . $secret);
        return $base64;
    }

    public function token()
    {
        // return  $this->base_64();
        $url = 'https://sandbox.safaricom.co.ke';

        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . $this->base_64(),
        ])->get($url . '/oauth/v1/generate?grant_type=client_credentials');

        // $token = $response->json()['access_token'];
        $data =  json_decode($response->getBody()->getContents());

        return $data->access_token;
    }
    public function callback(Request $request)
    {
        Log::debug($request->all());
    }
}
