<?php

namespace App\Http\Controllers;

use EpPay\LaravelEpPay\Facades\EpPay;
use Illuminate\Http\Request;

/**
 * Example Payment Controller
 *
 * This controller demonstrates how to integrate EpPay
 * cryptocurrency payments into your Laravel application.
 */
class PaymentController extends Controller
{
    /**
     * Show the payment creation form
     */
    public function index()
    {
        return view('payments.create');
    }

    /**
     * Generate a new payment request
     */
    public function createPayment(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'network' => 'nullable|string',
            'currency' => 'nullable|string',
            'description' => 'nullable|string|max:255',
        ]);

        try {
            // Generate payment with EpPay
            $payment = EpPay::generatePayment(
                amount: $validated['amount'],
                network: $validated['network'] ?? null,
                currency: $validated['currency'] ?? null,
                metadata: [
                    'description' => $validated['description'] ?? 'Payment',
                    'user_id' => auth()->id(),
                    'created_at' => now()->toIso8601String(),
                ]
            );

            // Optionally store payment in your database
            // Payment::create([...]);

            return redirect()->route('payment.show', $payment['payment_id']);

        } catch (\Exception $e) {
            return back()->withErrors([
                'error' => 'Failed to create payment: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Display the payment QR code page
     */
    public function showPayment($paymentId)
    {
        try {
            // Get payment details
            $paymentDetails = EpPay::getPaymentDetails($paymentId);

            if (!$paymentDetails) {
                abort(404, 'Payment not found');
            }

            return view('payments.show', [
                'paymentId' => $paymentId,
                'paymentData' => $paymentDetails,
            ]);

        } catch (\Exception $e) {
            abort(404, 'Payment not found');
        }
    }

    /**
     * Verify payment status (called via AJAX or manually)
     */
    public function verifyPayment($paymentId)
    {
        try {
            $status = EpPay::verifyPayment($paymentId);

            if ($status['status'] === 'completed') {
                // Update your database
                // Payment::where('payment_id', $paymentId)->update(['status' => 'completed']);

                return response()->json([
                    'success' => true,
                    'status' => 'completed',
                    'message' => 'Payment completed successfully!',
                    'redirect' => route('payment.success', $paymentId),
                ]);
            }

            return response()->json([
                'success' => false,
                'status' => $status['status'],
                'message' => 'Payment not completed yet',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Payment success page
     */
    public function paymentSuccess($paymentId)
    {
        // Verify the payment is actually completed
        if (!EpPay::isPaymentCompleted($paymentId)) {
            return redirect()->route('payment.show', $paymentId)
                ->with('error', 'Payment not completed');
        }

        $paymentDetails = EpPay::getPaymentDetails($paymentId);

        return view('payments.success', [
            'paymentId' => $paymentId,
            'paymentData' => $paymentDetails,
        ]);
    }

    /**
     * Payment cancel/failure page
     */
    public function paymentCancel($paymentId)
    {
        return view('payments.cancel', [
            'paymentId' => $paymentId,
        ]);
    }

    /**
     * Example: Download digital product after payment
     * Uses middleware to verify payment
     */
    public function downloadProduct(Request $request, $paymentId)
    {
        // The middleware ensures payment is completed
        // Payment details are available in the request
        $paymentDetails = $request->input('payment_details');

        // Get product from payment metadata
        $metadata = json_decode($paymentDetails['metadata'] ?? '{}', true);
        $productId = $metadata['product_id'] ?? null;

        if (!$productId) {
            abort(404, 'Product not found');
        }

        // Return download
        return response()->download(
            storage_path("products/{$productId}.zip"),
            "product-{$productId}.zip"
        );
    }
}
