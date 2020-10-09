<?php

declare(strict_types = 1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Concert;
use App\Billing\FakePaymentGateway;
use App\Billing\PaymentGateway;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response;

class PurchaseTicketsTest extends TestCase
{
    use DatabaseMigrations;

    private FakePaymentGateway $paymentGateway;

    protected function setUp(): void
    {
        parent::setUp();
        $this->paymentGateway = new FakePaymentGateway();
        $this->app->instance(PaymentGateway::class, $this->paymentGateway);
    }

    /** @test */
    public function can_purchase_published_concerts(): void
    {
        $concert = Concert::factory()->published()->create();

        $ticketQuantity = 3;
        $response = $this->json('POST', "/concerts/{$concert->id}/orders", [
            'email' => 'john@example.com',
            'ticket_quantity' => $ticketQuantity,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $this->assertEquals($concert->ticket_price * $ticketQuantity, $this->paymentGateway->totalCharges());
        $order = $concert->orders()->where('email', 'john@example.com')->first();
        $this->assertNotNull($order);
        $this->assertEquals(3, $order->tickets()->count());
    }

    /** @test */
    public function order_not_created_with_invalid_token(): void
    {
        $concert = Concert::factory()->published()->create();

        $response = $this->json('POST', "/concerts/{$concert->id}/orders", [
            'ticket_quantity' => 1,
            'payment_token' => 'not-a-valid-token',
            'email' => 'john@example.com',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $order = $concert->orders()->where('email', 'john@example.com')->first();
        $this->assertNull($order);
    }

    /** @test */
    public function can_not_purchase_unpublished_concert(): void
    {
        $concert = Concert::factory()->unpublished()->create();
        $response = $this->json('POST', "/concerts/{$concert->id}/orders", [
            'ticket_quantity' => 1,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
            'email' => 'john@example.com',
        ]);

        $response->assertStatus(Response::HTTP_NOT_FOUND);
        $this->assertEquals(0, $concert->orders()->count());
        $this->assertEquals(0, $this->paymentGateway->totalCharges());
    }

    /**
     * @test
     * @dataProvider validationData
    */
    public function validate_input_request(array $data, int $status, string $errorKey): void
    {
        $concert = Concert::factory()->published()->create();

        $response = $this->json('POST', "/concerts/{$concert->id}/orders", $data);

        $this->assertValidationError($response, $status, $errorKey);

    }

    private function assertValidationError(
        TestResponse $testResponse,
        int $status,
        string $errorKey
    ): void {
        $testResponse->assertStatus($status);
        $this->assertArrayHasKey($errorKey, $testResponse->json('errors'));
    }

    public function validationData()
    {
        return [
            'email_is_required_to_purchase_tickets' => [
                'data' => [
                    'ticket_quantity' => 3,
                    'payment_token' => 'test-token',
                ],
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'errorKey' => 'email',
            ],
            'email_must_be_valid_to_purchase_tickets' => [
                'data' => [
                    'email' => 'not-a-valid-email',
                    'ticket_quantity' => 3,
                    'payment_token' => 'test-token'
                ],
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'errorKey' => 'email',
            ],
            'ticket_quantity_is_required_to_purchase_tickets' => [
                'data' => [
                    'email' => 'john@example.com',
                    'payment_token' => 'test-token',
                ],
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'errorKey' => 'ticket_quantity',
            ],
            'ticket_quantity_must_be_at_least_1_to_purchase' => [
                'data' => [
                    'email' => 'john@example.com',
                    'ticket_quantity' => 0,
                    'payment_token' => 'test-token',
                ],
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'errorKey' => 'ticket_quantity',
            ],
            'payment_token_is_required' => [
                'data' => [
                    'email' => 'john@example.com',
                    'ticket_quantity' => 1,
                ],
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'errorKey' => 'payment_token',
            ]
        ];
    }
}
