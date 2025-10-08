<?php

namespace EpPay\LaravelEpPay\Middleware;

use Closure;
use Illuminate\Http\Request;
use EpPay\LaravelEpPay\Facades\EpPay;
use Symfony\Component\HttpFoundation\Response;

class VerifyEpPayPayment
{
    /**
     * Handle an incoming request.
     *
     * This middleware verifies that a payment has been completed before
     * allowing access to the route. The payment ID should be passed as
     * a route parameter or query parameter.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $paramName = 'payment_id'): Response
    {
        // Get payment ID from route or query parameters
        $paymentId = $request->route($paramName) ?? $request->query($paramName);

        if (!$paymentId) {
            return response()->json([
                'error' => 'Payment ID is required',
            ], 400);
        }

        // Verify payment status
        if (!EpPay::isPaymentCompleted($paymentId)) {
            return response()->json([
                'error' => 'Payment has not been completed or is invalid',
                'payment_id' => $paymentId,
            ], 402); // 402 Payment Required
        }

        // Add payment details to request for controller use
        $paymentDetails = EpPay::getPaymentDetails($paymentId);
        $request->merge(['payment_details' => $paymentDetails]);

        return $next($request);
    }
}
