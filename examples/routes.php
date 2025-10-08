<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Example Payment Routes
|--------------------------------------------------------------------------
|
| Add these routes to your routes/web.php file to enable
| EpPay payment functionality in your Laravel application.
|
*/

// Payment routes
Route::prefix('payments')->name('payment.')->group(function () {
    // Create payment form
    Route::get('/', [PaymentController::class, 'index'])
        ->name('index');

    // Generate payment
    Route::post('/', [PaymentController::class, 'createPayment'])
        ->name('create');

    // Show payment QR code
    Route::get('/{paymentId}', [PaymentController::class, 'showPayment'])
        ->name('show');

    // Verify payment (AJAX endpoint)
    Route::get('/{paymentId}/verify', [PaymentController::class, 'verifyPayment'])
        ->name('verify');

    // Payment success page
    Route::get('/{paymentId}/success', [PaymentController::class, 'paymentSuccess'])
        ->name('success');

    // Payment cancel page
    Route::get('/{paymentId}/cancel', [PaymentController::class, 'paymentCancel'])
        ->name('cancel');

    // Protected route - requires completed payment
    Route::get('/{paymentId}/download', [PaymentController::class, 'downloadProduct'])
        ->name('download')
        ->middleware('eppay.verify:paymentId');
});

// Alternative: Simple single-page payment
Route::get('/pay/{amount}', function ($amount) {
    $payment = \EpPay\LaravelEpPay\Facades\EpPay::generatePayment($amount);

    return view('simple-payment', [
        'paymentId' => $payment['payment_id'],
        'paymentData' => $payment,
    ]);
})->name('simple.pay');
