<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Facades\TicketCode;
use App\Models\Concert;
use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use DatabaseMigrations;

    private const CUSTOMER_EMAIL = 'john@example.com';

    /** @test */
    public function tickets_can_be_reserved(): void
    {
        $ticket = Ticket::factory()->create();
        $this->assertNull($ticket->reserved_at);
        $ticket->reserve();

        $this->assertNotNull($ticket->fresh()->reserved_at);
    }

    /** @test */
    public function a_ticket_can_be_released(): void
    {
        /** @var Ticket $ticket */
        $ticket = Ticket::factory()->reserved()->create();

        $ticket->release();

        $this->assertNull($ticket->fresh()->reserved_at);
    }

    /** @test */
    public function a_ticket_can_be_claimed_for_an_order(): void
    {
        $order = Order::factory()->create();
        $ticket = Ticket::factory()->create([
            'code' => null
        ]);

        TicketCode::shouldReceive('generate')->andReturn('TICKETCODE1');

        $ticket->claimFor($order);

        // Assert that the ticket is saved to the order
        $this->assertContains($ticket->id, $order->tickets->pluck('id'));
        // Assert that the ticket had the expected ticket code generated
        $this->assertEquals('TICKETCODE1', $ticket->code);
    }
}
