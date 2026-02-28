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
        $this->baseUrl = rtrim(config('eppay.base_url'), '/');

        if (empty($this->apiKey)) {
            throw new \Exception('EpPay API key is not configured. Please set EPPAY_API_KEY in your .env file.');
        }
    }

    /**
     * Create a payment request via API v2.
     *
     * @param float $amount Payment amount
     * @param string|null $to Beneficiary wallet address (uses config default if null)
     * @param string|null $network Network slug e.g. "bsc", "eth" (uses config default if null)
     * @param string|null $tokenType Token type "USDT" or "USDC" (uses config default if null)
     * @param string|null $successUrl Merchant callback URL (uses config default if null)
     * @return array Rich response: payment_id, amount, network, token_type, to, status, qr_data, payment_url, created_at
     * @throws \Exception
     */
    public function createPayment(
        float $amount,
        ?string $to = null,
        ?string $network = null,
        ?string $tokenType = null,
        ?string $successUrl = null
    ): array {
        $to = $to ?? config('eppay.default_beneficiary');
        $network = $network ?? config('eppay.default_network', 'bsc');
        $tokenType = $tokenType ?? config('eppay.default_token_type', 'USDT');
        $successUrl = $successUrl ?? config('eppay.success_url');

        if (empty($to)) {
            throw new \Exception('Beneficiary address is required. Set EPPAY_DEFAULT_BENEFICIARY or pass $to parameter.');
        }

        $payload = [
            'apiKey' => $this->apiKey,
            'amount' => (string) $amount,
            'to' => $to,
            'network' => $network,
            'token_type' => strtoupper($tokenType),
        ];

        if ($successUrl) {
            $payload['success'] = $successUrl;
        }

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . '/api/v2/create-payment', $payload);

        if (!$response->successful()) {
            Log::error('EpPay payment creation failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \Exception('Failed to create payment: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Check payment status (rich response with network, tx_hash, timestamps).
     *
     * @param string $paymentId The payment UUID
     * @return array Status data: payment_id, status, confirmed, amount, network, token_type, tx_hash, from, completed_at
     * @throws \Exception
     */
    public function checkStatus(string $paymentId): array
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
        ])->get($this->baseUrl . '/api/v2/payments/' . $paymentId . '/status');

        if (!$response->successful()) {
            Log::error('EpPay status check failed', [
                'payment_id' => $paymentId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \Exception('Failed to check payment status: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Get full payment details (includes nested network object).
     *
     * @param string $paymentId The payment UUID
     * @return array|null Payment details or null if not found
     */
    public function getPaymentDetails(string $paymentId): ?array
    {
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
            ])->get($this->baseUrl . '/api/v2/payments/' . $paymentId);

            if (!$response->successful()) {
                return null;
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('EpPay get payment details failed', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Check if a payment is completed.
     *
     * @param string $paymentId The payment UUID
     * @return bool
     */
    public function isPaymentCompleted(string $paymentId): bool
    {
        try {
            $status = $this->checkStatus($paymentId);
            return !empty($status['confirmed']);
        } catch (\Exception $e) {
            Log::error('Error checking payment completion', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get available networks with their tokens.
     *
     * @return array List of networks
     * @throws \Exception
     */
    public function getNetworks(): array
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
        ])->get($this->baseUrl . '/api/v2/networks');

        if (!$response->successful()) {
            throw new \Exception('Failed to fetch networks: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Generate QR code data string for payment.
     * Format: product=uuideppay&id={paymentId}
     *
     * @param string $paymentId The payment UUID
     * @return string QR code data string
     */
    public function getQrCodeData(string $paymentId): string
    {
        return 'product=uuideppay&id=' . $paymentId;
    }

    /**
     * Get QR code as base64 SVG data URL.
     *
     * @param string $paymentId The payment UUID
     * @param int $size QR code size in pixels (default: 300)
     * @return string QR code as base64 data URL
     */
    public function getQrCodeUrl(string $paymentId, int $size = 300): string
    {
        $data = $this->getQrCodeData($paymentId);

        $renderer = new \BaconQrCode\Renderer\ImageRenderer(
            new \BaconQrCode\Renderer\RendererStyle\RendererStyle($size),
            new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
        );

        $writer = new \BaconQrCode\Writer($renderer);
        $qrCode = $writer->writeString($data);

        return 'data:image/svg+xml;base64,' . base64_encode($qrCode);
    }

    /**
     * Get the payment page URL.
     *
     * @param string $paymentId The payment UUID
     * @return string Payment page URL
     */
    public function getPaymentUrl(string $paymentId): string
    {
        return $this->baseUrl . '/payment/' . $paymentId;
    }

    // ── Legacy v1 methods (deprecated) ──────────────────────────────

    /**
     * @deprecated Use createPayment() instead. This method calls the v1 API.
     */
    public function generatePayment(
        float $amount,
        ?string $to = null,
        ?string $rpc = null,
        ?string $token = null,
        ?string $successUrl = null
    ): array {
        $to = $to ?? config('eppay.default_beneficiary');
        $successUrl = $successUrl ?? config('eppay.success_url');

        if (empty($to) || empty($rpc) || empty($token)) {
            throw new \Exception('Legacy v1 requires $to, $rpc, and $token. Consider using createPayment() with network + token_type instead.');
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
        ])->post($this->baseUrl . '/api/generate-code', $payload);

        if (!$response->successful()) {
            throw new \Exception('Failed to generate payment: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * @deprecated Use checkStatus() instead. This method calls the v1 API.
     */
    public function verifyPayment(string $paymentId): array
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
        ])->get($this->baseUrl . '/api/payment-status/' . $paymentId);

        if (!$response->successful()) {
            throw new \Exception('Failed to verify payment: ' . $response->body());
        }

        return $response->json();
    }
}
