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
        $publishedConcerts = Concert::factory()->published()->count(6)->create(['user_id' => 1]);
        foreach ($publishedConcerts as $concert) {
            Ticket::factory()->create([
                'concert_id' => $concert->id,
                'code' => null,
            ]);
        }

        $unpublishedConcerts = Concert::factory()->unpublished()->count(5)->create(['user_id' => 1]);
        foreach ($unpublishedConcerts as $concert) {
            Ticket::factory()->create([
                'concert_id' => $concert->id,
                'code' => null,
            ]);
        }

    }
}
