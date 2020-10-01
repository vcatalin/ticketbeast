<?php

declare(strict_types = 1);

namespace Tests\Unit;

use Carbon\Carbon;
use Tests\TestCase;
use App\Models\Concert;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ConcertTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function retrieved_date_is_formatted(): void
    {
        $concert = Concert::factory()->make([
                'date' => Carbon::parse('2016-12-01 8:00pm'),
        ]);

        $this->assertEquals('December 1, 2016', $concert->formatted_date);
    }

    /** @test */
    public function retrieved_start_time_is_formatted(): void
    {
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
        $publishedConcertA = Concert::factory()->create([
            'published_at' =>  Carbon::parse('-1 week'),
        ]);

        /** @var Concert */
        $publishedConcertB = Concert::factory()->create([
            'published_at' =>  Carbon::parse('-1 week'),
        ]);

        /** @var Concert */
        $unpublished = Concert::factory()->create([
            'published_at' =>  null,
        ]);

        $publishedConcerts = Concert::published()->get();

        $this->assertTrue($publishedConcerts->contains($publishedConcertA));
        $this->assertTrue($publishedConcerts->contains($publishedConcertB));
        $this->assertFalse($publishedConcerts->contains($unpublished));
    }
}
