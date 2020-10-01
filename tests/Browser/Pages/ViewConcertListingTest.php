<?php

declare(strict_types = 1);

namespace Tests\Browser\Pages;

use App\Models\Concert;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Support\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Symfony\Component\HttpFoundation\Response;

class ViewConcertListingTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function user_can_view_a_published_concert_listing(): void
    {
        /** @var Concert */
        $concert = Concert::factory()->published()->create([
            'title' => 'The Red Chord',
            'subtitle' => 'with Animosity and Lethargy',
            'date' => Carbon::parse('December 13, 2020 8:00pm'),
            'ticket_price' => 3250,
            'venue' => 'The Mosh Pit',
            'venue_address' => '123 Example Lane',
            'city' => 'Laraville',
            'state' => 'ON',
            'zip' => '17916',
            'additional_information' => 'For tickets, call (555) 555-5555.',
        ]);

        $this->browse(function (Browser $browser) use ($concert) {
            $browser->visit('/concerts/' . $concert->id)
                ->assertSee('The Red Chord')
                ->assertSee('with Animosity and Lethargy')
                ->assertSee('December 13, 2020')
                ->assertSee('8:00pm')
                ->assertSee('32.50')
                ->assertSee('The Mosh Pit')
                ->assertSee('123 Example Lane')
                ->assertSee('Laraville, ON 17916')
                ->assertSee('For tickets, call (555) 555-5555.');
        });
    }

    /** @test */
    public function user_can_not_view_unpublished_concert_listing(): void
    {
        /** @var Concert */
        $concert = Concert::factory()->unpublished()->create();

        $this->browse(function (Browser $browser) use ($concert) {
            $browser->visit('/concerts/' . $concert->id)
                ->assertDontSee($concert->title)
                ->assertDontSee($concert->subtitle);
        });

        $response = $this->get('/concerts/' . $concert->id);
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
