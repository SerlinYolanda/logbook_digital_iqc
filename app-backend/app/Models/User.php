<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'nik',
        'nip',
        'role',
        'status_aktif',
        'shift',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status_aktif' => 'boolean',
        ];
    }

    // Role helpers
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isMasterData(): bool
    {
        return $this->role === 'master_data';
    }

    public function isSupervisor(): bool
    {
        return $this->role === 'supervisor';
    }

    public function isInspector(): bool
    {
        return str_starts_with($this->role, 'inspector');
    }

    // Commodity check for Poka-Yoke
    public function getInspectorCommodity(): ?string
    {
        return match ($this->role) {
            'inspector_ee' => 'ee_part',
            'inspector_automotive' => 'automotive',
            'inspector_wire_harness' => 'wire_harness',
            default => null,
        };
    }

    public function canInspectCommodity(string $commodity): bool
    {
        if ($this->role === 'inspector') {
            return true; // generic demo fallback
        }
        $assigned = $this->getInspectorCommodity();
        return $assigned === null || $assigned === $commodity;
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'admin' => 'System Administrator',
            'master_data' => 'Master Data / Logistics',
            'supervisor' => 'IQC Supervisor',
            'inspector_ee' => 'EE Part Inspector',
            'inspector_automotive' => 'Automotive Inspector',
            'inspector_wire_harness' => 'Wire Harness Inspector',
            'inspector' => 'IQC Inspector',
            default => ucfirst(str_replace('_', ' ', $this->role)),
        };
    }

    public function logbooks(): HasMany
    {
        return $this->hasMany(Logbook::class, 'inspector_id');
    }

    public function mrbDispositions(): HasMany
    {
        return $this->hasMany(MrbDisposition::class, 'supervisor_id');
    }

    public function auditTrails(): HasMany
    {
        return $this->hasMany(AuditTrail::class, 'user_id');
    }
}
