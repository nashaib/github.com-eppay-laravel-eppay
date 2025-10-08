<?php

namespace EpPay\LaravelEpPay\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array generatePayment(float $amount, ?string $to = null, ?string $rpc = null, ?string $token = null, ?string $successUrl = null)
 * @method static array verifyPayment(string $paymentId)
 * @method static bool isPaymentCompleted(string $paymentId)
 * @method static array|null getPaymentDetails(string $paymentId)
 * @method static string getQrCodeData(string $paymentId)
 * @method static string getQrCodeUrl(string $paymentId, int $size = 300)
 * @method static string getPaymentUrl(string $paymentId)
 *
 * @see \EpPay\LaravelEpPay\EpPayClient
 */
class EpPay extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'eppay';
    }
}
