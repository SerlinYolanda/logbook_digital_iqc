<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Logbook extends Model
{
    use HasFactory;

    protected $fillable = [
        'receiving_id',
        'inspector_id',
        'qty_sampel',
        'visual_packaging',
        'visual_esd_moisture',
        'visual_body',
        'visual_terminal',
        'visual_notes',
        'date_code',
        'manufacturing_date',
        'expiration_date',
        'is_expired',
        'shelf_life_status',
        'status',
        'tanggal_inspeksi',
    ];

    protected $casts = [
        'tanggal_inspeksi' => 'datetime',
        'manufacturing_date' => 'date',
        'expiration_date' => 'date',
        'qty_sampel' => 'integer',
        'is_expired' => 'boolean',
    ];

    public function hasVisualFail(): bool
    {
        return $this->visual_packaging === 'fail'
            || $this->visual_esd_moisture === 'fail'
            || $this->visual_body === 'fail'
            || $this->visual_terminal === 'fail';
    }

    public function getShelfLifeBadgeClassAttribute(): string
    {
        if ($this->is_expired || $this->shelf_life_status === 'expired') {
            return 'bg-rose-100 text-rose-800 border-rose-300 animate-pulse';
        }
        if ($this->shelf_life_status === 'near_expiry') {
            return 'bg-amber-100 text-amber-800 border-amber-300';
        }
        return 'bg-emerald-100 text-emerald-800 border-emerald-300';
    }

    public function receiving(): BelongsTo
    {
        return $this->belongsTo(Receiving::class, 'receiving_id');
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    public function measurements(): HasMany
    {
        return $this->hasMany(Measurement::class, 'logbook_id');
    }

    public function defects(): HasMany
    {
        return $this->hasMany(Defect::class, 'logbook_id');
    }

    public function nestProcess(): HasOne
    {
        return $this->hasOne(NestProcess::class, 'logbook_id');
    }

    public function mrbDispositions(): HasMany
    {
        return $this->hasMany(MrbDisposition::class, 'logbook_id');
    }

    public function latestMrbDisposition(): HasOne
    {
        return $this->hasOne(MrbDisposition::class, 'logbook_id')->latestOfMany();
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'menunggu_penetapan' => 'Pending MRB Review',
            'perlu_revisi' => 'Needs Revision',
            'accept' => 'Accepted / Passed',
            'return_to_vendor' => 'Return to Vendor (RTV)',
            'scrap' => 'Scrap',
            'rework' => 'Rework',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'accept' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'menunggu_penetapan' => 'bg-amber-100 text-amber-800 border-amber-300 animate-pulse',
            'perlu_revisi' => 'bg-orange-100 text-orange-800 border-orange-300',
            'return_to_vendor' => 'bg-rose-100 text-rose-800 border-rose-300',
            'scrap' => 'bg-red-200 text-red-900 border-red-400',
            'rework' => 'bg-purple-100 text-purple-800 border-purple-300',
            'draft' => 'bg-slate-100 text-slate-700 border-slate-300',
            default => 'bg-gray-100 text-gray-700 border-gray-300',
        };
    }
}

