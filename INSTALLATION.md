# Installation Guide

This guide will walk you through installing and configuring the EpPay Laravel package in your application.

## Prerequisites

Before you begin, make sure you have:

1. ✅ PHP 8.1 or higher
2. ✅ Laravel 10.0 or higher
3. ✅ Composer installed
4. ✅ An EpPay account and API key

If you don't have an EpPay API key yet, [register here](https://eppay.io/register) and generate one in the [API settings](https://eppay.io/apis).

## Step-by-Step Installation

### Step 1: Install the Package

Open your terminal in your Laravel project directory and run:

```bash
composer require eppay/laravel-eppay
```

The package will be automatically discovered by Laravel thanks to package auto-discovery.

### Step 2: Publish Configuration (Optional)

If you want to customize the configuration, publish the config file:

```bash
php artisan vendor:publish --tag=eppay-config
```

This creates `config/eppay.php` where you can customize settings like default network, currency, and polling intervals.

### Step 3: Publish Views (Optional)

If you want to customize the payment QR component appearance:

```bash
php artisan vendor:publish --tag=eppay-views
```

This publishes views to `resources/views/vendor/eppay/` for customization.

### Step 4: Configure Environment Variables

Add your EpPay API key to your `.env` file:

```env
EPPAY_API_KEY=your_api_key_here
EPPAY_BASE_URL=https://eppay.io
EPPAY_DEFAULT_NETWORK=ETH
EPPAY_DEFAULT_CURRENCY=USDT
EPPAY_POLLING_INTERVAL=3000
```

Replace `your_api_key_here` with your actual API key from [eppay.io/apis](https://eppay.io/apis).

### Step 5: Clear Config Cache

If you're using config caching, clear the cache:

```bash
php artisan config:clear
```

### Step 6: Register Middleware (Optional)

If you want to use the payment verification middleware, add it to your `app/Http/Kernel.php`:

```php
protected $middlewareAliases = [
    // ... other middleware
    'eppay.verify' => \EpPay\LaravelEpPay\Middleware\VerifyEpPayPayment::class,
];
```

## Quick Test

Let's verify the installation works by creating a simple test route.

Add this to your `routes/web.php`:

```php
use EpPay\LaravelEpPay\Facades\EpPay;

Route::get('/test-payment', function () {
    try {
        $payment = EpPay::generatePayment(10.00);
        return response()->json([
            'success' => true,
            'payment_id' => $payment['payment_id'],
            'message' => 'EpPay package is working!'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});
```

Visit `http://your-app.test/test-payment` in your browser. If you see a success response with a payment ID, everything is working correctly!

## Basic Implementation

Now let's create a basic payment flow.

### 1. Create a Payment Controller

```bash
php artisan make:controller PaymentController
```

Edit `app/Http/Controllers/PaymentController.php`:

```php
<?php

namespace App\Http\Controllers;

use EpPay\LaravelEpPay\Facades\EpPay;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function create()
    {
        return view('payment.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $payment = EpPay::generatePayment($validated['amount']);

        return redirect()->route('payment.show', $payment['payment_id']);
    }

    public function show($paymentId)
    {
        $details = EpPay::getPaymentDetails($paymentId);

        return view('payment.show', [
            'paymentId' => $paymentId,
            'paymentData' => $details,
        ]);
    }

    public function success($paymentId)
    {
        if (EpPay::isPaymentCompleted($paymentId)) {
            return view('payment.success', compact('paymentId'));
        }

        return redirect()->route('payment.show', $paymentId);
    }
}
```

### 2. Add Routes

Add to `routes/web.php`:

```php
use App\Http\Controllers\PaymentController;

Route::get('/payment/create', [PaymentController::class, 'create'])->name('payment.create');
Route::post('/payment', [PaymentController::class, 'store'])->name('payment.store');
Route::get('/payment/{paymentId}', [PaymentController::class, 'show'])->name('payment.show');
Route::get('/payment/{paymentId}/success', [PaymentController::class, 'success'])->name('payment.success');
```

### 3. Create Views

Create `resources/views/payment/create.blade.php`:

```blade
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-md mx-auto bg-white rounded-lg shadow p-6">
        <h1 class="text-2xl font-bold mb-4">Create Payment</h1>

        <form method="POST" action="{{ route('payment.store') }}">
            @csrf

            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Amount (USDT)</label>
                <input
                    type="number"
                    name="amount"
                    step="0.01"
                    min="0.01"
                    required
                    class="w-full border rounded px-3 py-2"
                    placeholder="10.00"
                >
                @error('amount')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button
                type="submit"
                class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700"
            >
                Generate Payment
            </button>
        </form>
    </div>
</div>
@endsection
```

Create `resources/views/payment/show.blade.php`:

```blade
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-center mb-8">Complete Your Payment</h1>

    <x-eppay-payment-qr
        :payment-id="$paymentId"
        :payment-data="$paymentData"
        :auto-refresh="true"
        :success-url="route('payment.success', $paymentId)"
    />
</div>
@endsection
```

Create `resources/views/payment/success.blade.php`:

```blade
@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-md mx-auto bg-white rounded-lg shadow p-6 text-center">
        <div class="text-green-500 text-6xl mb-4">✓</div>
        <h1 class="text-2xl font-bold mb-2">Payment Successful!</h1>
        <p class="text-gray-600 mb-6">Your payment has been confirmed.</p>
        <a href="/" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
            Back to Home
        </a>
    </div>
</div>
@endsection
```

## Testing Your Implementation

1. Start your Laravel development server:
```bash
php artisan serve
```

2. Visit `http://localhost:8000/payment/create`

3. Enter an amount and submit

4. You'll see the QR code page - scan it with the EpPay mobile app

5. After payment, you'll be automatically redirected to the success page

## Troubleshooting

### Error: "EpPay API key is not configured"

Make sure you've added `EPPAY_API_KEY` to your `.env` file and run `php artisan config:clear`.

### Error: "Failed to generate payment"

1. Verify your API key is correct
2. Check you have internet connectivity
3. Ensure the EpPay API is accessible from your server

### QR Code Not Displaying

1. Check browser console for JavaScript errors
2. Ensure Alpine.js is loaded
3. Verify the payment ID is valid

### Payment Status Not Updating

1. Check the polling interval in config
2. Verify the `/eppay/verify/{paymentId}` route is accessible
3. Check browser network tab for failed API calls

## Next Steps

- Read the [full documentation](README.md)
- Check out [example implementations](examples/)
- Learn about [advanced features](README.md#advanced-usage)
- Join our [community](https://eppay.io/community)

## Support

Need help? Reach out:

- Email: support@eppay.io
- Documentation: https://eppay.io/docs
- GitHub Issues: https://github.com/eppay/laravel-eppay/issues

Congratulations! You've successfully installed the EpPay Laravel package. Happy coding! 🎉
