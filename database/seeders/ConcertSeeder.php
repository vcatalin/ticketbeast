<?php

declare(strict_types = 1);

namespace Database\Seeders;

use App\Models\Concert;
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
        Concert::factory()->published()->count(10)->create();
        Concert::factory()->unpublished()->count(10)->create();
    }
}
