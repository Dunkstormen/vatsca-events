<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

class StaffingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'name' => fake()->randomElement(['Early Shift', 'Late Shift', 'Main Event', 'Special Operations']),
            'order' => fake()->numberBetween(0, 100),
            'synced_to_vatsim' => false,
            'synced_at' => null,
        ];
    }
}
