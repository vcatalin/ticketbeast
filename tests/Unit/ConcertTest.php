<?php

declare(strict_types = 1);

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Concert;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ConcertTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function fetched_date_is_formatted(): void
    {
        // Create a concert with a known date
        $concert = Factory::factoryForModel(Concert::class)->create(
            [
                'date' => Carbon::parse('2016-12-01 8:00pm'),
            ]);

        // Retrieve the formatted date
        $date = $concert->formatted_date;

        // Verify the date is formatted as expected
        $this->assertEquals('December 1, 2016', $date);
    }
}
