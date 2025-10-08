# Changelog

All notable changes to `laravel-eppay` will be documented in this file.

## v1.0.4 - 2025-10-08

### Fixed
- **Complete component rewrite**: Removed Alpine.js dependency from payment QR component
- Fixed persistent JavaScript syntax errors caused by Blade directives inside JS code
- Eliminated view caching issues that prevented component updates

### Changed
- Payment QR component now uses vanilla JavaScript instead of Alpine.js
- Implemented `window.EpPayWidget` object for cleaner global scope management
- All DOM manipulation now uses standard `getElementById` and `style.display`
- Better separation of PHP/Blade and JavaScript code

### Improved
- More reliable component with no frontend framework dependencies
- Easier to debug and customize
- Reduced bundle size by removing Alpine.js requirement
- Cleaner code structure with self-executing function pattern

## v1.0.3 - 2025-10-08

### Added
- **Built-in QR Code Generation**: No longer depends on external QR code services
- Uses BaconQrCode library to generate QR codes locally
- QR codes returned as SVG data URLs (base64 encoded)

### Fixed
- Fixed Alpine.js syntax error in payment-qr component caused by Blade directives
- QR codes now work offline and don't require external API calls
- More reliable - no dependency on third-party QR services

### Improved
- Faster QR code generation (no network latency)
- Better privacy - QR data never leaves the server
- Works in offline/intranet environments

## v1.0.2 - 2025-10-08

### Fixed
- **Success URL Clarification**: Clarified that success URL (`https://eppay.io/payment-success`) points to EpPay server, NOT the developer's app
- **Configuration**: Renamed `default_success_url` to `success_url` with proper default value
- **Documentation**: Updated QUICK-START.md to clearly explain the payment flow
- **API Flow Diagram**: Corrected to show mobile app sends confirmation to EpPay server
- **Examples**: Removed confusing callback handling example (developers poll `/payment-status` instead)

### Important Note
The mobile app sends `{"status": true}` to `https://eppay.io/payment-success` (EpPay's server). Developers should NOT expect to receive this callback. Instead, use the auto-polling QR component or manually poll `GET /payment-status/{paymentId}`.

## v1.0.1 - 2025-10-08

### Fixed - BREAKING CHANGES
- **API Integration**: Updated to match actual EpPay API specification
- **generatePayment()**: Changed parameters from `(amount, network, currency, metadata)` to `(amount, to, rpc, token, successUrl)`
- **Authentication**: API key now sent in POST body as `apiKey` instead of Authorization header
- **Payment Verification**: Changed from `POST /payment-verification` to `GET /payment-status/{paymentId}`
- **Payment Status**: Now checks for `{"status": true}` instead of `{"status": "completed"}`
- **QR Code Format**: Updated to generate `product=uuideppay&id={paymentId}` format

### Added
- `getQrCodeData()` method to get raw QR code data string
- `QUICK-START.md` with complete API flow documentation
- Default configuration options for beneficiary, RPC, and token
- Support for using config defaults (can now call `generatePayment(amount)` only)

### Changed
- Config file now uses `default_beneficiary`, `default_rpc`, `default_token` instead of `default_network`, `default_currency`
- QR code component now properly checks for boolean status value
- All API calls updated to match EpPay's actual endpoints

### Migration Guide
**Old usage:**
```php
EpPay::generatePayment(100, 'ETH', 'USDT', ['meta' => 'data']);
```

**New usage:**
```php
// Option 1: Set defaults in .env and call with amount only
EpPay::generatePayment(100);

// Option 2: Specify all parameters
EpPay::generatePayment(100, $beneficiary, $rpc, $token, $successUrl);
```

## v1.0.0 - 2025-10-08

### Added
- Initial release
- EpPay payment generation
- Payment verification
- Pre-built QR code Blade component
- Automatic payment status polling
- Payment verification middleware
- Support for multiple blockchain networks
- Support for multiple cryptocurrencies
- Facade for easy API access
- Comprehensive documentation
- Laravel 10 & 11 support
