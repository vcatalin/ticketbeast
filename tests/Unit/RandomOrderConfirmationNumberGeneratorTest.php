<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\RandomOrderConfirmationNumberGenerator;
use Tests\TestCase;

class RandomOrderConfirmationNumberGeneratorTest extends TestCase
{
    /** @test */
    public function must_be_24_characters_long(): void
    {
        $generator = new RandomOrderConfirmationNumberGenerator();
        $confirmationNumber = $generator->generate();

        $this->assertEquals(24, strlen($confirmationNumber));
    }

    /** @test */
    public function can_only_contain_uppercase_letters_and_numbers(): void
    {
        $generator = new RandomOrderConfirmationNumberGenerator();
        $confirmationNumber = $generator->generate();

        $this->assertMatchesRegularExpression('/^[A-Z0-9]+$/', $confirmationNumber);
    }

    /** @test */
    public function can_not_contain_ambiguous_characters(): void
    {
        $generator = new RandomOrderConfirmationNumberGenerator();
        $confirmationNumber = $generator->generate();

        $this->assertFalse(strpos($confirmationNumber, '1'));
        $this->assertFalse(strpos($confirmationNumber, 'I'));
        $this->assertFalse(strpos($confirmationNumber, '0'));
        $this->assertFalse(strpos($confirmationNumber, 'O'));
    }

    /** @test */
    public function confirmation_numbers_must_be_unique(): void
    {
        $generator = new RandomOrderConfirmationNumberGenerator();
        $confirmationNumber = array_map(static function ($i) use ($generator) {
            return $generator->generate();
        }, range(1, 100));

        $this->assertCount(100, array_unique($confirmationNumber));
    }
}
