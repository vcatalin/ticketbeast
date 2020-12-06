<?php

declare(strict_types=1);

namespace Tests\Feature\Backstage;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class PromoterLoginTest extends TestCase
{
    use DatabaseMigrations;

    /** @test */
    public function logging_in_with_valid_credentials(): void
    {
        $user = User::factory()->create([
           'email' => 'jane@example.com',
           'password' => bcrypt('super-secret-password'),
        ]);

        $response = $this->json(
            'POST',
            '/login',
            [
                'email' => 'jane@example.com',
                'password' => 'super-secret-password',
            ]
        );
        $response->assertRedirect('/backstage/concerts/new');

        $this->assertTrue(Auth::check());
        $this->assertTrue(Auth::user()->is($user));
    }

    /** @test */
    public function logging_in_with_invalid_credentials(): void
    {
        $this->disableExceptionHandling();
        $user = User::factory()->create([
            'email' => 'jane@example.com',
            'password' => bcrypt('super-secret-password'),
        ]);

        $response = $this->json(
            'POST',
            '/login',
            [
                'email' => 'jane@example.com',
                'password' => 'not-the-right-password',
            ]
        );
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');

        $this->assertFalse(Auth::check());
    }

    /** @test */
    public function logging_in_with_an_account_that_does_not_exist(): void
    {
        $response = $this->json(
            'POST',
            '/login',
            [
                'email' => 'jane@example.com',
                'password' => 'not-the-right-password',
            ]
        );
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors();

        $this->assertFalse(Auth::check());
    }

    /** @test */
    public function logging_out_the_current_user(): void
    {
        $this->disableExceptionHandling();
        Auth::login(User::factory()->create());

        $response = $this->post('/logout');

        $response->assertRedirect('/login');
        $this->assertFalse(Auth::check());
    }
}
