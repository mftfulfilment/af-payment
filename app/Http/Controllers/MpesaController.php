<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MpesaController extends Controller
{

    public function stk_push(Request $request)
    {
        $shortcode = 174379;
        $phone = $request->phone;
        $passkey = 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919';
        $callback = env('APP_URL') . "/api/stk_response";
        $timestamp = Carbon::parse(Carbon::now()->toDateTimeString())->format('YmdHis');
        $password = $shortcode . $passkey . $timestamp;


        $phone = '254743895505';
        $data = [
            "BusinessShortCode" => $shortcode,
            "Password" => base64_encode($password),
            "Timestamp" => $timestamp,
            "TransactionType" => "CustomerPayBillOnline",
            "Amount" => 1,
            "PartyA" => $phone,
            "PartyB" => $shortcode,
            "PhoneNumber" => $phone,
            "CallBackURL" => $callback,
            "AccountReference" => "CompanyXLTD",
            "TransactionDesc" => 'Payment'
        ];
        $url = "https://sandbox.safaricom.co.ke/mpesa/stkpush/v2/processrequest";

        $response = Http::withHeaders([
            // 'Authorization' => 'Bearer ' . $this->stk_auth(),
            'content-type' => 'application/json'
        ])->post($url, $data);
        $data =  json_decode($response->getBody()->getContents());
        return $data;
    }
}
