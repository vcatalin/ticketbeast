<?php

declare(strict_types=1);

namespace Tests\Browser\Pages;

use App\Models\Concert;
use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\DuskTestCase;

class ViewOrderTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function user_can_view_their_order_confirmation(): void
    {
        $concert = Concert::factory()->create();
        $order = Order::factory()->create();
        $ticket = Ticket::factory()->create();
    }
}
