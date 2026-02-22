<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'rupeerush' => [
        'mer_no' => env('RUPEERUSH_MER_NO'),
        'key' => env('RUPEERUSH_KEY'),
        'api_url' => env('RUPEERUSH_API_URL'),
        'pay_type' => env('RUPEERUSH_PAY_TYPE'),
        'bank_code' => env('RUPEERUSH_BANK_CODE'),
    ],

    'simplypay' => [
        'app_id' => env('SIMPLYPAY_APP_ID'),
        'app_secret' => env('SIMPLYPAY_APP_SECRET'),
        'api_url' => env('SIMPLYPAY_API_URL', 'https://api.simplypay.vip/api/v2/payment/order/create'),
    ],

    'watchpay' => [
        'mch_id' => env('WATCHPAY_MCH_ID'),
        'web_merchant_key' => env('WATCHPAY_WEB_MERCHANT_KEY'),
        'merchant_key' => env('WATCHPAY_MERCHANT_KEY'),
        'web_api_url' => env('WATCHPAY_WEB_API_URL', 'https://api.watchglb.com/pay/web'),
        'pay_type' => env('WATCHPAY_PAY_TYPE', '101'),
    ],

];
