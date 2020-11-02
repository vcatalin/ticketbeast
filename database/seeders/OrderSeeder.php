<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Concert;
use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /** @var Concert $concert */
        $concert = Concert::factory()->published()->create();

        $orderConfirmations = [
            'FOO123' => '8500',
            'BAR123' => '6500',
            'FOOBAR123' => '2500',
            'FOOBAR' => '4500',
            'FOO' => '8500',
            'BAR' => '8500',
        ];
        foreach ($orderConfirmations as $id => $amount) {
            $order = Order::factory()->create([
                'confirmation_number' => $id,
                'amount' => $amount,
                'card_last_four' => '4242',
            ]);

            Ticket::factory()->create([
                'concert_id' => $concert->id,
                'order_id' => $order->id,
            ]);
        }
    }
}
