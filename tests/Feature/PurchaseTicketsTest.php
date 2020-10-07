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

    private PaymentGateway $paymentGateway;

    protected function setUp(): void
    {
        parent::setUp();
        $this->paymentGateway = new FakePaymentGateway();
        $this->app->instance(PaymentGateway::class, $this->paymentGateway);
    }

    /** @test */
    public function can_purchase_concerts(): void
    {
        $concert = Concert::factory()->create();

        $ticketQuantity = 3;
        $response = $this->json('POST', "/concerts/{$concert->id}/orders", [
            'email' => 'john@example.com',
            'ticket_quantity' => $ticketQuantity,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $response->assertStatus(Response::HTTP_ACCEPTED);

        $this->assertEquals($concert->ticket_price * $ticketQuantity, $this->paymentGateway->totalCharges());
        $order = $concert->orders()->where('email', 'john@example.com')->first();
        $this->assertNotNull($order);
        $this->assertEquals(3, $order->tickets()->count());
    }

    /**
     * @test
     * @dataProvider validationData
    */
    function validate_input_request(array $data, int $status, string $input): void
    {
        $concert = Concert::factory()->create();

        $response = $this->json('POST', "/concerts/{$concert->id}/orders", $data);

        $response->assertStatus($status);
        $this->assertArrayHasKey($input, $response->json('errors'));
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
                'inputKey' => 'email',
            ],
            'email_must_be_valid_to_purchase_tickets' => [
                'data' => [
                    'email' => 'not-a-valid-email',
                    'ticket_quantity' => 3,
                    'payment_token' => 'test-token'
                ],
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'inputKey' => 'email',
            ],
            'ticket_quantity_is_required_to_purchase_tickets' => [
                'data' => [
                    'email' => 'john@example.com',
                    'payment_token' => 'test-token',
                ],
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'inputKey' => 'ticket_quantity',
            ],
            'ticket_quantity_must_be_at_least_1_to_purchase' => [
                'data' => [
                    'email' => 'john@example.com',
                    'ticket_quantity' => 0,
                    'payment_token' => 'test-token',
                ],
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'inputKey' => 'ticket_quantity',
            ],
            'payment_token_is_required' => [
                'data' => [
                    'email' => 'john@example.com',
                    'ticket_quantity' => 1,
                ],
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'inputKey' => 'payment_token',
            ]
        ];
    }
}
