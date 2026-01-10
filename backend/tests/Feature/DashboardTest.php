<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_view_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'employee']);
        $token = $user->createToken('test-token')->plainTextToken;

        // Create some time entries
        TimeEntry::factory()->create([
            'user_id' => $user->id,
            'start_time' => now()->startOfDay(),
            'end_time' => now()->startOfDay()->addHours(8),
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'active_entry',
                'today_hours',
                'week_hours',
            ]);
    }

    public function test_admin_can_view_dashboard_with_all_statistics(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('admin-token')->plainTextToken;

        $employee = User::factory()->create(['role' => 'employee']);
        TimeEntry::factory()->create([
            'user_id' => $employee->id,
            'start_time' => now()->startOfDay(),
            'end_time' => now()->startOfDay()->addHours(8),
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'today_hours',
                'week_hours',
                'total_employees',
                'active_entries',
            ]);
    }
}
