<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Instrument extends Model
{
    /** Days before the due date at which calibration is flagged as "due soon". */
    public const DUE_SOON_DAYS = 30;

    protected $fillable = [
        'instrument_code',
        'name',
        'brand_model',
        'measuring_range',
        'calibration_due_date',
        'last_verified_at',
    ];

    protected $casts = [
        'calibration_due_date' => 'date',
        'last_verified_at' => 'date',
    ];

    /** Returns 'expired', 'due_soon' or 'valid'. */
    public function calibrationStatus(): string
    {
        $today = now()->startOfDay();

        if ($this->calibration_due_date->lt($today)) {
            return 'expired';
        }

        if ($this->calibration_due_date->lte($today->copy()->addDays(self::DUE_SOON_DAYS))) {
            return 'due_soon';
        }

        return 'valid';
    }

    public function daysUntilDue(): int
    {
        return (int) now()->startOfDay()->diffInDays($this->calibration_due_date, false);
    }

    public function isVerifiedToday(): bool
    {
        return $this->last_verified_at !== null && $this->last_verified_at->isToday();
    }
}
