<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_many_time_entries(): void
    {
        $user = User::factory()->create();
        TimeEntry::factory()->count(3)->create(['user_id' => $user->id]);

        $this->assertCount(3, $user->timeEntries);
    }

    public function test_is_admin_returns_true_for_admin_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isEmployee());
    }

    public function test_is_employee_returns_true_for_employee_role(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);

        $this->assertTrue($employee->isEmployee());
        $this->assertFalse($employee->isAdmin());
    }
}
