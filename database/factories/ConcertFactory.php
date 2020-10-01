<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Concert;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConcertFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Concert::class;

    public function definition(): array
    {
        return [
            'title' => 'Example Band',
            'subtitle' => 'with The Fake Openers',
            'date' => Carbon::parse('+2 weeks'),
            'ticket_price' => 2000,
            'venue' => 'The Example Theatre',
            'venue_address' => '123 Example Lane',
            'city' => 'Fakeville',
            'state' => 'ON',
            'zip' => '90210',
            'additional_information' => 'Some sample additional information',
        ];
    }

    /**
     * Indicate that concert is published
     */
    public function published(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'published_at' => Carbon::parse('-2 weeks'),
            ];
        });
    }

    /**
     * Indicate that concert is not published
     */
    public function unpublished(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'published_at' => null,
            ];
        });
    }
}
