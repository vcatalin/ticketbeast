<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Concert;
use App\Models\Ticket;
use App\Reservation;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Collection;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class ReservationTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function calculating_the_total_cost(): void
    {
        $tickets = new Collection([
            (object) ['price' => 1200],
            (object) ['price' => 1200],
            (object) ['price' => 1200],
        ]);

        $reservation = new Reservation($tickets, 'john@example.com');

        $this->assertEquals(3600, $reservation->totalCost());
    }

    /** @test */
    public function reserved_tickets_are_released_when_a_reservation_is_cancelled(): void
    {
        $tickets = new Collection([
            Mockery::spy(Ticket::class),
            Mockery::spy(Ticket::class),
            Mockery::spy(Ticket::class),
        ]);

        $reservation = new Reservation($tickets, 'john@example.com');

        $reservation->cancel();

        foreach ($tickets as $ticket) {
            /** @var MockInterface $ticket */
            $ticket->shouldHaveReceived('release')->once();
        }
    }

    /** @test */
    public function retrieving_the_reservation_tickets(): void
    {
        $tickets = new Collection([
            (object) ['price' => 1200],
            (object) ['price' => 1200],
            (object) ['price' => 1200],
        ]);

        $reservation = new Reservation($tickets,'john@example.com');

        $this->assertEquals($tickets, $reservation->tickets());
    }

    /** @test */
    public function retrieving_the_reservation_email(): void
    {
        $tickets = new Collection([]);

        $reservation = new Reservation($tickets,'john@example.com');

        $this->assertEquals('john@example.com', $reservation->email());
    }

    /** @test */
    public function completing_a_reservation(): void
    {
        /** @var Concert $concert */
        $concert = Concert::factory()->published()->create(['ticket_price' => 1200]);

        /** @var Collection $tickets */
        $tickets = Ticket::factory()->count(3)->create(['concert_id' => $concert->id]);

        $reservation = new Reservation($tickets, 'john@example.com');
        $order = $reservation->complete();

        $this->assertEquals('john@example.com', $order->email);
        $this->assertEquals(3, $order->ticketQuantity());
        $this->assertEquals(3600, $order->amount);
    }
}
