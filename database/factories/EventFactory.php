<?php

namespace Database\Factories;

use App\Models\Calendar;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('now', '+30 days');
        $end = (clone $start)->modify('+2 hours');
        
        return [
            'calendar_id' => Calendar::factory(),
            'title' => fake()->sentence(4),
            'short_description' => fake()->sentence(),
            'long_description' => fake()->paragraphs(3, true),
            'staffing_description' => null,
            'featured_airports' => [],
            'banner_path' => null,
            'start_datetime' => $start,
            'end_datetime' => $end,
            'recurrence_rule' => null,
            'recurrence_parent_id' => null,
            'discord_staffing_message_id' => null,
            'discord_staffing_channel_id' => null,
            'notified_occurrences' => [],
            'created_by' => User::factory(),
        ];
    }
}
