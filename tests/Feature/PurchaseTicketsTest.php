<?php

declare(strict_types = 1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Concert;
use App\Billing\FakePaymentGateway;
use App\Billing\PaymentGateway;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Symfony\Component\HttpFoundation\Response;

class PurchaseTicketsTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->paymentGateway = new FakePaymentGateway();
        $this->app->instance(PaymentGateway::class, $this->paymentGateway);
    }

    /** @test */
    public function successful_concert_purchase(): void
    {
        $concert = Concert::factory()->create();

        $response = $this->json('POST', "/concerts/{$concert->id}/orders", [
            'email' => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $response->assertStatus(Response::HTTP_ACCEPTED);

        $this->assertEquals(2000 * 3, $this->paymentGateway->totalCharges());
        $order = $concert->orders()->where('email', 'john@example.com')->first();
        $this->assertNotNull($order);
        $this->assertEquals(3, $order->tickets()->count());
    }

    /** @test */
    function email_is_required_to_purchase_tickets(): void
    {
        $concert = Concert::factory()->create();

        $response = $this->json('POST', "/concerts/{$concert->id}/orders", [
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertArrayHasKey('email', $response->json('errors'));
    }
}
