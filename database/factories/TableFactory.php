<?php

namespace Database\Factories;

use App\Models\Restaurant;
use App\Models\TableModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TableModel>
 */
class TableFactory extends Factory
{
    protected $model = TableModel::class;

    public function definition(): array
    {
        return [
            'restaurant_id' => Restaurant::factory(),
            'table_number' => (string) fake()->unique()->numberBetween(1, 50),
            'capacity' => fake()->numberBetween(2, 8),
            'is_active' => true,
        ];
    }
}
