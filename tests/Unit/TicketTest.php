<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Concert;
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
}
