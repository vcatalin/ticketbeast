<?php

declare(strict_types=1);

namespace Tests\Feature\Backstage;

use App\Models\Concert;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Carbon;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class EditConcertTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        TestResponse::macro('data', function (string $key) {
            return $this->original->getData()[$key];
        });
    }

    /** @test */
    public function promoters_can_view_the_edit_form_for_their_own_unpublished_concerts(): void
    {
        $user = User::factory()->create();
        /** @var Concert $concert */
        $concert = Concert::factory()->create(['user_id' => $user->id]);

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/edit");

        $response->assertStatus(Response::HTTP_OK);
        $this->assertTrue($response->data('concert')->is($concert));
    }

    /** @test */
    public function promoters_cannot_view_the_edit_form_for_their_own_published_concerts(): void
    {
        $user = User::factory()->create();
        /** @var Concert $concert */
        $concert = Concert::factory()->published()->create(['user_id' => $user->id]);

        $this->assertTrue($concert->isPublished());

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/edit");

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function promoters_cannot_view_the_edit_form_for_other_concerts(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $concert = Concert::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/edit");

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function promoters_see_a_404_when_attempting_to_view_the_edit_form_for_a_concert_that_does_not_exist(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get("/backstage/concerts/999/edit");

        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function guests_are_asked_to_login_when_attempting_to_view_the_edit_form_for_any_concert(): void
    {
        $user = User::factory()->create();
        $concert = Concert::factory()->create(['user_id' => $user->id]);

        $response = $this->get("/backstage/concerts/{$concert->id}/edit");

        $response->assertStatus(Response::HTTP_FOUND);
        $response->assertRedirect('/login');
    }

    /** @test */
    public function guests_are_asked_to_login_when_attempting_to_view_the_edit_form_for_a_concert_that_does_not_exist(): void
    {
        $response = $this->get('/backstage/concerts/999/edit');

        $response->assertStatus(Response::HTTP_FOUND);
        $response->assertRedirect('/login');
    }

    /** @test */
    public function promoters_can_edit_their_own_unpublished_concerts(): void
    {
        $this->disableExceptionHandling();
        $user = User::factory()->create();
        /** @var Concert $concert */
        $concert = Concert::factory()->create([
            'user_id' => $user->id,
            'title' => 'Old title',
            'subtitle' => 'Old subtitle',
            'additional_information' => 'Old additional information',
            'date' => Carbon::parse('2020-12-01 5:00pm'),
            'venue' => 'Old venue',
            'venue_address' => 'Old address',
            'city' => 'Old city',
            'state' => 'Old state',
            'zip' => '00000',
            'ticket_price' => 2000,
        ]);

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)
            ->from("/backstage/{$concert->id}/edit")
            ->patch("/backstage/concerts/{$concert->id}", [
                'title' => 'New title',
                'subtitle' => 'New subtitle',
                'additional_information' => 'New additional information',
                'date' => '2021-01-01',
                'time' => '8:00pm',
                'venue' => 'New venue',
                'venue_address' => 'New address',
                'city' => 'New city',
                'state' => 'New state',
                'zip' => '99999',
                'ticket_price' => '72.50',
            ]);

        $response->assertRedirect('/backstage/concerts');

        tap($concert->fresh(), function (Concert $concert) {
            $this->assertEquals('New title', $concert->title);
            $this->assertEquals('New subtitle', $concert->subtitle);
            $this->assertEquals('New additional information', $concert->additional_information);
            $this->assertEquals(Carbon::parse('2021-01-01 8:00pm'), $concert->date);
            $this->assertEquals('New venue', $concert->venue);
            $this->assertEquals('New address', $concert->venue_address);
            $this->assertEquals('New city', $concert->city);
            $this->assertEquals('New state', $concert->state);
            $this->assertEquals('99999', $concert->zip);
            $this->assertEquals(7250, $concert->ticket_price);
        });
    }

    /** @test */
    public function promoters_can_not_edit_other_unpublished_concerts(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        /** @var Concert $concert */
        $concert = Concert::factory()->create([
            'user_id' => $otherUser->id,
            'title' => 'Old title',
            'subtitle' => 'Old subtitle',
            'additional_information' => 'Old additional information',
            'date' => Carbon::parse('2020-12-01 5:00pm'),
            'venue' => 'Old venue',
            'venue_address' => 'Old address',
            'city' => 'Old city',
            'state' => 'Old state',
            'zip' => '00000',
            'ticket_price' => 2000,
        ]);

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)
            ->from("/backstage/{$concert->id}/edit")
            ->patch("/backstage/concerts/{$concert->id}", [
                'title' => 'New title',
                'subtitle' => 'New subtitle',
                'additional_information' => 'New additional information',
                'date' => '2021-01-01',
                'time' => '8:00pm',
                'venue' => 'New venue',
                'venue_address' => 'New address',
                'city' => 'New city',
                'state' => 'New state',
                'zip' => '99999',
                'ticket_price' => '72.50',
            ]);

        $response->assertStatus(Response::HTTP_NOT_FOUND);

        tap($concert->fresh(), function (Concert $concert) {
            $this->assertEquals('Old title', $concert->title);
            $this->assertEquals('Old subtitle', $concert->subtitle);
            $this->assertEquals('Old additional information', $concert->additional_information);
            $this->assertEquals(Carbon::parse('2020-12-01 5:00pm'), $concert->date);
            $this->assertEquals('Old venue', $concert->venue);
            $this->assertEquals('Old address', $concert->venue_address);
            $this->assertEquals('Old city', $concert->city);
            $this->assertEquals('Old state', $concert->state);
            $this->assertEquals('00000', $concert->zip);
            $this->assertEquals(2000, $concert->ticket_price);
        });
    }

    /** @test */
    public function promoters_can_not_edit_published_concerts(): void
    {
        $user = User::factory()->create();
        /** @var Concert $concert */
        $concert = Concert::factory()->published()->create([
            'user_id' => $user->id,
            'title' => 'Old title',
            'subtitle' => 'Old subtitle',
            'additional_information' => 'Old additional information',
            'date' => Carbon::parse('2020-12-01 5:00pm'),
            'venue' => 'Old venue',
            'venue_address' => 'Old address',
            'city' => 'Old city',
            'state' => 'Old state',
            'zip' => '00000',
            'ticket_price' => 2000,
        ]);

        $this->assertTrue($concert->isPublished());

        $response = $this->actingAs($user)
            ->from("/backstage/{$concert->id}/edit")
            ->patch("/backstage/concerts/{$concert->id}", [
                'title' => 'New title',
                'subtitle' => 'New subtitle',
                'additional_information' => 'New additional information',
                'date' => '2021-01-01',
                'time' => '8:00pm',
                'venue' => 'New venue',
                'venue_address' => 'New address',
                'city' => 'New city',
                'state' => 'New state',
                'zip' => '99999',
                'ticket_price' => '72.50',
            ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN);

        tap($concert->fresh(), function (Concert $concert) {
            $this->assertEquals('Old title', $concert->title);
            $this->assertEquals('Old subtitle', $concert->subtitle);
            $this->assertEquals('Old additional information', $concert->additional_information);
            $this->assertEquals(Carbon::parse('2020-12-01 5:00pm'), $concert->date);
            $this->assertEquals('Old venue', $concert->venue);
            $this->assertEquals('Old address', $concert->venue_address);
            $this->assertEquals('Old city', $concert->city);
            $this->assertEquals('Old state', $concert->state);
            $this->assertEquals('00000', $concert->zip);
            $this->assertEquals(2000, $concert->ticket_price);
        });
    }

    /** @test */
    public function guests_can_not_edit_concerts(): void
    {
        $user = User::factory()->create();
        /** @var Concert $concert */
        $concert = Concert::factory()->create([
            'user_id' => $user->id,
            'title' => 'Old title',
            'subtitle' => 'Old subtitle',
            'additional_information' => 'Old additional information',
            'date' => Carbon::parse('2020-12-01 5:00pm'),
            'venue' => 'Old venue',
            'venue_address' => 'Old address',
            'city' => 'Old city',
            'state' => 'Old state',
            'zip' => '00000',
            'ticket_price' => 2000,
        ]);

        $this->assertFalse($concert->isPublished());

        $response = $this
            ->from("/backstage/{$concert->id}/edit")
            ->patch("/backstage/concerts/{$concert->id}", [
                'title' => 'New title',
                'subtitle' => 'New subtitle',
                'additional_information' => 'New additional information',
                'date' => '2021-01-01',
                'time' => '8:00pm',
                'venue' => 'New venue',
                'venue_address' => 'New address',
                'city' => 'New city',
                'state' => 'New state',
                'zip' => '99999',
                'ticket_price' => '72.50',
            ]);

        $response->assertStatus(Response::HTTP_FOUND);
        $response->assertRedirect('/login');

        tap($concert->fresh(), function (Concert $concert) {
            $this->assertEquals('Old title', $concert->title);
            $this->assertEquals('Old subtitle', $concert->subtitle);
            $this->assertEquals('Old additional information', $concert->additional_information);
            $this->assertEquals(Carbon::parse('2020-12-01 5:00pm'), $concert->date);
            $this->assertEquals('Old venue', $concert->venue);
            $this->assertEquals('Old address', $concert->venue_address);
            $this->assertEquals('Old city', $concert->city);
            $this->assertEquals('Old state', $concert->state);
            $this->assertEquals('00000', $concert->zip);
            $this->assertEquals(2000, $concert->ticket_price);
        });
    }

    /** @test */
    public function title_is_required(): void
    {
        $user = User::factory()->create();
        /** @var Concert $concert */
        $concert = Concert::factory()->create();

        $response = $this->actingAs($user)
            ->from("/backstage/{$concert->id}/edit")
            ->patch(
                "/backstage/concerts/{$concert->id}",
                $this->getRequestData(['title' => ''])
            );

        $response->assertRedirect("/backstage/{$concert->id}/edit");
        $response->assertStatus(Response::HTTP_FOUND);
        $response->assertSessionHasErrors('title');
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
}
