<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Go WhatsApp Gateway Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for interacting with the internal Go WhatsApp API Gateway.
    | In local or test environments, mock mode can be enabled to log messages
    | without performing actual HTTP requests.
    |
    */

    'enabled' => env('WHATSAPP_ENABLED', false),

    'gateway_url' => env('WHATSAPP_GATEWAY_URL', 'http://127.0.0.1:3000/send-message'),

    'api_token' => env('WHATSAPP_API_TOKEN', ''),

    'sender_number' => env('WHATSAPP_SENDER_NUMBER', ''),

    'mock_mode' => env('WHATSAPP_MOCK_MODE', true),

    'timeout' => (int) env('WHATSAPP_TIMEOUT_SECONDS', 5),
];

