<?php

declare(strict_types=1);

namespace Tests\Feature\Backstage;

use App\Models\Concert;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AddConcertTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function promoters_can_view_the_concert_form(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/backstage/concerts/new');

        $response->assertStatus(Response::HTTP_OK);
    }

    /** @test */
    public function guests_can_not_view_the_concert_form(): void
    {
        $response = $this->get('/backstage/concerts/new');

        $response->assertStatus(Response::HTTP_FOUND);
        $response->assertRedirect('/login');
    }

    /** @test */
    public function adding_a_valid_concert(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/backstage/concerts', $this->getRequestData());

        /** @var Concert $concert */
        $concert = Concert::first();

        $response->assertStatus(Response::HTTP_FOUND);
        $response->assertRedirect("/concerts/{$concert->id}");

        $this->assertTrue($concert->user->is($user));
        $this->assertEquals('No Warning', $concert->title);
        $this->assertEquals('with Cruel Hand and Backtrack', $concert->subtitle);
        $this->assertEquals('You must be 19 years of age to attend this concert.', $concert->additional_information);
        $this->assertEquals(Carbon::parse('2017-11-18 8:00pm'), $concert->date);
        $this->assertEquals('The Mosh Pit', $concert->venue);
        $this->assertEquals('123 Fake St.', $concert->venue_address);
        $this->assertEquals('Laraville', $concert->city);
        $this->assertEquals('ON', $concert->state);
        $this->assertEquals('12345', $concert->zip);
        $this->assertEquals(3250, $concert->ticket_price);
        $this->assertEquals(75, $concert->ticketsRemaining());
    }

    /** @test */
    public function guests_cannot_add_new_concerts(): void
    {
        $response = $this->post('/backstage/concerts', $this->getRequestData());

        $response->assertStatus(Response::HTTP_FOUND);
        $response->assertRedirect('/login');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function subtitle_is_optional(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post(
            '/backstage/concerts',
            $this->getRequestData(['subtitle' => ''])
        );

        /** @var Concert $concert */
        $concert = Concert::first();

        $response->assertStatus(Response::HTTP_FOUND);
        $response->assertRedirect("/concerts/{$concert->id}");

        $this->assertTrue($concert->user->is($user));
        $this->assertNull($concert->subtitle);
    }

    /** @test */
    public function additional_information_is_optional(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post(
            '/backstage/concerts',
            $this->getRequestData(['additional_information' => ''])
        );

        /** @var Concert $concert */
        $concert = Concert::first();

        $response->assertStatus(Response::HTTP_FOUND);
        $response->assertRedirect("/concerts/{$concert->id}");

        $this->assertTrue($concert->user->is($user));
        $this->assertNull($concert->additional_information);
    }

    /**
     * @test
     * @dataProvider invalidInputData
     */
    public function invalid_concert_request_data(array $requestData, string $error): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts', $requestData);

        $response->assertRedirect('/backstage/concerts/new');
        $response->assertSessionHasErrors($error);
        $this->assertEquals(0, Concert::count());

    }

    public function getRequestData(array $overrides = []): array
    {
        return array_merge([
            'title' => 'No Warning',
            'subtitle' => 'with Cruel Hand and Backtrack',
            'additional_information' => 'You must be 19 years of age to attend this concert.',
            'date' => '2017-11-18',
            'time' => '8:00pm',
            'venue' => 'The Mosh Pit',
            'venue_address' => '123 Fake St.',
            'city' => 'Laraville',
            'state' => 'ON',
            'zip' => '12345',
            'ticket_price' => '32.50',
            'ticket_quantity' => '75',
        ], $overrides);
    }

    public function invalidInputData(): array
    {
        return [
            'venue name is required' => [
                'data' => $this->getRequestData(['venue' => '']),
                'error' => 'venue',
            ],
            'venue address is required' => [
                'data' => $this->getRequestData(['venue_address' => '']),
                'error' => 'venue_address',
            ],
            'title is required' => [
                'data' => $this->getRequestData(['title' => '']),
                'error' => 'title',
            ],
            'city is required' => [
                'data' => $this->getRequestData(['city' => '']),
                'error' => 'city',
            ],
            'state is required' => [
                'data' => $this->getRequestData(['state' => '']),
                'error' => 'state',
            ],
            'zip is required' => [
                'data' => $this->getRequestData(['zip' => '']),
                'error' => 'zip',
            ],
            'ticket price is required' => [
                'data' => $this->getRequestData(['ticket_price' => '']),
                'error' => 'ticket_price',
            ],
            'ticket price must be numeric' => [
                'data' => $this->getRequestData(['ticket_price' => 'not a price']),
                'error' => 'ticket_price',
            ],
            'ticket price must be at least 5' => [
                'data' => $this->getRequestData(['ticket_price' => '4.99']),
                'error' => 'ticket_price',
            ],
            'ticket quantity is required' => [
                'data' => $this->getRequestData(['ticket_quantity' => '']),
                'error' => 'ticket_quantity',
            ],
            'ticket quantity must be at least 1' => [
                'data' => $this->getRequestData(['ticket_quantity' => '0']),
                'error' => 'ticket_quantity',
            ],
            'date is required' => [
                'data' => $this->getRequestData(['date' => '']),
                'error' => 'date',
            ],
            'date is a valid date' => [
                'data' => $this->getRequestData(['date' => 'not-a-valid-date']),
                'error' => 'date',
            ],
            'time is required' => [
                'data' => $this->getRequestData(['time' => '']),
                'error' => 'time',
            ],
            'time is a valid time' => [
                'data' => $this->getRequestData(['time' => 'not-a-time']),
                'error' => 'time',
            ],
        ];
    }
}
