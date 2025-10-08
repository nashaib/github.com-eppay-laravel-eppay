<?php

namespace EpPay\LaravelEpPay\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array generatePayment(float $amount, ?string $network = null, ?string $currency = null, array $metadata = [])
 * @method static array verifyPayment(string $paymentId)
 * @method static bool isPaymentCompleted(string $paymentId)
 * @method static array|null getPaymentDetails(string $paymentId)
 * @method static string getQrCodeUrl(string $paymentId)
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
