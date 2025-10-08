<?php

namespace EpPay\LaravelEpPay;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EpPayClient
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('eppay.api_key');
        $this->baseUrl = config('eppay.base_url');

        if (empty($this->apiKey)) {
            throw new \Exception('EpPay API key is not configured. Please set EPPAY_API_KEY in your .env file.');
        }
    }

    /**
     * Generate a payment request
     *
     * @param float $amount The amount to charge
     * @param string|null $network The blockchain network (default from config)
     * @param string|null $currency The cryptocurrency (default from config)
     * @param array $metadata Additional metadata to store with the payment
     * @return array Payment data including payment_id and QR code
     * @throws \Exception
     */
    public function generatePayment(
        float $amount,
        ?string $network = null,
        ?string $currency = null,
        array $metadata = []
    ): array {
        $network = $network ?? config('eppay.default_network');
        $currency = $currency ?? config('eppay.default_currency');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Accept' => 'application/json',
        ])->post($this->baseUrl . '/generate-code', [
            'amount' => $amount,
            'network' => $network,
            'currency' => $currency,
            'metadata' => json_encode($metadata),
        ]);

        if (!$response->successful()) {
            Log::error('EpPay payment generation failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \Exception('Failed to generate payment: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Verify payment status
     *
     * @param string $paymentId The payment ID to verify
     * @return array Payment status data
     * @throws \Exception
     */
    public function verifyPayment(string $paymentId): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Accept' => 'application/json',
        ])->post($this->baseUrl . '/payment-verification', [
            'payment_id' => $paymentId,
        ]);

        if (!$response->successful()) {
            Log::error('EpPay payment verification failed', [
                'payment_id' => $paymentId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \Exception('Failed to verify payment: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Check if a payment is completed
     *
     * @param string $paymentId The payment ID to check
     * @return bool
     */
    public function isPaymentCompleted(string $paymentId): bool
    {
        try {
            $status = $this->verifyPayment($paymentId);
            return isset($status['status']) && $status['status'] === 'completed';
        } catch (\Exception $e) {
            Log::error('Error checking payment status', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get payment details
     *
     * @param string $paymentId The payment ID
     * @return array|null Payment details or null if not found
     */
    public function getPaymentDetails(string $paymentId): ?array
    {
        try {
            return $this->verifyPayment($paymentId);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Generate QR code URL for payment
     *
     * @param string $paymentId The payment ID
     * @return string QR code URL
     */
    public function getQrCodeUrl(string $paymentId): string
    {
        return $this->baseUrl . '/qr-code/' . $paymentId;
    }

    /**
     * Get payment page URL
     *
     * @param string $paymentId The payment ID
     * @return string Payment page URL
     */
    public function getPaymentUrl(string $paymentId): string
    {
        return $this->baseUrl . '/payment/' . $paymentId;
    }
}
