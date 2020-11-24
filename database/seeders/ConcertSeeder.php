<?php

declare(strict_types = 1);

namespace Database\Seeders;

use App\Models\Concert;
use App\Models\Ticket;
use Illuminate\Database\Seeder;

class ConcertSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $publishedConcerts = Concert::factory()->published()->count(10)->create();
        foreach ($publishedConcerts as $concert) {
            Ticket::factory()->create([
                'concert_id' => $concert->id,
                'code' => null,
            ]);
        }

        Concert::factory()->unpublished()->count(10)->create();
    }
}
