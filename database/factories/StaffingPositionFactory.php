<?php

namespace Database\Factories;

use App\Models\Staffing;
use Illuminate\Database\Eloquent\Factories\Factory;

class StaffingPositionFactory extends Factory
{
    public function definition(): array
    {
        $positions = [
            ['id' => 'ENGM_ATIS', 'name' => 'Oslo ATIS'],
            ['id' => 'ENGM_DEL', 'name' => 'Oslo Delivery'],
            ['id' => 'ENGM_GND', 'name' => 'Oslo Ground'],
            ['id' => 'ENGM_TWR', 'name' => 'Oslo Tower'],
            ['id' => 'ENGM_APP', 'name' => 'Oslo Approach'],
        ];
        
        $position = fake()->randomElement($positions);
        
        return [
            'staffing_id' => Staffing::factory(),
            'position_id' => $position['id'],
            'position_name' => $position['name'],
            'is_local' => fake()->boolean(20),
            'start_time' => null,
            'end_time' => null,
            'order' => fake()->numberBetween(0, 100),
            'booked_by_user_id' => null,
            'discord_user_id' => null,
            'vatsim_cid' => null,
            'control_center_booking_id' => null,
        ];
    }
}
