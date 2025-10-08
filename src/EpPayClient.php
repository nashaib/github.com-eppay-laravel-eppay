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
     * @param string|null $to Beneficiary wallet address (uses default if null)
     * @param string|null $rpc Network RPC URL (uses default if null)
     * @param string|null $token Token contract address (uses default if null)
     * @param string|null $successUrl Callback URL for successful payment (uses default if null)
     * @return array Payment data including paymentId
     * @throws \Exception
     */
    public function generatePayment(
        float $amount,
        ?string $to = null,
        ?string $rpc = null,
        ?string $token = null,
        ?string $successUrl = null
    ): array {
        // Use defaults from config if not provided
        $to = $to ?? config('eppay.default_beneficiary');
        $rpc = $rpc ?? config('eppay.default_rpc');
        $token = $token ?? config('eppay.default_token');
        $successUrl = $successUrl ?? config('eppay.success_url');

        // Validate required fields
        if (empty($to)) {
            throw new \Exception('Beneficiary address is required. Set EPPAY_DEFAULT_BENEFICIARY or pass $to parameter.');
        }
        if (empty($rpc)) {
            throw new \Exception('Network RPC is required. Set EPPAY_DEFAULT_RPC or pass $rpc parameter.');
        }
        if (empty($token)) {
            throw new \Exception('Token address is required. Set EPPAY_DEFAULT_TOKEN or pass $token parameter.');
        }

        $payload = [
            'apiKey' => $this->apiKey,
            'amount' => (string) $amount,
            'to' => $to,
            'rpc' => $rpc,
            'token' => $token,
        ];

        if ($successUrl) {
            $payload['success'] = $successUrl;
        }

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . '/generate-code', $payload);

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
     * @return array Payment status data (e.g., ['status' => true])
     * @throws \Exception
     */
    public function verifyPayment(string $paymentId): array
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
        ])->get($this->baseUrl . '/payment-status/' . $paymentId);

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
            return isset($status['status']) && $status['status'] === true;
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
     * Generate QR code data string for payment
     * Format: product=uuideppay&id={paymentId}
     *
     * @param string $paymentId The payment ID
     * @return string QR code data string
     */
    public function getQrCodeData(string $paymentId): string
    {
        return 'product=uuideppay&id=' . $paymentId;
    }

    /**
     * Get QR code image URL for payment
     * This generates a QR code image via QR Server API
     *
     * @param string $paymentId The payment ID
     * @param int $size QR code size in pixels (default: 300)
     * @return string QR code image URL
     */
    public function getQrCodeUrl(string $paymentId, int $size = 300): string
    {
        $data = $this->getQrCodeData($paymentId);
        return 'https://api.qrserver.com/v1/create-qr-code/?size=' . $size . 'x' . $size . '&data=' . urlencode($data);
    }

    /**
     * Get payment page URL (if you have one on eppay.io)
     *
     * @param string $paymentId The payment ID
     * @return string Payment page URL
     */
    public function getPaymentUrl(string $paymentId): string
    {
        return $this->baseUrl . '/payment/' . $paymentId;
    }
}
