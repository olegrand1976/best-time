<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ActivityLog>
 */
class ActivityLogFactory extends Factory
{
    protected $model = ActivityLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $actions = ['created', 'updated', 'deleted', 'login', 'logout', 'clock_in', 'clock_out'];
        $models = ['App\\Models\\User', 'App\\Models\\Project', 'App\\Models\\TimeEntry'];
        
        return [
            'user_id' => User::factory(),
            'action' => $this->faker->randomElement($actions),
            'model_type' => $this->faker->randomElement($models),
            'model_id' => $this->faker->numberBetween(1, 100),
            'old_values' => null,
            'new_values' => ['field' => $this->faker->word],
            'description' => $this->faker->sentence,
            'ip_address' => $this->faker->ipv4,
            'user_agent' => $this->faker->userAgent,
        ];
    }
}
