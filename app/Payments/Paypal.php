<?php

namespace Payments;

use Illuminate\Support\Facades\Http;

class Paypal
{
    public function paypal($amount)
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer A21AAL4Eqz1hpX8hpDnGJay_jhyrao5Y_7CNsL59uDQBdcPlKOPr-R1JUA7CwL0EtAFlEOfFj2E9GOu0Mk0r7sGLauabXTAcw',
        ])
            ->post('https://api-m.sandbox.paypal.com/v2/checkout/orders', [
                "intent" => "CAPTURE",
                "purchase_units" => [
                    [
                        "reference_id" => "d9f80740-38f0-11e8-b467-0ed5f89f718b",
                        "amount" => [
                            "currency_code" => "USD",
                            "value" => $amount,
                        ],
                        "shipping" => [
                            "address" => [
                                "address_line_1" => "123 Main Street",
                                "address_line_2" => "Apt 4B",
                                "admin_area_2" => "San Jose",
                                "admin_area_1" => "CA",
                                "postal_code" => "95131",
                                "country_code" => "US",
                            ],
                        ],
                    ],
                ],
                "payment_source" => [
                    "paypal" => [
                        "experience_context" => [
                            "payment_method_preference" => "IMMEDIATE_PAYMENT_REQUIRED",
                            "payment_method_selected" => "PAYPAL",
                            "brand_name" => "EXAMPLE INC",
                            "locale" => "en-US",
                            "landing_page" => "LOGIN",
                            "shipping_preference" => "SET_PROVIDED_ADDRESS",
                            "user_action" => "PAY_NOW",
                            "return_url" => "https://example.com/returnUrl",
                            "cancel_url" => "https://example.com/cancelUrl",
                        ],
                    ],
                ],
            ]);

    }
}
