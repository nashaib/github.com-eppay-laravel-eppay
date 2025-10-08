# Package Structure

This document explains the structure of the EpPay Laravel package.

## Directory Structure

```
laravel-eppay/
├── config/
│   └── eppay.php                        # Package configuration file
├── src/
│   ├── EpPayClient.php                  # Main client for API interactions
│   ├── EpPayServiceProvider.php         # Laravel service provider
│   ├── routes.php                       # Package routes
│   ├── Facades/
│   │   └── EpPay.php                    # Facade for easy access
│   ├── View/
│   │   └── Components/
│   │       └── PaymentQr.php            # Blade component class
│   ├── Middleware/
│   │   └── VerifyEpPayPayment.php       # Payment verification middleware
│   └── Http/
│       └── Controllers/
│           └── (Future: Webhook controller)
├── resources/
│   └── views/
│       └── components/
│           └── payment-qr.blade.php     # QR code component view
├── tests/
│   └── EpPayClientTest.php              # PHPUnit tests
├── examples/
│   ├── PaymentController.php            # Example controller
│   ├── routes.php                       # Example routes
│   ├── payment-show.blade.php           # Example view
│   └── simple-payment.blade.php         # Simple example
├── composer.json                        # Composer package definition
├── phpunit.xml                          # PHPUnit configuration
├── README.md                            # Main documentation
├── INSTALLATION.md                      # Installation guide
├── PUBLISHING.md                        # Publishing guide
├── CHANGELOG.md                         # Version history
├── LICENSE.md                           # MIT License
├── PACKAGE-STRUCTURE.md                 # This file
└── .gitignore                           # Git ignore rules
```

## Core Components

### 1. EpPayClient.php

The main service class that handles all API interactions with EpPay.

**Methods:**
- `generatePayment()` - Creates a new payment request
- `verifyPayment()` - Checks payment status
- `isPaymentCompleted()` - Returns boolean for completion status
- `getPaymentDetails()` - Gets full payment information
- `getQrCodeUrl()` - Generates QR code URL
- `getPaymentUrl()` - Gets payment page URL

**Usage:**
```php
use EpPay\LaravelEpPay\EpPayClient;

$client = new EpPayClient();
$payment = $client->generatePayment(100.00);
```

### 2. EpPayServiceProvider.php

Registers the package with Laravel.

**Responsibilities:**
- Registers the EpPayClient as a singleton
- Publishes config and views
- Loads routes and Blade components
- Sets up package aliases

### 3. Facades/EpPay.php

Laravel facade for convenient static access.

**Usage:**
```php
use EpPay\LaravelEpPay\Facades\EpPay;

$payment = EpPay::generatePayment(50.00);
```

### 4. View/Components/PaymentQr.php

Blade component class for the payment QR code display.

**Properties:**
- `$paymentId` - The payment identifier
- `$paymentData` - Payment details array
- `$qrCodeUrl` - URL to QR code image
- `$paymentUrl` - URL to payment page
- `$pollingInterval` - How often to check status
- `$autoRefresh` - Enable automatic status checking
- `$successUrl` - Redirect URL on success
- `$cancelUrl` - Redirect URL on cancel

**Usage:**
```blade
<x-eppay-payment-qr
    :payment-id="$paymentId"
    :payment-data="$paymentData"
    :auto-refresh="true"
    success-url="/success"
/>
```

### 5. Middleware/VerifyEpPayPayment.php

Middleware to protect routes requiring completed payments.

**Usage:**
```php
Route::get('/download/{payment_id}', function (Request $request) {
    // Payment is verified
    $details = $request->input('payment_details');
    return response()->download($file);
})->middleware('eppay.verify:payment_id');
```

### 6. resources/views/components/payment-qr.blade.php

The Blade view for the QR code component.

**Features:**
- Displays QR code image
- Shows payment details
- Auto-refreshes payment status
- Handles payment completion
- Shows loading states
- Error handling
- Mobile responsive
- Uses Alpine.js for interactivity

## Configuration

### config/eppay.php

All configurable options:

```php
[
    'api_key' => env('EPPAY_API_KEY'),
    'base_url' => env('EPPAY_BASE_URL', 'https://eppay.io'),
    'default_network' => env('EPPAY_DEFAULT_NETWORK', 'ETH'),
    'default_currency' => env('EPPAY_DEFAULT_CURRENCY', 'USDT'),
    'polling_interval' => env('EPPAY_POLLING_INTERVAL', 3000),
    'payment_timeout' => env('EPPAY_PAYMENT_TIMEOUT', 30),
]
```

## Routes

The package automatically registers these routes:

- `GET /eppay/verify/{paymentId}` - AJAX endpoint for status verification

Named route: `eppay.verify`

## Testing

Tests are located in the `tests/` directory using PHPUnit and Orchestra Testbench.

**Run tests:**
```bash
composer test
# or
./vendor/bin/phpunit
```

## Dependencies

### Required
- `php`: ^8.1|^8.2|^8.3
- `laravel/framework`: ^10.0|^11.0
- `illuminate/support`: ^10.0|^11.0
- `guzzlehttp/guzzle`: ^7.2

### Dev Dependencies
- `phpunit/phpunit`: ^10.0
- `orchestra/testbench`: ^8.0|^9.0

## Auto-Discovery

The package uses Laravel's package auto-discovery feature. The service provider and facade alias are automatically registered.

Defined in `composer.json`:
```json
"extra": {
    "laravel": {
        "providers": [
            "EpPay\\LaravelEpPay\\EpPayServiceProvider"
        ],
        "aliases": {
            "EpPay": "EpPay\\LaravelEpPay\\Facades\\EpPay"
        }
    }
}
```

## Frontend Dependencies

The QR code component requires:
- **Alpine.js 3.x** - For reactive interactivity
- **Tailwind CSS** (optional) - For default styling

These are loaded via CDN in the component, but can be included in your app's build process.

## Customization

### Customize Configuration

Publish and edit config:
```bash
php artisan vendor:publish --tag=eppay-config
```

### Customize Views

Publish and edit views:
```bash
php artisan vendor:publish --tag=eppay-views
```

Views will be copied to `resources/views/vendor/eppay/`

## API Flow

```
1. Developer calls EpPay::generatePayment()
   ↓
2. EpPayClient sends POST to /generate-code
   ↓
3. EpPay API returns payment data
   ↓
4. Developer displays <x-eppay-payment-qr>
   ↓
5. Component polls /eppay/verify/{id} every 3s
   ↓
6. User scans QR and pays via mobile app
   ↓
7. Status changes to 'completed'
   ↓
8. Component detects completion
   ↓
9. Redirects to success URL
```

## Extension Points

### Add Custom Metadata

```php
$payment = EpPay::generatePayment(100, metadata: [
    'order_id' => 12345,
    'customer_email' => 'user@example.com',
    'custom_field' => 'value'
]);
```

### Listen to Events

```javascript
document.addEventListener('payment-completed', (event) => {
    console.log(event.detail); // { paymentId, data }
});
```

### Custom Middleware

Extend the verification middleware:
```php
class CustomVerifyPayment extends VerifyEpPayPayment
{
    public function handle($request, $next, $paramName = 'payment_id')
    {
        // Custom logic
        return parent::handle($request, $next, $paramName);
    }
}
```

## Support

For questions about the package structure:
- Email: support@eppay.io
- GitHub: https://github.com/eppay/laravel-eppay
- Docs: https://eppay.io/docs
