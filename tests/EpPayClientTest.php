<?php

namespace EpPay\LaravelEpPay\Tests;

use EpPay\LaravelEpPay\EpPayClient;
use Orchestra\Testbench\TestCase;
use EpPay\LaravelEpPay\EpPayServiceProvider;

class EpPayClientTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [EpPayServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('eppay.api_key', 'test_api_key');
        $app['config']->set('eppay.base_url', 'https://eppay.io');
    }

    /** @test */
    public function it_can_be_instantiated()
    {
        $client = new EpPayClient();
        $this->assertInstanceOf(EpPayClient::class, $client);
    }

    /** @test */
    public function it_throws_exception_when_api_key_is_missing()
    {
        config(['eppay.api_key' => null]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('EpPay API key is not configured');

        new EpPayClient();
    }

    /** @test */
    public function it_generates_qr_code_url()
    {
        $client = new EpPayClient();
        $paymentId = 'test_payment_123';

        $qrUrl = $client->getQrCodeUrl($paymentId);

        $this->assertEquals('https://eppay.io/qr-code/test_payment_123', $qrUrl);
    }

    /** @test */
    public function it_generates_payment_url()
    {
        $client = new EpPayClient();
        $paymentId = 'test_payment_123';

        $paymentUrl = $client->getPaymentUrl($paymentId);

        $this->assertEquals('https://eppay.io/payment/test_payment_123', $paymentUrl);
    }
}
