<?php

namespace App\Providers;

use App\Billing\PaymentGateway;
use App\Billing\StripePaymentGateway;
use App\HashIdTicketCodeGenerator;
use App\OrderConfirmationNumberGenerator;
use App\RandomOrderConfirmationNumberGenerator;
use App\TicketCodeGenerator;
use Illuminate\Support\ServiceProvider;
use Stripe\StripeClient;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(PaymentGateway::class, function () {
            return new StripePaymentGateway(
                new StripeClient(config('services.stripe.secret'))
            );
        });

        $this->app->bind(TicketCodeGenerator::class, function () {
            return new HashIdTicketCodeGenerator(
                config('app.ticket_code_salt')
            );
        });

        $this->app->bind(
            OrderConfirmationNumberGenerator::class,
            RandomOrderConfirmationNumberGenerator::class
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
