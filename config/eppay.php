<?php

return [
    /*
    |--------------------------------------------------------------------------
    | EpPay API Key
    |--------------------------------------------------------------------------
    |
    | Your EpPay API key from https://eppay.io/apis
    | Get your API key by registering at EpPay and creating an API key.
    |
    */
    'api_key' => env('EPPAY_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | EpPay Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL for EpPay API. Default is the production URL.
    | Change this only if you're using a different environment.
    |
    */
    'base_url' => env('EPPAY_BASE_URL', 'https://eppay.io'),

    /*
    |--------------------------------------------------------------------------
    | Default Beneficiary Address
    |--------------------------------------------------------------------------
    |
    | The default wallet address that will receive payments.
    | This can be overridden per payment if needed.
    |
    */
    'default_beneficiary' => env('EPPAY_DEFAULT_BENEFICIARY'),

    /*
    |--------------------------------------------------------------------------
    | Default Network RPC
    |--------------------------------------------------------------------------
    |
    | The default blockchain network RPC URL to use for payments.
    | Example: https://rpc.scimatic.net for Scimatic Network
    |
    */
    'default_rpc' => env('EPPAY_DEFAULT_RPC', 'https://rpc.scimatic.net'),

    /*
    |--------------------------------------------------------------------------
    | Default Token Address
    |--------------------------------------------------------------------------
    |
    | The default token contract address (USDT, USDC, etc.) on your network.
    | Example: 0x65C4A0dA0416d1262DbC04BeE524c804205B92e8 (USDT on Scimatic)
    |
    */
    'default_token' => env('EPPAY_DEFAULT_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Default Success Callback URL
    |--------------------------------------------------------------------------
    |
    | The default URL where the EpPay mobile app will send payment confirmation.
    | This should be a route in your app that handles payment success.
    |
    */
    'default_success_url' => env('EPPAY_DEFAULT_SUCCESS_URL'),

    /*
    |--------------------------------------------------------------------------
    | Payment Verification Polling Interval
    |--------------------------------------------------------------------------
    |
    | How often (in milliseconds) to check payment status on the frontend.
    | Default: 3000 (3 seconds)
    |
    */
    'polling_interval' => env('EPPAY_POLLING_INTERVAL', 3000),

    /*
    |--------------------------------------------------------------------------
    | Payment Timeout
    |--------------------------------------------------------------------------
    |
    | How long (in minutes) before a payment request expires.
    | Default: 30 minutes
    |
    */
    'payment_timeout' => env('EPPAY_PAYMENT_TIMEOUT', 30),
];
