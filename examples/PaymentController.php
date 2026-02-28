<?php

namespace App\Http\Controllers;

use EpPay\LaravelEpPay\Facades\EpPay;
use Illuminate\Http\Request;

/**
 * Example Payment Controller
 *
 * Demonstrates EpPay v2 API integration in a Laravel application.
 */
class PaymentController extends Controller
{
    /**
     * Show the payment creation form
     */
    public function index()
    {
        // Fetch available networks for the form dropdown
        $networks = EpPay::getNetworks();

        return view('payments.create', compact('networks'));
    }

    /**
     * Create a new payment via EpPay v2 API
     */
    public function createPayment(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'network' => 'nullable|string',
            'token_type' => 'nullable|string|in:USDT,USDC',
        ]);

        try {
            // Create payment — uses .env defaults for wallet, network, token_type
            $payment = EpPay::createPayment(
                amount: $validated['amount'],
                network: $validated['network'] ?? null,
                tokenType: $validated['token_type'] ?? null,
                successUrl: route('payment.callback'),
            );

            // $payment contains: payment_id, amount, network, token_type, to,
            //                    status, qr_data, payment_url, created_at

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
        $payment = EpPay::getPaymentDetails($paymentId);

        if (!$payment) {
            abort(404, 'Payment not found');
        }

        return view('payments.show', [
            'paymentId' => $paymentId,
            'paymentData' => $payment,
        ]);
    }

    /**
     * AJAX endpoint: check payment status (called by frontend polling)
     */
    public function checkStatus($paymentId)
    {
        try {
            $status = EpPay::checkStatus($paymentId);

            return response()->json($status);
            // Returns: payment_id, status, confirmed, amount, network,
            //          token_type, tx_hash, from, completed_at

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Server-to-server callback from EpPay (POST)
     * This is called by EpPay when payment is confirmed on-chain.
     */
    public function handleCallback(Request $request)
    {
        // EpPay sends: payment_id, amount, tx_hash, from, token_type, network
        $paymentId = $request->input('payment_id');
        $txHash = $request->input('tx_hash');
        $amount = $request->input('amount');

        // Verify the payment is actually complete
        if (!EpPay::isPaymentCompleted($paymentId)) {
            return response()->json(['error' => 'Payment not confirmed'], 400);
        }

        // Update your order/database
        // Order::where('payment_id', $paymentId)->update(['status' => 'paid', 'tx_hash' => $txHash]);

        return response()->json(['received' => true]);
    }

    /**
     * Payment success page
     */
    public function paymentSuccess($paymentId)
    {
        if (!EpPay::isPaymentCompleted($paymentId)) {
            return redirect()->route('payment.show', $paymentId)
                ->with('error', 'Payment not completed yet');
        }

        $payment = EpPay::getPaymentDetails($paymentId);

        return view('payments.success', [
            'paymentId' => $paymentId,
            'paymentData' => $payment,
        ]);
    }
}
