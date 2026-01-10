<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Project;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@besttime.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Create employee users
        $employee1 = User::create([
            'name' => 'John Doe',
            'email' => 'john@besttime.test',
            'password' => Hash::make('password'),
            'role' => 'employee',
        ]);

        $employee2 = User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@besttime.test',
            'password' => Hash::make('password'),
            'role' => 'employee',
        ]);

        // Create projects
        $project1 = Project::create([
            'name' => 'Site Web E-commerce',
            'client' => 'Client ABC',
            'status' => 'active',
        ]);

        $project2 = Project::create([
            'name' => 'Application Mobile',
            'client' => 'Client XYZ',
            'status' => 'active',
        ]);

        $project3 = Project::create([
            'name' => 'Projet Archive',
            'client' => 'Ancien Client',
            'status' => 'archived',
        ]);

        // Create time entries for employee1
        TimeEntry::create([
            'user_id' => $employee1->id,
            'project_id' => $project1->id,
            'start_time' => now()->subDays(2)->setTime(8, 0),
            'end_time' => now()->subDays(2)->setTime(17, 0),
            'description' => 'Développement des fonctionnalités de base',
            'duration' => 32400, // 9 hours
        ]);

        TimeEntry::create([
            'user_id' => $employee1->id,
            'project_id' => $project1->id,
            'start_time' => now()->subDay()->setTime(8, 0),
            'end_time' => now()->subDay()->setTime(12, 30),
            'description' => 'Tests et corrections de bugs',
            'duration' => 16200, // 4.5 hours
        ]);

        TimeEntry::create([
            'user_id' => $employee1->id,
            'project_id' => $project2->id,
            'start_time' => now()->subDay()->setTime(14, 0),
            'end_time' => now()->subDay()->setTime(17, 0),
            'description' => 'Réunion et planification',
            'duration' => 10800, // 3 hours
        ]);

        // Create time entries for employee2
        TimeEntry::create([
            'user_id' => $employee2->id,
            'project_id' => $project2->id,
            'start_time' => now()->subDays(3)->setTime(9, 0),
            'end_time' => now()->subDays(3)->setTime(18, 0),
            'description' => 'Design UI/UX',
            'duration' => 32400, // 9 hours
        ]);

        TimeEntry::create([
            'user_id' => $employee2->id,
            'project_id' => $project1->id,
            'start_time' => now()->subDay()->setTime(9, 0),
            'end_time' => now()->subDay()->setTime(17, 0),
            'description' => 'Intégration frontend',
            'duration' => 28800, // 8 hours
        ]);

        // Create an active entry for employee1 (currently clocked in)
        TimeEntry::create([
            'user_id' => $employee1->id,
            'project_id' => $project1->id,
            'start_time' => now()->subHours(2),
            'end_time' => null,
            'description' => 'Développement en cours',
        ]);
    }
}
