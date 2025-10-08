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
    | Default Network
    |--------------------------------------------------------------------------
    |
    | The default blockchain network to use for payments.
    | Options: ETH, BSC, Polygon, etc.
    |
    */
    'default_network' => env('EPPAY_DEFAULT_NETWORK', 'ETH'),

    /*
    |--------------------------------------------------------------------------
    | Default Currency
    |--------------------------------------------------------------------------
    |
    | The default cryptocurrency to use for payments.
    | Options: USDT, USDC, ETH, BNB, etc.
    |
    */
    'default_currency' => env('EPPAY_DEFAULT_CURRENCY', 'USDT'),

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
