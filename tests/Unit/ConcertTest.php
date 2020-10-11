<?php

declare(strict_types = 1);

namespace Tests\Unit;

use App\Billing\Exceptions\NotEnoughTicketsException;
use App\Models\Concert;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class ConcertTest extends TestCase
{
    use DatabaseMigrations;

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
    public function order_concert_tickets(): void
    {
        $ticketQuantity = 3;

        /** @var Concert $concert */
        $concert = Concert::factory()->create();
        $concert->addTickets($ticketQuantity);

        $order = $concert->orderTickets('jane@example.com', $ticketQuantity);

        $this->assertEquals('jane@example.com', $order->email);
        $this->assertEquals($ticketQuantity, $order->tickets()->count());
    }

    /** @test */
    public function can_add_tickets(): void
    {
        /** @var Concert $concert */
        $concert = Concert::factory()->create();
        $concert->addTickets(50);

        $this->assertEquals(50, $concert->ticketsRemaining());
    }

    /** @test */
    public function tickets_remaining_does_not_include_tickets_tickets_associated_with_an_order(): void
    {
        /** @var Concert $concert */
        $concert = Concert::factory()->create();
        $concert->addTickets(50);

        $ticketQuantity = 30;

        $concert->orderTickets('jane@example.com', $ticketQuantity);

        $this->assertEquals(20, $concert->ticketsRemaining());
    }

    /** @test */
    public function purchasing_more_tickets_than_remain_throws_exception(): void
    {
        /** @var Concert $concert */
        $concert = Concert::factory()->create();
        $concert->addTickets(10);

        $email = 'john@example.com';

        try {
            $concert->orderTickets($email, 11);
        } catch (NotEnoughTicketsException $e) {
            /** @var Order $order */
            $order = $concert->orders()->where('email', $email)->first();
            $this->assertNull($order);
            $this->assertEquals(10, $concert->ticketsRemaining());
            return;
        }

        $this->fail('Order succeeded even though there were not enough tickets remaining.');
    }

    /** @test */
    public function can_not_order_tickets_that_have_been_already_been_purchased(): void
    {
        /** @var Concert $concert */
        $concert = Concert::factory()->create();
        $concert->addTickets(10);

        $concert->orderTickets('jane@example.com', 8);

        try {
            $concert->orderTickets('john@example.com', 3);
        } catch (NotEnoughTicketsException $e) {
            /** @var Order $order */
            $order = $concert->orders()->where('email', 'john@example.com')->first();
            $this->assertNull($order);
            $this->assertEquals(2, $concert->ticketsRemaining());
            return;
        }

        $this->fail('Order succeeded even though there were not enough tickets remaining.');
    }
}
