<?php

declare(strict_types = 1);

namespace Tests\Unit;

use App\Billing\Exceptions\NotEnoughTicketsException;
use App\Models\Concert;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class ConcertTest extends TestCase
{
    use DatabaseMigrations;

    private const CUSTOMER_EMAIL = 'john@example.com';

    /** @test */
    public function retrieved_date_is_formatted(): void
    {
        /** @var Concert $concert */
        $concert = Concert::factory()->make([
                'date' => Carbon::parse('2016-12-01 8:00pm'),
        ]);

        $this->assertEquals('December 1, 2016', $concert->formatted_date);
    }

    /** @test */
    public function retrieved_start_time_is_formatted(): void
    {
        /** @var Concert $concert */
        $concert = Concert::factory()->make([
            'date' => Carbon::parse('2016-12-01 17:00:00'),
        ]);

        $this->assertEquals('5:00pm', $concert->formatted_start_time);
    }

    /** @test */
    public function retrieved_ticket_price_is_formatted_properly_in_dollars(): void
    {
        /** @var Concert $concert */
        $concert = Concert::factory()->make([
            'ticket_price' => 8550,
        ]);

        $this->assertEquals('85.50', $concert->formatted_ticket_price);
    }

    /** @test */
    public function concerts_with_a_published_at_date_are_published(): void
    {
        /** @var Concert */
        $publishedConcertA = Concert::factory()->published()->create();
        /** @var Concert */
        $publishedConcertB = Concert::factory()->published()->create();
        /** @var Concert */
        $unpublished = Concert::factory()->unpublished()->create();

        $publishedConcerts = Concert::published()->get();

        $this->assertTrue($publishedConcerts->contains($publishedConcertA));
        $this->assertTrue($publishedConcerts->contains($publishedConcertB));
        $this->assertFalse($publishedConcerts->contains($unpublished));
    }

    /** @test */
    public function can_reserve_available_tickets(): void
    {
        /** @var Concert $concert */
        $concert = Concert::factory()->published()->create()->addTickets(3);

        $this->assertEquals(3, $concert->ticketsRemaining());

        $reservedTickets = $concert->reserveTickets(2);

        $this->assertCount(2, $reservedTickets);
        $this->assertEquals(1, $concert->ticketsRemaining());
    }

    /** @test */
    public function order_concert_tickets(): void
    {
        $ticketQuantity = 3;

        /** @var Concert $concert */
        $concert = Concert::factory()->create()->addTickets($ticketQuantity);

        $order = $concert->orderTickets(self::CUSTOMER_EMAIL, $ticketQuantity);

        $this->assertEquals(self::CUSTOMER_EMAIL, $order->email);
        $this->assertEquals($ticketQuantity, $order->ticketQuantity());
    }

    /** @test */
    public function can_add_tickets(): void
    {
        /** @var Concert $concert */
        $concert = Concert::factory()->create()->addTickets(50);

        $this->assertEquals(50, $concert->ticketsRemaining());
    }

    /** @test */
    public function tickets_remaining_does_not_include_tickets_associated_with_an_order(): void
    {
        /** @var Concert $concert */
        $concert = Concert::factory()->create()->addTickets(50);

        $concert->orderTickets(self::CUSTOMER_EMAIL, 30);

        $this->assertEquals(20, $concert->ticketsRemaining());
    }

    /** @test */
    public function purchasing_more_tickets_than_remain_throws_exception(): void
    {
        /** @var Concert $concert */
        $concert = Concert::factory()->create()->addTickets(10);

        try {
            $concert->orderTickets(self::CUSTOMER_EMAIL, 11);
        } catch (NotEnoughTicketsException $e) {
            $this->assertFalse($concert->hasOrderFor(self::CUSTOMER_EMAIL));
            $this->assertEquals(10, $concert->ticketsRemaining());
            return;
        }

        $this->fail('Order succeeded even though there were not enough tickets remaining.');
    }

    /** @test */
    public function can_not_order_tickets_that_have_been_already_been_purchased(): void
    {
        /** @var Concert $concert */
        $concert = Concert::factory()->create()->addTickets(10);

        $concert->orderTickets('jane@example.com', 8);

        try {
            $concert->orderTickets(self::CUSTOMER_EMAIL, 3);
        } catch (NotEnoughTicketsException $e) {
            $this->assertFalse($concert->hasOrderFor(self::CUSTOMER_EMAIL));
            $this->assertEquals(2, $concert->ticketsRemaining());
            return;
        }

        $this->fail('Order succeeded even though there were not enough tickets remaining.');
    }
}
