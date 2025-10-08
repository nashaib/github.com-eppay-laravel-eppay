<?php

namespace EpPay\LaravelEpPay;

use Illuminate\Support\ServiceProvider;
use EpPay\LaravelEpPay\View\Components\PaymentQr;

class EpPayServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__.'/../config/eppay.php', 'eppay'
        );

        // Register the EpPayClient as a singleton
        $this->app->singleton('eppay', function ($app) {
            return new EpPayClient();
        });

        // Register the alias
        $this->app->alias('eppay', EpPayClient::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish config
        $this->publishes([
            __DIR__.'/../config/eppay.php' => config_path('eppay.php'),
        ], 'eppay-config');

        // Publish views
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/eppay'),
        ], 'eppay-views');

        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'eppay');

        // Register Blade components
        $this->loadViewComponentsAs('eppay', [
            PaymentQr::class,
        ]);

        // Register routes
        $this->loadRoutesFrom(__DIR__.'/routes.php');
    }
}
