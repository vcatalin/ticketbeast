<?php

namespace Database\Factories;

use App\Facades\OrderConfirmationNumber;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'amount' => 6500,
            'email' => 'john@example.com',
            'confirmation_number' => 'ORDERCONFIRMATION1234',
            'card_last_four' => '1234',
        ];
    }
}
