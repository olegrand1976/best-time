<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class LogControllerTest extends TestCase
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

    public function test_admin_can_view_application_logs(): void
    {
        // Ensure log file exists
        $logPath = storage_path('logs/laravel.log');
        if (!File::exists($logPath)) {
            File::put($logPath, '[2024-01-01 10:00:00] local.INFO: Test log entry');
        }

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/logs/application?lines=100');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'logs',
                'total_lines',
            ]);
    }

    public function test_employee_cannot_view_application_logs(): void
    {
        $response = $this->actingAs($this->employee)
            ->getJson('/api/admin/logs/application');

        $response->assertStatus(403);
    }

    public function test_admin_can_view_activity_logs(): void
    {
        ActivityLog::factory()->count(5)->create([
            'user_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/logs/activity');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'user', 'action', 'description', 'created_at'],
                ],
            ]);
    }

    public function test_admin_can_filter_activity_logs_by_action(): void
    {
        ActivityLog::factory()->create([
            'user_id' => $this->admin->id,
            'action' => 'created',
        ]);

        ActivityLog::factory()->create([
            'user_id' => $this->admin->id,
            'action' => 'deleted',
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/logs/activity?action=created');

        $response->assertStatus(200);
        $data = $response->json('data');
        
        foreach ($data as $log) {
            $this->assertEquals('created', $log['action']);
        }
    }

    public function test_admin_can_search_activity_logs(): void
    {
        ActivityLog::factory()->create([
            'user_id' => $this->admin->id,
            'description' => 'User created: John Doe',
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/logs/activity?search=John');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertNotEmpty($data);
    }

    public function test_admin_can_view_log_statistics(): void
    {
        ActivityLog::factory()->count(10)->create([
            'user_id' => $this->admin->id,
            'action' => 'created',
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/logs/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_activities',
                'activities_by_action',
                'activities_by_user',
                'most_active_users',
            ]);
    }
}
