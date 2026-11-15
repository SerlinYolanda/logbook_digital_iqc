<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Receiving extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_grn_iqa',
        'tanggal_terima',
        'supplier_id',
        'part_id',
        'no_po',
        'no_lot',
        'qty_datang',
        'status_antrean',
    ];

    protected $casts = [
        'tanggal_terima' => 'datetime',
        'qty_datang' => 'integer',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class, 'part_id');
    }

    public function logbooks(): HasMany
    {
        return $this->hasMany(Logbook::class, 'receiving_id');
    }

    public function getLatestLogbookAttribute(): ?Logbook
    {
        return $this->logbooks()->latest()->first();
    }

    /**
     * Queue aging calculation based on Proposal:
     * - Normal: < 12 hours
     * - Prioritas: 12 - 24 hours
     * - Overdue: > 24 hours
     */
    public function getAgingHoursAttribute(): float
    {
        return round(Carbon::parse($this->tanggal_terima)->diffInMinutes(now()) / 60, 1);
    }

    public function getAgingStatusAttribute(): string
    {
        $hours = $this->aging_hours;
        if ($hours >= 24) {
            return 'Overdue';
        }
        if ($hours >= 12) {
            return 'Priority';
        }
        return 'Normal';
    }

    public function getAgingBadgeClassAttribute(): string
    {
        return match ($this->aging_status) {
            'Overdue' => 'bg-rose-100 text-rose-700 border-rose-300',
            'Priority' => 'bg-amber-100 text-amber-800 border-amber-300',
            default => 'bg-emerald-100 text-emerald-800 border-emerald-300',
        };
    }

    public function getQueueStatusLabelAttribute(): string
    {
        return match ($this->status_antrean) {
            'menunggu' => 'Waiting for Inspection',
            'proses' => 'In Progress',
            'selesai' => 'Completed',
            default => ucfirst(str_replace('_', ' ', $this->status_antrean)),
        };
    }
}

