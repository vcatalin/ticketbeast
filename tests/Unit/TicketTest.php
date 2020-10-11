<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Concert;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use DatabaseMigrations;

    private const CUSTOMER_EMAIL = 'john@example.com';

    /** @test */
    public function a_ticket_can_be_released(): void
    {
        /** @var Concert $concert */
        $concert = Concert::factory()->published()->create()->addTickets(1);

        $order = $concert->orderTickets(self::CUSTOMER_EMAIL, 1);

        /** @var Ticket $ticket */
        $ticket = $order->tickets()->first();

        $this->assertEquals($order->id, $ticket->order_id);

        $ticket->release();

        $this->assertNull($ticket->fresh()->order_id);
    }
}
