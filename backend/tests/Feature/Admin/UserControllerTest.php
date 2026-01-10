<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
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

    public function test_admin_can_list_users(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'email', 'role'],
                ],
            ]);
    }

    public function test_employee_cannot_list_users(): void
    {
        $response = $this->actingAs($this->employee)
            ->getJson('/api/admin/users');

        $response->assertStatus(403);
    }

    public function test_admin_can_create_user(): void
    {
        $userData = [
            'name' => 'New User',
            'email' => 'newuser@test.com',
            'password' => 'password123',
            'role' => 'employee',
        ];

        $response = $this->actingAs($this->admin)
            ->postJson('/api/admin/users', $userData);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'New User',
                'email' => 'newuser@test.com',
                'role' => 'employee',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@test.com',
            'role' => 'employee',
        ]);
    }

    public function test_admin_can_update_user(): void
    {
        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@test.com',
        ];

        $response = $this->actingAs($this->admin)
            ->putJson("/api/admin/users/{$this->employee->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'Updated Name',
                'email' => 'updated@test.com',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->employee->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_admin_can_delete_user(): void
    {
        $userToDelete = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/admin/users/{$userToDelete->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('users', [
            'id' => $userToDelete->id,
        ]);
    }

    public function test_admin_cannot_delete_self(): void
    {
        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/admin/users/{$this->admin->id}");

        $response->assertStatus(403);
    }

    public function test_admin_can_view_user_statistics(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson("/api/admin/users/{$this->employee->id}/statistics");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_entries',
                'total_hours',
                'active_projects',
            ]);
    }

    public function test_can_search_users_by_name(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/users?search=' . urlencode($this->employee->name));

        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => $this->employee->name,
            ]);
    }

    public function test_can_filter_users_by_role(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/admin/users?role=employee');

        $response->assertStatus(200);

        $users = $response->json('data');
        foreach ($users as $user) {
            $this->assertEquals('employee', $user['role']);
        }
    }
}
