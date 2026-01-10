<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => fake()->randomElement(['admin', 'responsable', 'ouvrier', 'team_leader']),
            'organization_id' => null, // Will be set in seeder
            'phone' => fake()->phoneNumber(),
            'employee_number' => fake()->unique()->numerify('EMP####'),
            'hire_date' => fake()->dateTimeBetween('-5 years', 'now'),
            'is_active' => true,
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
            'organization_id' => null, // Admins may not belong to an organization
        ]);
    }

    public function responsable(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'responsable',
        ]);
    }

    public function ouvrier(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'ouvrier',
        ]);
    }

    public function teamLeader(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'team_leader',
        ]);
    }
}
