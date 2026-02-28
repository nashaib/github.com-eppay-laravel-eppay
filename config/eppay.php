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
    | Default Network
    |--------------------------------------------------------------------------
    |
    | The default blockchain network slug to use for payments.
    | Examples: "bsc", "eth", "polygon", "arbitrum"
    | See https://eppay.io/docs/v2#supported-networks for all options.
    |
    */
    'default_network' => env('EPPAY_DEFAULT_NETWORK', 'bsc'),

    /*
    |--------------------------------------------------------------------------
    | Default Token Type
    |--------------------------------------------------------------------------
    |
    | The default token type for payments.
    | Supported: "USDT", "USDC"
    |
    */
    'default_token_type' => env('EPPAY_DEFAULT_TOKEN_TYPE', 'USDT'),

    /*
    |--------------------------------------------------------------------------
    | Success Callback URL
    |--------------------------------------------------------------------------
    |
    | The URL where EpPay sends a POST when payment is confirmed on-chain.
    | This is your server endpoint that handles payment completion.
    | The callback receives: payment_id, amount, tx_hash, from, token_type, network
    |
    */
    'success_url' => env('EPPAY_SUCCESS_URL'),

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
