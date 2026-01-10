<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Project;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatisticsControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'email' => 'admin@test.com',
            'role' => 'admin',
        ]);

        $this->employee = User::factory()->create([
            'email' => 'employee@test.com',
            'role' => 'employee',
        ]);
    }

    public function test_admin_can_view_statistics(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_users',
                'total_employees',
                'total_projects',
                'active_projects',
                'total_time_entries',
                'total_hours',
                'hours_by_day',
                'hours_by_user',
                'hours_by_project',
            ]);
    }

    public function test_employee_cannot_view_statistics(): void
    {
        $response = $this->actingAs($this->employee)
            ->getJson('/api/admin/statistics');

        $response->assertStatus(403);
    }

    public function test_statistics_includes_user_data(): void
    {
        User::factory()->count(3)->create(['role' => 'employee']);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/statistics');

        $response->assertStatus(200)
            ->assertJson([
                'total_users' => 5, // admin + employee + 3 new
                'total_employees' => 4, // employee + 3 new
            ]);
    }

    public function test_statistics_includes_project_data(): void
    {
        Project::factory()->count(5)->create(['status' => 'active']);
        Project::factory()->count(2)->create(['status' => 'archived']);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/statistics');

        $response->assertStatus(200)
            ->assertJson([
                'total_projects' => 7,
                'active_projects' => 5,
            ]);
    }

    public function test_statistics_can_filter_by_date_range(): void
    {
        $startDate = now()->startOfMonth()->format('Y-m-d');
        $endDate = now()->endOfMonth()->format('Y-m-d');

        $response = $this->actingAs($this->admin)
            ->getJson("/api/admin/statistics?start_date={$startDate}&end_date={$endDate}");

        $response->assertStatus(200);
    }
}
