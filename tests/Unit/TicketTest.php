<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Facades\TicketCode;
use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\DatabaseMigrations;
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
        /** @var Order $order */
        $order = Order::factory()->create();
        /** @var Ticket $ticket */
        $ticket = Ticket::factory()->create([
            'code' => null
        ]);
        TicketCode::shouldReceive('generateFor')->with($ticket)->andReturn('TICKETCODE1');

        $ticket->claimFor($order);

        $this->assertContains($ticket->id, $order->tickets->pluck('id'));
        $this->assertEquals('TICKETCODE1', $ticket->code);
    }
}
