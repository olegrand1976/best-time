<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'description',
        'address',
        'city',
        'postal_code',
        'country',
        'phone',
        'email',
        'is_active',
        'location_required',
        'geofencing_enabled',
        'geofencing_radius',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'location_required' => 'boolean',
            'geofencing_enabled' => 'boolean',
        ];
    }

    /**
     * Get all users belonging to this organization.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get responsables in this organization.
     */
    public function responsables(): HasMany
    {
        return $this->hasMany(User::class)->where('role', 'responsable');
    }

    /**
     * Get ouvriers in this organization.
     */
    public function ouvriers(): HasMany
    {
        return $this->hasMany(User::class)->whereIn('role', ['ouvrier', 'team_leader']);
    }

    /**
     * Scope a query to only include active organizations.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
