<?php

declare(strict_types = 1);

namespace Tests\Feature;

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentGateway;
use App\Models\Concert;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class PurchaseTicketsTest extends TestCase
{
    use DatabaseMigrations;

    private FakePaymentGateway $paymentGateway;

    private const CUSTOMER_EMAIL = 'john@example.com';

    protected function setUp(): void
    {
        parent::setUp();
        $this->paymentGateway = new FakePaymentGateway();
        $this->app->instance(PaymentGateway::class, $this->paymentGateway);
    }

    /** @test */
    public function can_purchase_published_concerts(): void
    {
        $ticketQuantity = 3;

        /** @var Concert $concert */
        $concert = Concert::factory()->published()->create()->addTickets($ticketQuantity);

        $response = $this->json('POST', "/concerts/{$concert->id}/orders", [
            'email' => self::CUSTOMER_EMAIL,
            'ticket_quantity' => $ticketQuantity,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $response->assertStatus(Response::HTTP_CREATED);

        $totalPrice = $concert->ticket_price * $ticketQuantity;

        $this->assertEquals($totalPrice, $this->paymentGateway->totalCharges());
        $this->assertTrue($concert->hasOrderFor(self::CUSTOMER_EMAIL));
        $this->assertEquals(
            $ticketQuantity,
            $concert->ordersFor(self::CUSTOMER_EMAIL)->first()->ticketQuantity(),
        );
    }

    /** @test */
    public function can_not_purchase_more_tickets_than_remain(): void
    {
        /** @var Concert $concert */
        $concert = Concert::factory()->published()->create()->addTickets(50);

        $ticketQuantity = 51;

        $response = $this->json('POST', "/concerts/{$concert->id}/orders", [
            'email' => self::CUSTOMER_EMAIL,
            'ticket_quantity' => $ticketQuantity,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertFalse($concert->hasOrderFor(self::CUSTOMER_EMAIL));
        $this->assertEquals(0, $this->paymentGateway->totalCharges());
        $this->assertEquals(50, $concert->ticketsRemaining());
    }

    /** @test */
    public function order_not_created_with_invalid_token(): void
    {
        /** @var Concert $concert */
        $concert = Concert::factory()->published()->create()->addTickets(1);

        $response = $this->json('POST', "/concerts/{$concert->id}/orders", [
            'ticket_quantity' => 1,
            'payment_token' => 'not-a-valid-token',
            'email' => self::CUSTOMER_EMAIL,
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertFalse($concert->hasOrderFor(self::CUSTOMER_EMAIL));
    }

    /** @test */
    public function can_not_purchase_unpublished_concert(): void
    {
        /** @var Concert $concert */
        $concert = Concert::factory()->unpublished()->create()->addTickets(1);

        $response = $this->json('POST', "/concerts/{$concert->id}/orders", [
            'ticket_quantity' => 1,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
            'email' => self::CUSTOMER_EMAIL,
        ]);

        $response->assertStatus(Response::HTTP_NOT_FOUND);
        $this->assertFalse($concert->hasOrderFor(self::CUSTOMER_EMAIL));
        $this->assertEquals(0, $this->paymentGateway->totalCharges());
    }

    /**
     * @test
     * @dataProvider validationData
    */
    public function validate_input_request(array $data, int $status, string $errorKey): void
    {
        /** @var Concert $concert */
        $concert = Concert::factory()->published()->create()->addTickets(1);

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
