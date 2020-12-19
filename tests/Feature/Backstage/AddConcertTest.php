<?php

declare(strict_types=1);

namespace Tests\Feature\Backstage;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Response;
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
        $response = $this->get('backstage/concerts/new');

        $response->assertStatus(Response::HTTP_FOUND);
        $response->assertRedirect('/login');
    }
}
