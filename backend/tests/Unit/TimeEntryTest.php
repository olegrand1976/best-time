<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Project;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimeEntryTest extends TestCase
{
    use RefreshDatabase;

    public function test_time_entry_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $entry = TimeEntry::factory()->create(['user_id' => $user->id]);

        $this->assertEquals($user->id, $entry->user->id);
    }

    public function test_time_entry_belongs_to_project(): void
    {
        $project = Project::factory()->create();
        $entry = TimeEntry::factory()->create(['project_id' => $project->id]);

        $this->assertEquals($project->id, $entry->project->id);
    }

    public function test_calculate_duration_returns_correct_seconds(): void
    {
        $start = now();
        $end = $start->copy()->addHours(2)->addMinutes(30);

        $entry = new TimeEntry([
            'start_time' => $start,
            'end_time' => $end,
        ]);

        $duration = $entry->calculateDuration();

        $this->assertEquals(9000, $duration); // 2.5 hours = 9000 seconds
    }

    public function test_calculate_duration_returns_null_when_end_time_missing(): void
    {
        $entry = new TimeEntry([
            'start_time' => now(),
            'end_time' => null,
        ]);

        $this->assertNull($entry->calculateDuration());
    }

    public function test_duration_is_automatically_calculated_on_save(): void
    {
        $user = User::factory()->create();
        $start = now();
        $end = $start->copy()->addHours(3);

        $entry = TimeEntry::create([
            'user_id' => $user->id,
            'start_time' => $start,
            'end_time' => $end,
        ]);

        $this->assertEquals(10800, $entry->duration); // 3 hours = 10800 seconds
    }

    public function test_today_scope_filters_entries_for_today(): void
    {
        $user = User::factory()->create();

        TimeEntry::factory()->create([
            'user_id' => $user->id,
            'start_time' => now()->startOfDay(),
        ]);

        TimeEntry::factory()->create([
            'user_id' => $user->id,
            'start_time' => now()->subDay(),
        ]);

        $todayEntries = TimeEntry::today()->get();

        $this->assertCount(1, $todayEntries);
    }
}
