# EpPay Laravel Package

[![Latest Version](https://img.shields.io/packagist/v/eppay/laravel-eppay.svg)](https://packagist.org/packages/eppay/laravel-eppay)
[![License](https://img.shields.io/packagist/l/eppay/laravel-eppay.svg)](https://packagist.org/packages/eppay/laravel-eppay)

A simple and elegant Laravel package for integrating EpPay cryptocurrency payments into your Laravel application. Accept crypto payments with just a few lines of code!

## Features

- ✅ Easy installation via Composer
- ✅ Simple `.env` configuration
- ✅ Beautiful pre-built QR code payment component
- ✅ Automatic payment status verification
- ✅ Real-time payment polling
- ✅ Support for multiple blockchain networks (ETH, BSC, Polygon, etc.)
- ✅ Support for multiple cryptocurrencies (USDT, USDC, ETH, BNB, etc.)
- ✅ Payment verification middleware
- ✅ Fully customizable Blade components
- ✅ Laravel 10 & 11 compatible

## Requirements

- PHP 8.1 or higher
- Laravel 10.0 or higher
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

### Step 3: Configure Your API Key

Add your EpPay API key to your `.env` file:

```env
EPPAY_API_KEY=your_api_key_here
EPPAY_BASE_URL=https://eppay.io
EPPAY_DEFAULT_NETWORK=ETH
EPPAY_DEFAULT_CURRENCY=USDT
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
        // Generate a payment request
        $payment = EpPay::generatePayment(
            amount: 100.00,
            network: 'ETH',      // Optional: defaults to config
            currency: 'USDT',     // Optional: defaults to config
            metadata: [           // Optional: store custom data
                'order_id' => '12345',
                'user_id' => auth()->id(),
            ]
        );

        // Payment response contains:
        // - payment_id: Unique payment identifier
        // - qr_code: QR code data
        // - wallet_address: Payment address
        // - amount: Payment amount
        // - currency: Payment currency
        // - network: Blockchain network

        return view('payments.show', [
            'paymentId' => $payment['payment_id'],
            'paymentData' => $payment,
        ]);
    }

    public function verifyPayment($paymentId)
    {
        // Check if payment is completed
        $isCompleted = EpPay::isPaymentCompleted($paymentId);

        if ($isCompleted) {
            // Payment successful! Process the order
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
- Beautiful QR code display
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

## Advanced Usage

### Using the Facade

The `EpPay` facade provides several helpful methods:

```php
use EpPay\LaravelEpPay\Facades\EpPay;

// Generate a payment
$payment = EpPay::generatePayment(50.00, 'BSC', 'USDT');

// Verify payment status
$status = EpPay::verifyPayment('payment_id_here');

// Check if payment is completed (returns boolean)
$isCompleted = EpPay::isPaymentCompleted('payment_id_here');

// Get payment details
$details = EpPay::getPaymentDetails('payment_id_here');

// Get QR code URL
$qrUrl = EpPay::getQrCodeUrl('payment_id_here');

// Get payment page URL
$pageUrl = EpPay::getPaymentUrl('payment_id_here');
```

### Payment Verification Middleware

Protect routes that require completed payments:

```php
// app/Http/Kernel.php

protected $middlewareAliases = [
    // ... other middleware
    'eppay.verify' => \EpPay\LaravelEpPay\Middleware\VerifyEpPayPayment::class,
];
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

    // Perform custom actions
    window.location.href = '/success';
});
```

### Custom Polling Interval

Change how often payment status is checked:

```env
# Check every 5 seconds (5000 milliseconds)
EPPAY_POLLING_INTERVAL=5000
```

Or pass it directly to the component:

```blade
<x-eppay-payment-qr
    :payment-id="$paymentId"
    polling-interval="5000"
/>
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

        // Generate payment
        $payment = EpPay::generatePayment(
            amount: $order->total,
            network: $request->network ?? 'ETH',
            currency: $request->currency ?? 'USDT',
            metadata: [
                'order_id' => $order->id,
                'user_id' => auth()->id(),
                'customer_email' => auth()->user()->email,
            ]
        );

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
                <!-- Add more order details -->
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

## Configuration

All configuration options in `config/eppay.php`:

```php
return [
    // Your EpPay API key
    'api_key' => env('EPPAY_API_KEY'),

    // EpPay base URL
    'base_url' => env('EPPAY_BASE_URL', 'https://eppay.io'),

    // Default blockchain network
    'default_network' => env('EPPAY_DEFAULT_NETWORK', 'ETH'),

    // Default cryptocurrency
    'default_currency' => env('EPPAY_DEFAULT_CURRENCY', 'USDT'),

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
