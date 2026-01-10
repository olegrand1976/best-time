<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Project;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimeEntryTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'employee']);
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    public function test_employee_can_create_time_entry(): void
    {
        $project = Project::factory()->create();

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/time-entries', [
                'project_id' => $project->id,
                'start_time' => now()->format('Y-m-d H:i:s'),
                'end_time' => now()->addHours(2)->format('Y-m-d H:i:s'),
                'description' => 'Test entry',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'user_id',
                'project_id',
                'start_time',
                'end_time',
                'duration',
            ]);

        $this->assertDatabaseHas('time_entries', [
            'user_id' => $this->user->id,
            'project_id' => $project->id,
            'description' => 'Test entry',
        ]);
    }

    public function test_employee_can_start_time_entry(): void
    {
        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/time-entries/start', [
                'description' => 'Starting work',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['id', 'start_time', 'is_active'])
            ->assertJson(['is_active' => true]);

        $this->assertDatabaseHas('time_entries', [
            'user_id' => $this->user->id,
            'end_time' => null,
        ]);
    }

    public function test_employee_can_stop_time_entry(): void
    {
        // Start an entry
        $entry = TimeEntry::create([
            'user_id' => $this->user->id,
            'start_time' => now()->subHour(),
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/time-entries/stop');

        $response->assertStatus(200)
            ->assertJsonStructure(['id', 'start_time', 'end_time', 'duration']);

        $entry->refresh();
        $this->assertNotNull($entry->end_time);
        $this->assertNotNull($entry->duration);
    }

    public function test_employee_can_view_their_time_entries(): void
    {
        TimeEntry::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/time-entries');

        $response->assertStatus(200)
            ->assertJsonStructure(['data'])
            ->assertJsonCount(3, 'data');
    }

    public function test_employee_cannot_view_other_users_entries(): void
    {
        $otherUser = User::factory()->create();
        TimeEntry::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/time-entries');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    public function test_admin_can_view_all_time_entries(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $adminToken = $admin->createToken('admin-token')->plainTextToken;

        $employee = User::factory()->create(['role' => 'employee']);
        TimeEntry::factory()->create(['user_id' => $employee->id]);

        $response = $this->withHeader('Authorization', "Bearer {$adminToken}")
            ->getJson('/api/time-entries');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }
}
