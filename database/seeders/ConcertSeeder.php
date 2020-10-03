<?php

namespace Database\Seeders;

use App\Models\Concert;
use Illuminate\Support\Carbon;
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
        Concert::factory()->count(10)->create([
            'published_at' =>  Carbon::parse('-1 week'),
        ]);

        Concert::factory()->count(10)->create();
    }
}
