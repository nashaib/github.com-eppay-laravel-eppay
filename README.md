# EpPay Laravel Package

[![Latest Version](https://img.shields.io/packagist/v/eppay/laravel-eppay.svg)](https://packagist.org/packages/eppay/laravel-eppay)
[![License](https://img.shields.io/packagist/l/eppay/laravel-eppay.svg)](https://packagist.org/packages/eppay/laravel-eppay)

A simple and elegant Laravel package for integrating EpPay cryptocurrency payments into your Laravel application. Accept crypto payments with just a few lines of code!

## Features

- Easy installation via Composer
- Simple `.env` configuration
- Beautiful pre-built QR code payment component
- Automatic payment status verification
- Real-time payment polling
- Built-in QR code generation (no external services)
- Support for multiple blockchain networks (ETH, BSC, Polygon, etc.)
- Support for multiple cryptocurrencies (USDT, USDC, ETH, BNB, etc.)
- Payment verification middleware
- Fully customizable Blade components
- Laravel 10, 11 & 12 compatible

## Requirements

- PHP 8.2 or higher
- Laravel 10.0 or higher (including Laravel 12)
- An EpPay API key ([Get one here](https://eppay.io/apis))

## Installation

### Step 1: Install via Composer

```bash
composer require eppay/laravel-eppay
```

### Step 2: Publish Configuration (Optional)

```bash
php artisan vendor:publish --tag=eppay-config
```

This will create a `config/eppay.php` file where you can customize settings.

### Step 3: Configure Your Environment

Add the following to your `.env` file:

```env
EPPAY_API_KEY=your_api_key_here
EPPAY_BASE_URL=https://eppay.io
EPPAY_DEFAULT_BENEFICIARY=0xYourWalletAddress
EPPAY_DEFAULT_RPC=https://rpc.scimatic.net
EPPAY_DEFAULT_TOKEN=0xYourTokenContractAddress
```

That's it! You're ready to start accepting crypto payments.

## Quick Start

### Basic Usage in Controller

```php
<?php

namespace App\Http\Controllers;

use EpPay\LaravelEpPay\Facades\EpPay;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function createPayment(Request $request)
    {
        // Generate a payment request using .env defaults
        $payment = EpPay::generatePayment(amount: 100.00);

        // Or specify all parameters explicitly
        // $payment = EpPay::generatePayment(
        //     amount: 100.00,
        //     to: '0xBeneficiaryWallet',    // Wallet to receive payment
        //     rpc: 'https://rpc.url',       // Blockchain RPC URL
        //     token: '0xTokenContract',      // Token contract address
        //     successUrl: 'https://eppay.io/payment-success'
        // );

        return view('payments.show', [
            'paymentId' => $payment['payment_id'],
            'paymentData' => $payment,
        ]);
    }

    public function verifyPayment($paymentId)
    {
        // Check if payment is completed (returns boolean)
        $isCompleted = EpPay::isPaymentCompleted($paymentId);

        if ($isCompleted) {
            return redirect()->route('order.success');
        }

        return back()->with('error', 'Payment not completed yet');
    }
}
```

### Display Payment QR Code in Blade

Simply use the pre-built Blade component:

```blade
<!-- resources/views/payments/show.blade.php -->

<x-eppay-payment-qr
    :payment-id="$paymentId"
    :payment-data="$paymentData"
    :auto-refresh="true"
    success-url="{{ route('order.success') }}"
    cancel-url="{{ route('order.cancel') }}"
/>
```

The component includes:
- QR code display (SVG, generated locally)
- Automatic payment status checking (every 3 seconds)
- Payment completion detection
- Redirect on successful payment
- Loading states and error handling
- Mobile-responsive design

### Routes Example

```php
// routes/web.php

use App\Http\Controllers\PaymentController;

Route::get('/payment/create', [PaymentController::class, 'createPayment'])
    ->name('payment.create');

Route::get('/payment/{paymentId}', [PaymentController::class, 'showPayment'])
    ->name('payment.show');

Route::get('/payment/{paymentId}/verify', [PaymentController::class, 'verifyPayment'])
    ->name('payment.verify');
```

> **Note:** The package auto-registers a route at `GET /eppay/verify/{paymentId}` used by the QR component for status polling.

## Advanced Usage

### Using the Facade

The `EpPay` facade provides several methods:

```php
use EpPay\LaravelEpPay\Facades\EpPay;

// Generate a payment (uses .env defaults for to/rpc/token)
$payment = EpPay::generatePayment(50.00);

// Generate with explicit parameters
$payment = EpPay::generatePayment(50.00, $beneficiary, $rpc, $token, $successUrl);

// Verify payment status (returns full status array)
$status = EpPay::verifyPayment('payment_id_here');

// Check if payment is completed (returns boolean)
$isCompleted = EpPay::isPaymentCompleted('payment_id_here');

// Get payment details
$details = EpPay::getPaymentDetails('payment_id_here');

// Get QR code data string (format: product=uuideppay&id={paymentId})
$qrData = EpPay::getQrCodeData('payment_id_here');

// Get QR code as base64 SVG data URL
$qrUrl = EpPay::getQrCodeUrl('payment_id_here');

// Get payment page URL
$pageUrl = EpPay::getPaymentUrl('payment_id_here');
```

### Payment Verification Middleware

Protect routes that require completed payments.

Register the middleware in `bootstrap/app.php` (Laravel 11+):

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'eppay.verify' => \EpPay\LaravelEpPay\Middleware\VerifyEpPayPayment::class,
    ]);
})
```

Use in routes:

```php
// Only allow access if payment is completed
Route::get('/download/{payment_id}', function (Request $request) {
    // Payment details are automatically added to the request
    $paymentDetails = $request->input('payment_details');

    return response()->download('path/to/file.pdf');
})->middleware('eppay.verify:payment_id');
```

### Custom Payment Component

If you want to customize the payment UI, publish the views:

```bash
php artisan vendor:publish --tag=eppay-views
```

Then edit the published view at `resources/views/vendor/eppay/components/payment-qr.blade.php`.

### Handling Payment Events

Listen for payment completion in your JavaScript:

```javascript
// The component dispatches a 'payment-completed' event
document.addEventListener('payment-completed', (event) => {
    console.log('Payment completed!', event.detail);
    // event.detail contains: { paymentId, data }

    window.location.href = '/success';
});
```

### Custom Polling Interval

Change how often payment status is checked:

```env
# Check every 5 seconds (5000 milliseconds)
EPPAY_POLLING_INTERVAL=5000
```

## Complete Example

Here's a full example of integrating EpPay into an e-commerce checkout:

```php
// app/Http/Controllers/CheckoutController.php

namespace App\Http\Controllers;

use App\Models\Order;
use EpPay\LaravelEpPay\Facades\EpPay;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function processCheckout(Request $request)
    {
        // Create order
        $order = Order::create([
            'user_id' => auth()->id(),
            'total' => $request->total,
            'status' => 'pending',
        ]);

        // Generate payment (uses .env defaults for beneficiary/rpc/token)
        $payment = EpPay::generatePayment(amount: $order->total);

        // Save payment ID to order
        $order->update([
            'payment_id' => $payment['payment_id'],
        ]);

        return view('checkout.payment', [
            'order' => $order,
            'paymentId' => $payment['payment_id'],
            'paymentData' => $payment,
        ]);
    }

    public function paymentSuccess($orderId)
    {
        $order = Order::findOrFail($orderId);

        // Verify payment is actually completed
        if (EpPay::isPaymentCompleted($order->payment_id)) {
            $order->update(['status' => 'paid']);

            return view('checkout.success', compact('order'));
        }

        return redirect()->route('checkout.show', $order)
            ->with('error', 'Payment not verified');
    }
}
```

```blade
<!-- resources/views/checkout/payment.blade.php -->

@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Complete Your Payment</h1>

    <div class="grid md:grid-cols-2 gap-8">
        <!-- Order Summary -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Order Summary</h2>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span>Order #{{ $order->id }}</span>
                    <span class="font-semibold">${{ $order->total }}</span>
                </div>
            </div>
        </div>

        <!-- Payment QR -->
        <div>
            <x-eppay-payment-qr
                :payment-id="$paymentId"
                :payment-data="$paymentData"
                :auto-refresh="true"
                :success-url="route('checkout.success', $order->id)"
                :cancel-url="route('checkout.cancel', $order->id)"
            />
        </div>
    </div>
</div>
@endsection
```

## How Payment Flow Works

1. Your app calls `EpPay::generatePayment()` to create a payment
2. Display the QR code using the `<x-eppay-payment-qr>` component
3. User scans the QR code with the EpPay mobile app
4. The mobile app sends payment confirmation to EpPay's server (`https://eppay.io/payment-success`)
5. The QR component auto-polls `GET /eppay/verify/{paymentId}` to check status
6. Once confirmed, the component redirects to your `success-url`

> **Important:** The mobile app sends confirmation to EpPay's server, not to your app. Your app detects payment completion by polling the payment status endpoint.

## Configuration

All configuration options in `config/eppay.php`:

```php
return [
    // Your EpPay API key from https://eppay.io/apis
    'api_key' => env('EPPAY_API_KEY'),

    // EpPay base URL
    'base_url' => env('EPPAY_BASE_URL', 'https://eppay.io'),

    // Default wallet address that will receive payments
    'default_beneficiary' => env('EPPAY_DEFAULT_BENEFICIARY'),

    // Default blockchain network RPC URL
    'default_rpc' => env('EPPAY_DEFAULT_RPC', 'https://rpc.scimatic.net'),

    // Default token contract address (USDT, USDC, etc.)
    'default_token' => env('EPPAY_DEFAULT_TOKEN'),

    // EpPay server callback URL (do not change)
    'success_url' => env('EPPAY_SUCCESS_URL', 'https://eppay.io/payment-success'),

    // Polling interval in milliseconds
    'polling_interval' => env('EPPAY_POLLING_INTERVAL', 3000),

    // Payment timeout in minutes
    'payment_timeout' => env('EPPAY_PAYMENT_TIMEOUT', 30),
];
```

## Supported Networks & Currencies

### Networks
- Ethereum (ETH)
- Binance Smart Chain (BSC)
- Polygon (MATIC)
- And more...

### Currencies
- USDT (Tether)
- USDC (USD Coin)
- ETH (Ethereum)
- BNB (Binance Coin)
- And more...

Check [EpPay documentation](https://eppay.io/docs) for the full list.

## Testing

```bash
composer test
```

## Security

If you discover any security-related issues, please email security@eppay.io instead of using the issue tracker.

## Credits

- [EpPay Team](https://eppay.io)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Support

- Documentation: [https://eppay.io/docs](https://eppay.io/docs)
- Email: support@eppay.io
- Issues: [GitHub Issues](https://github.com/eppay/laravel-eppay/issues)

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.
