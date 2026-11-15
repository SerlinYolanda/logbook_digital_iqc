<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MrbDisposition extends Model
{
    use HasFactory;

    protected $fillable = [
        'logbook_id',
        'supervisor_id',
        'status_keputusan',
        'catatan',
        'diputuskan_pada',
    ];

    protected $casts = [
        'diputuskan_pada' => 'datetime',
    ];

    public function logbook(): BelongsTo
    {
        return $this->belongsTo(Logbook::class, 'logbook_id');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function getDecisionLabelAttribute(): string
    {
        return match ($this->status_keputusan) {
            'accept' => 'Accept (Use As Is)',
            'return_to_vendor' => 'Return to Vendor (RTV)',
            'scrap' => 'Scrap',
            'rework' => 'Rework',
            'perlu_revisi' => 'Revise Logbook',
            default => ucfirst(str_replace('_', ' ', $this->status_keputusan)),
        };
    }
}

