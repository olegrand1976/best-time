<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Project;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TimeEntryFactory extends Factory
{
    protected $model = TimeEntry::class;

    public function definition(): array
    {
        $startTime = fake()->dateTimeBetween('-1 week', 'now');
        $endTime = (clone $startTime)->modify('+' . fake()->numberBetween(1, 8) . ' hours');

        return [
            'user_id' => User::factory(),
            'project_id' => fake()->optional(0.7)->passthrough(Project::factory()),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'description' => fake()->optional()->sentence(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'end_time' => null,
        ]);
    }
}
