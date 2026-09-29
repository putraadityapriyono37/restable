<?php

namespace Database\Factories;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Restaurant>
 */
class RestaurantFactory extends Factory
{
    protected $model = Restaurant::class;

    public function definition(): array
    {
        return [
            'owner_id' => User::factory(),
            'name' => fake()->company().' Resto',
            'address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
            'open_time' => '10:00:00',
            'close_time' => '22:00:00',
            'slot_duration' => 60,
            'dp_type' => 'percentage',
            'dp_value' => 30,
        ];
    }
}
