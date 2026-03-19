<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WhatsApp Business Cloud API Settings
    |--------------------------------------------------------------------------
    */

    'token'               => env('WHATSAPP_TOKEN', ''),
    'phone_number_id'     => env('WHATSAPP_PHONE_NUMBER_ID', ''),
    'business_account_id' => env('WHATSAPP_BUSINESS_ACCOUNT_ID', ''),
    'verify_token'        => env('WHATSAPP_VERIFY_TOKEN', ''),
    'api_url'             => 'https://graph.facebook.com/v19.0',
];
