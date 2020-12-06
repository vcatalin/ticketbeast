<?php

declare(strict_types=1);

namespace Tests\Browser\Pages;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PromoterLoginTest extends DuskTestCase
{
    use DatabaseMigrations;

    /** @test */
    public function logging_in_successfully(): void
    {
        $email = 'jane@example.com';
        $password = 'super-secret-password';

        $user = User::factory()->create([
            'email' => $email,
            'password' => bcrypt($password),
        ]);

        $this->browse(function (Browser $browser) use ($user, $password) {
           $browser->visit('login')
               ->type('email', $user->email)
               ->type('password', $password)
               ->press('Log in')
               ->assertPathIs('/backstage/concerts/new');
        });
    }

    /** @test */
    public function logging_in_with_invalid_credentials(): void
    {
        $email = 'jane@example.com';
        $password = 'super-secret-password';

        $user = User::factory()->create([
            'email' => $email,
            'password' => bcrypt($password),
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->visit('login')
                ->type('email', $user->email)
                ->type('password', 'wrong-password')
                ->press('Log in')
                ->assertPathIs('/login')
                ->assertSee('credentials do not match');
        });
    }
}
