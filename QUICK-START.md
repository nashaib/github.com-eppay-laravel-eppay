# Quick Start Guide

## Installation

```bash
composer require eppay/laravel-eppay
```

## Configuration

Add to your `.env` file:

```env
# Required: Your API key from https://eppay.io/apis
EPPAY_API_KEY=your_api_key_here

# Required: Wallet address that receives payments
EPPAY_DEFAULT_BENEFICIARY=0x8AB960B95aCCc5080c15721fdeA30e72C8251F0b

# Required: Network RPC URL
EPPAY_DEFAULT_RPC=https://rpc.scimatic.net

# Required: Token contract address (USDT, USDC, etc.)
EPPAY_DEFAULT_TOKEN=0x65C4A0dA0416d1262DbC04BeE524c804205B92e8

# Optional: API base URL (default: https://eppay.io)
EPPAY_BASE_URL=https://eppay.io

# Optional: Success URL (default: https://eppay.io/payment-success)
# This is where the mobile app sends payment confirmation to EpPay server
# DO NOT CHANGE unless instructed by EpPay support
EPPAY_SUCCESS_URL=https://eppay.io/payment-success
```

## How It Works

1. **User gets API key** from https://eppay.io
2. **Your app generates payment** by calling EpPay API
3. **App displays QR code** with format: `product=uuideppay&id={paymentId}`
4. **User scans QR** with EpPay mobile app and completes payment
5. **Mobile app sends confirmation** to EpPay server (`https://eppay.io/payment-success`) as `{"status": true}`
6. **Your app verifies payment** by checking `/payment-status/{paymentId}` which returns `{"status": true}`

## Basic Usage

### 1. Create Payment Controller

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

        try {
            // Generate payment (uses defaults from .env)
            $payment = EpPay::generatePayment($validated['amount']);

            // Response: {"paymentId": "8a020135-19b7-42df-be4b-1a8722ad0570"}

            return redirect()->route('payment.show', $payment['paymentId']);

        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show($paymentId)
    {
        return view('payment.show', [
            'paymentId' => $paymentId
        ]);
    }

    public function verify($paymentId)
    {
        try {
            // Check payment status
            $status = EpPay::verifyPayment($paymentId);

            // Response: {"status": true} if paid, {"status": false} if pending

            if ($status['status'] === true) {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment completed!'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Payment pending'
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function success($paymentId)
    {
        // Verify payment is completed
        if (EpPay::isPaymentCompleted($paymentId)) {
            // Payment confirmed! Process the order
            // Update database, send email, etc.

            return view('payment.success', ['paymentId' => $paymentId]);
        }

        return redirect()->route('payment.show', $paymentId)
            ->with('error', 'Payment not yet completed');
    }
}
```

### 2. Add Routes

```php
// routes/web.php

use App\Http\Controllers\PaymentController;

Route::get('/payment/create', [PaymentController::class, 'create'])->name('payment.create');
Route::post('/payment', [PaymentController::class, 'store'])->name('payment.store');
Route::get('/payment/{paymentId}', [PaymentController::class, 'show'])->name('payment.show');

// AJAX verification endpoint (used by QR component)
Route::get('/payment/{paymentId}/verify', [PaymentController::class, 'verify'])->name('payment.verify');

// Success page (after payment is verified)
Route::get('/payment/{paymentId}/success', [PaymentController::class, 'success'])->name('payment.success');
```

### 3. Create Payment View

```blade
{{-- resources/views/payment/show.blade.php --}}

@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-center mb-8">Scan to Pay</h1>

    <x-eppay-payment-qr
        :payment-id="$paymentId"
        :auto-refresh="true"
        success-url="{{ route('order.success') }}"
    />
</div>
@endsection
```

## Advanced Usage

### Custom Payment Parameters

```php
use EpPay\LaravelEpPay\Facades\EpPay;

// Override default parameters for specific payment
$payment = EpPay::generatePayment(
    amount: 100.50,
    to: '0xYourCustomWalletAddress',        // Custom beneficiary
    rpc: 'https://custom-rpc-url.com',      // Custom network
    token: '0xCustomTokenAddress',          // Custom token
    successUrl: null                        // Uses default: https://eppay.io/payment-success
);

// Returns: {"paymentId": "uuid-here"}

// Or simply use defaults from .env
$payment = EpPay::generatePayment(100.50);
```

### Check Payment Status

```php
// Get full status
$status = EpPay::verifyPayment($paymentId);
// Returns: {"status": true} or {"status": false}

// Simple boolean check
$isPaid = EpPay::isPaymentCompleted($paymentId);
// Returns: true or false
```

### Generate QR Code

```php
// Get QR code data string
$qrData = EpPay::getQrCodeData($paymentId);
// Returns: "product=uuideppay&id=8a020135-19b7-42df-be4b-1a8722ad0570"

// Get QR code image URL (via Google Charts)
$qrImageUrl = EpPay::getQrCodeUrl($paymentId);
// Returns: https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=product%3Duuideppay%26id%3D...
```

### Manual QR Code Display

If you don't want to use the Blade component:

```blade
<div>
    <img src="{{ EpPay::getQrCodeUrl($paymentId) }}" alt="Payment QR Code">

    <button onclick="checkPayment()">Check Payment Status</button>
</div>

<script>
async function checkPayment() {
    const response = await fetch('/payment/{{ $paymentId }}/verify');
    const data = await response.json();

    if (data.success) {
        alert('Payment completed!');
        window.location.href = '/success';
    } else {
        alert('Payment not yet completed');
    }
}
</script>
```

## API Flow Diagram

```
┌─────────────┐
│  Your App   │
└──────┬──────┘
       │
       │ 1. POST /generate-code
       │    {apiKey, amount, to, rpc, token, success: "https://eppay.io/payment-success"}
       │
       v
┌──────────────┐
│  EpPay API   │
└──────┬───────┘
       │
       │ 2. Returns {"paymentId": "uuid"}
       │
       v
┌──────────────┐
│  Your App    │
└──────┬───────┘
       │
       │ 3. Display QR: product=uuideppay&id=uuid
       │
       v
┌──────────────┐
│ User scans   │
│ with mobile  │
└──────┬───────┘
       │
       │ 4. User pays via mobile app
       │
       v
┌──────────────┐
│ Mobile App   │
└──────┬───────┘
       │
       │ 5. POST to https://eppay.io/payment-success
       │    {"status": true}
       │
       v
┌──────────────┐
│ EpPay Server │ ← Payment marked as completed
└──────────────┘

       Meanwhile...

┌──────────────┐
│  Your App    │ ← Auto-polling every 3 seconds
└──────┬───────┘
       │
       │ 6. GET /payment-status/{paymentId}
       │
       v
┌──────────────┐
│  EpPay API   │
└──────┬───────┘
       │
       │ 7. Returns {"status": true}
       │
       v
┌──────────────┐
│ Payment Done │ ← Your app processes the order
└──────────────┘
```

## Testing

### Test Payment Generation

```php
use EpPay\LaravelEpPay\Facades\EpPay;

Route::get('/test', function () {
    try {
        $payment = EpPay::generatePayment(10.00);
        return response()->json([
            'success' => true,
            'paymentId' => $payment['paymentId']
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }
});
```

Visit `/test` to see if payment generation works.

## Troubleshooting

### "Beneficiary address is required"
Set `EPPAY_DEFAULT_BENEFICIARY` in your `.env` file.

### "Token address is required"
Set `EPPAY_DEFAULT_TOKEN` in your `.env` file.

### "Network RPC is required"
Set `EPPAY_DEFAULT_RPC` in your `.env` file.

### Payment status always returns false
- Verify the payment was actually made via mobile app
- Check that the paymentId is correct
- Confirm the mobile app sent success callback

### QR code not displaying
- Check that paymentId is valid
- Verify Google Charts API is accessible
- Check browser console for errors

## Support

- Email: support@eppay.io
- Docs: https://eppay.io/docs
- Issues: https://github.com/eppay/laravel-eppay/issues

## Next Steps

- See [README.md](README.md) for complete documentation
- Check [examples/](examples/) for more examples
- Read [INSTALLATION.md](INSTALLATION.md) for detailed setup
