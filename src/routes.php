<?php

use Illuminate\Support\Facades\Route;
use EpPay\LaravelEpPay\Facades\EpPay;

/*
|--------------------------------------------------------------------------
| EpPay Package Routes
|--------------------------------------------------------------------------
|
| These routes are automatically registered by the EpPay package.
| They handle payment verification for the frontend components.
|
*/

Route::prefix('eppay')->name('eppay.')->group(function () {
    // Payment verification endpoint for AJAX calls
    Route::get('/verify/{paymentId}', function ($paymentId) {
        try {
            $result = EpPay::verifyPayment($paymentId);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    })->name('verify');
});
