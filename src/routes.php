<?php

use Illuminate\Support\Facades\Route;
use EpPay\LaravelEpPay\Facades\EpPay;

/*
|--------------------------------------------------------------------------
| EpPay Package Routes
|--------------------------------------------------------------------------
|
| These routes are automatically registered by the EpPay package.
| They handle payment status checking for the frontend components.
|
*/

Route::prefix('eppay')->name('eppay.')->group(function () {
    // Payment status endpoint for AJAX polling (v2 rich response)
    Route::get('/status/{paymentId}', function ($paymentId) {
        try {
            $result = EpPay::checkStatus($paymentId);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    })->name('status');

    // Full payment details
    Route::get('/payment/{paymentId}', function ($paymentId) {
        try {
            $result = EpPay::getPaymentDetails($paymentId);
            if (!$result) {
                return response()->json(['error' => 'Payment not found'], 404);
            }
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    })->name('details');

    // Legacy verify endpoint (kept for backwards compatibility)
    Route::get('/verify/{paymentId}', function ($paymentId) {
        try {
            $result = EpPay::checkStatus($paymentId);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    })->name('verify');
});
