<?php

namespace Database\Factories;

use App\Models\Concert;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TicketFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Ticket::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'concert_id' => function () {
                return Concert::factory()->published()->create()->id;
            },
            'code' => null,
        ];
    }

    /**
     * Indicate that the ticket is reserved
     */
    public function reserved(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'reserved_at' => Carbon::now(),
            ];
        });
    }
}
