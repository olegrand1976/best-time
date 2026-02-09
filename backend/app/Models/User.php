<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'email',
        'password',
        'role',
        'organization_id',
        'phone',
        'address',
        'box',
        'zip_code',
        'city',
        'project_id',
        'employee_number',
        'hire_date',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'hire_date' => 'date',
            'is_active' => 'boolean',
        ];
    }
    /**
     * Get the user's full name.
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? '')) ?: $value,
        );
    }

    /**
     * Get the organization that the user belongs to.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the project that the user is assigned to.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the time entries for the user.
     */
    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    /**
     * Get time entries encoded by this user (for team leaders).
     */
    public function encodedTimeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class, 'encoded_by_user_id');
    }

    /**
     * Get responsables for this ouvrier (many-to-many).
     */
    public function responsables(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_responsables',
            'ouvrier_id',
            'responsable_id'
        )->withTimestamps();
    }

    /**
     * Get ouvriers managed by this responsable (inverse relation).
     */
    public function managedOuvriers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_responsables',
            'responsable_id',
            'ouvrier_id'
        )->withTimestamps();
    }

    /**
     * Get ouvriers this team leader can encode for.
     */
    public function teamOuvriers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'team_leaders',
            'team_leader_id',
            'ouvrier_id'
        )->withTimestamps();
    }

    /**
     * Get team leaders who can encode for this ouvrier.
     */
    public function teamLeaders(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'team_leaders',
            'ouvrier_id',
            'team_leader_id'
        )->withTimestamps();
    }

    /**
     * Get gestionnaires managed by this responsable.
     */
    public function managedGestionnaires(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'responsable_gestionnaires',
            'responsable_id',
            'gestionnaire_id'
        )->withTimestamps();
    }

    /**
     * Get the responsable managing this gestionnaire.
     */
    public function myResponsable(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'responsable_gestionnaires',
            'gestionnaire_id',
            'responsable_id'
        )->withTimestamps();
    }

    /**
     * Check if user is admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is responsable.
     */
    public function isResponsable(): bool
    {
        return $this->role === 'responsable';
    }

    /**
     * Check if user is gestionnaire.
     */
    public function isGestionnaire(): bool
    {
        return $this->role === 'gestionnaire';
    }

    /**
     * Check if user is ouvrier.
     */
    public function isOuvrier(): bool
    {
        return $this->role === 'ouvrier';
    }

    /**
     * Check if user is team leader.
     */
    public function isTeamLeader(): bool
    {
        return $this->role === 'team_leader';
    }

    /**
     * Check if user can point (admin, responsable, gestionnaire, ouvrier, team_leader can all point).
     */
    public function canPoint(): bool
    {
        return in_array($this->role, ['admin', 'responsable', 'gestionnaire', 'ouvrier', 'team_leader']);
    }

    /**
     * Check if user can encode for another user.
     */
    public function canEncodeFor(User $user): bool
    {
        // Admin can encode for anyone
        if ($this->isAdmin()) {
            return true;
        }

        // Responsable can encode for their managed ouvriers
        if ($this->isResponsable()) {
            return $this->managedOuvriers()->where('users.id', $user->id)->exists();
        }

        // Team leader can encode for their team ouvriers
        if ($this->isTeamLeader()) {
            return $this->teamOuvriers()->where('users.id', $user->id)->exists();
        }

        return false;
    }

    /**
     * Scope a query to only include active users.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter by role.
     */
    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope a query to filter by organization.
     */
    public function scopeByOrganization($query, int $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }
}
