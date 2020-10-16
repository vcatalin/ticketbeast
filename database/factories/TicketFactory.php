<?php

namespace Database\Factories;

use App\Models\Concert;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

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
            }
        ];
    }
}
