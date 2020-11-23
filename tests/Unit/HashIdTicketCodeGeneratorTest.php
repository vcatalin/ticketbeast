<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\HashIdTicketCodeGenerator;
use App\Models\Ticket;
use Tests\TestCase;

class HashIdTicketCodeGeneratorTest extends TestCase
{
    /** @test */
    public function ticket_codes_are_at_least6_characters_long(): void
    {
        $codeGenerator = new HashIdTicketCodeGenerator('testsalt1');
        $code = $codeGenerator->generateFor(new Ticket(['id' => 1]));

        $this->assertTrue(strlen($code) >= 6);
    }

    /** @test */
    public function ticket_codes_can_only_be_uppercase_letters(): void
    {
        $codeGenerator = new HashIdTicketCodeGenerator('testsalt1');
        $code = $codeGenerator->generateFor(new Ticket(['id' => 1]));

        $this->assertMatchesRegularExpression('/^[A-Z]+$/', $code);
    }

    /** @test */
    public function ticket_codes_for_the_same_ticket_id_are_the_same(): void
    {
        $codeGenerator = new HashIdTicketCodeGenerator('testsalt1');
        $code1 = $codeGenerator->generateFor(new Ticket(['id' => 1]));
        $code2 = $codeGenerator->generateFor(new Ticket(['id' => 1]));

        $this->assertEquals($code1, $code2);
    }

    /** @test */
    public function ticket_codes_for_different_ticket_ids_are_different(): void
    {
        $codeGenerator = new HashIdTicketCodeGenerator('testsalt1');
        $code1 = $codeGenerator->generateFor(new Ticket(['id' => 1]));
        $code2 = $codeGenerator->generateFor(new Ticket(['id' => 2]));

        $this->assertNotEquals($code1, $code2);
    }

    /** @test */
    public function ticket_codes_with_generated_with_different_salts_are_different(): void
    {
        $codeGenerator1 = new HashIdTicketCodeGenerator('testsalt1');
        $codeGenerator2 = new HashIdTicketCodeGenerator('testsalt2');

        $code1 = $codeGenerator1->generateFor(new Ticket(['id' => 1]));
        $code2 = $codeGenerator2->generateFor(new Ticket(['id' => 1]));

        $this->assertNotEquals($code1, $code2);
    }
}
