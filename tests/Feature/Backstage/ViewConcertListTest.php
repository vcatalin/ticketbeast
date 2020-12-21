<?php

declare(strict_types=1);

namespace Tests\Feature\Backstage;

use App\Models\Concert;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class ViewConcertListTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function guests_can_not_view_a_promoters_concert_list(): void
    {
        $response = $this->get('/backstage/concerts');

        $response->assertStatus(Response::HTTP_FOUND);
        $response->assertRedirect('/login');
    }

    /** @test */
    public function promoters_can_only_view_a_list_of_their_own_concerts(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $concertA = Concert::factory()->create(['user_id' => $user->id]);
        $concertB = Concert::factory()->create(['user_id' => $user->id]);
        $concertC = Concert::factory()->create(['user_id' => $otherUser->id]);
        $concertD = Concert::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/backstage/concerts');

        $response->assertStatus(Response::HTTP_OK);

        $this->assertTrue($response->original->getData()['concerts']->contains($concertA));
        $this->assertTrue($response->original->getData()['concerts']->contains($concertB));
        $this->assertTrue($response->original->getData()['concerts']->contains($concertD));
        $this->assertFalse($response->original->getData()['concerts']->contains($concertC));
    }
}
