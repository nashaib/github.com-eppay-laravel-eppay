<?php

namespace EpPay\LaravelEpPay\View\Components;

use Illuminate\View\Component;
use EpPay\LaravelEpPay\Facades\EpPay;

class PaymentQr extends Component
{
    public string $paymentId;
    public array $paymentData;
    public string $qrCodeUrl;
    public string $paymentUrl;
    public int $pollingInterval;
    public bool $autoRefresh;
    public ?string $successUrl;
    public ?string $cancelUrl;

    /**
     * Create a new component instance.
     */
    public function __construct(
        string $paymentId,
        ?array $paymentData = null,
        bool $autoRefresh = true,
        ?string $successUrl = null,
        ?string $cancelUrl = null
    ) {
        $this->paymentId = $paymentId;
        $this->paymentData = $paymentData ?? [];
        $this->qrCodeUrl = EpPay::getQrCodeUrl($paymentId);
        $this->paymentUrl = EpPay::getPaymentUrl($paymentId);
        $this->pollingInterval = config('eppay.polling_interval', 3000);
        $this->autoRefresh = $autoRefresh;
        $this->successUrl = $successUrl;
        $this->cancelUrl = $cancelUrl;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('eppay::components.payment-qr');
    }
}
