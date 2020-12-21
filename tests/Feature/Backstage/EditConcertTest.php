<?php

declare(strict_types=1);

namespace Tests\Feature\Backstage;

use App\Models\Concert;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

// TODO All tests need to run green
class EditConcertTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function promoters_can_view_the_edit_form_for_their_own_unpublished_concerts(): void
    {
        $this->disableExceptionHandling();

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
    public function promoters_see_a404_when_attempting_to_view_the_edit_form_for_a_concert_that_does_not_exist(): void
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
}
