<?php

declare(strict_types=1);

namespace Tests\Browser\Pages;

use App\Models\Concert;
use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Symfony\Component\HttpFoundation\Response;
use Tests\DuskTestCase;

class ViewOrderTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function user_can_view_their_order_confirmation(): void
    {
        $concert = Concert::factory()->create();
        $order = Order::factory()->create([
            'confirmation_number' => 'FOOBAR123',
            'amount' => '8500',
            'card_last_four' => '4242',
        ]);
        $ticket = Ticket::factory()->create([
            'concert_id' => $concert->id,
            'order_id' => $order->id,
        ]);


        $response = $this->get('/orders/' . $order->confirmation_number);
        $response->assertStatus(Response::HTTP_OK);
        $response->assertViewHas('order', $order);
        $response->assertSee($order->confirmation_number);
        $response->assertSee('$85.00');
        $response->assertSee('**** **** **** ' . $order->card_last_four);
    }
}
