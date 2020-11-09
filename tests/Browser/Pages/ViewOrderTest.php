<?php

declare(strict_types=1);

namespace Tests\Browser\Pages;

use App\Models\Concert;
use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;
use Tests\DuskTestCase;

class ViewOrderTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function user_can_view_their_order_confirmation(): void
    {
        $concert = Concert::factory()->published()->create([
            'title' => 'The Red Chord',
            'subtitle' => 'with Animosity and Lethargy',
            'date' => Carbon::parse('December 13, 2020 8:00pm'),
            'venue' => 'The Mosh Pit',
            'venue_address' => '123 Example Lane',
            'city' => 'Laraville',
            'state' => 'ON',
            'zip' => '17916',
        ]);

        $order = Order::factory()->create([
            'confirmation_number' => 'FOOBAR123',
            'amount' => '8500',
            'card_last_four' => '4242',
        ]);
        $ticketA = Ticket::factory()->create([
            'concert_id' => $concert->id,
            'order_id' => $order->id,
            'code' => 'TICKETCODE123',
        ]);
        $ticketB = Ticket::factory()->create([
            'concert_id' => $concert->id,
            'order_id' => $order->id,
            'code' => 'TICKETCODE456',
        ]);


        $response = $this->get('/orders/' . $order->confirmation_number);
        $response->assertStatus(Response::HTTP_OK);
        $response->assertViewHas('order', $order);
        $response->assertSee($order->confirmation_number);
        $response->assertSee('$85.00');
        $response->assertSee('**** **** **** 4242');
        $response->assertSee('TICKETCODE123');
        $response->assertSee('TICKETCODE456');
        $response->assertSee('The Red Chord');
        $response->assertSee('with Animosity and Lethargy');
        $response->assertSee('The Mosh Pit');
        $response->assertSee('123 Example Lane');
        $response->assertSee('Laraville, ON');
        $response->assertSee('17916');
        $response->assertSee('john@example.com');

        $response->assertSee('2020-12-13');
        $response->assertSee('8:00pm');
    }
}
